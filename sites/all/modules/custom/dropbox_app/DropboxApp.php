<?php

//namespace DropboxApp;

use \Dropbox;

class DropboxApp {
  /**
   * @var \Dropbox\AppInfo
   */
  protected $appInfo;

  /**
   * @var \Dropbox\WebAuth
   */
  protected $webAuth;

  /**
   * @var string
   *
   * An identifier for the API client, typically of the form "Name/Version".
   * This is used to set the HTTP User-Agent header when making API requests.
   * Example: "Drupal-Photo-Gallery/1.0"
   */
  protected $clientId;

  /**
   * @var string
   *
   * The URI that the Dropbox server will redirect the user to after the user
   * finishes authorizing your app. This URI must be HTTPS-based and
   * pre-registered with Dropbox, though "localhost"-based and "127.0.0.1"-based
   * URIs are allowed without pre-registration and can be either HTTP or HTTPS.
   * Generally, this endpoint will call authorizeFinish().
   * Example: "https://www.example.com/dropbox/authorize/complete"
   */
  protected $redirectUri;

  /**
   * @var integer
   *
   * The Drupal UID of the authorized user of the Dropbox account. For example,
   * a user may setup a photo gallery based on a Dropbox folder but wants it to
   * be viewable by anonymous users. When viewing the gallery, $drupalUser
   * should equal the gallery owner's Drupal UID otherwise anonymous (or other
   * Drupal users) will not be able access the files necessary to build the
   * gallery.
   */
  protected $drupalUser;

  /**
   * @var string
   *
   * The Dropbox access token returned from a successful \Dropbox\WebAuth.
   */
  protected $accessToken;

  /**
   * @var \Dropbox\Client
   *
   * The class used to make most Dropbox API calls.
   */
  protected $client;

  public function __construct($clientId, $redirectUri) {
    $this->clientId = $clientId;
    $this->redirectUri = $redirectUri;

    // Drupal user defaults to the currently logged in user. This can be over-
    // ridden via setDrupalUser().
    global $user;
    $this->drupalUser = $user->uid;

    $this->loadLibrary();

    // Collect App information. Note: even though the Dropbox API says it's
    // loading "fromJSON," it's actually using the output from json_decode(),
    // namely an array.
    $this->appInfo = Dropbox\AppInfo::loadFromJson(array(
      'key' => variable_get('dropbox_app_key', ''),
      'secret' => variable_get('dropbox_app_secret', ''),
    ));

    $csrfTokenStore = new Dropbox\ArrayEntryStore($_SESSION, 'dropbox-auth-csrf-token');
    $this->webAuth = new Dropbox\WebAuth($this->appInfo, $this->clientId, $this->redirectUri, $csrfTokenStore);
  }

  /**
   * Ensures the Dropbox SDK is available when unserializing this object.
   *
   * @throws \Exception
   */
  public function __wakeup() {
    $this->loadLibrary();
  }

  /**
   * Loads the Dropbox SDK library.
   *
   * @throws \Exception
   */
  protected function loadLibrary() {
    $library = libraries_load('dropbox_sdk');
    if (!$library || empty($library['loaded'])) {
      throw new Exception($library['error message']);
    }
  }

  /**
   * Starts an authorization request with Dropbox. Forward the visitor to
   * Dropbox to (potentially authenticate) and authorize use of this app.
   */
  public function authorizeStart() {
    if (user_is_anonymous()) {
      // It is not recommended that anonymous visitors authorize apps with
      // Dropbox. This would lead to any anonymous user having access to the
      // Dropbox account of the first visitor via the app in question. If this
      // is, in fact, the intention of your Dropbox app, then you should
      // subclass DropboxApp and override this functionality.
      drupal_access_denied();
    }
    $url = $this->webAuth->start();
    drupal_goto($url);
  }

  /**
   * Finishes an authorization request with Dropbox. On success, an access token
   * for this Drupal user will be saved in the database.
   */
  public function authorizeFinish() {
    try {
      // Note:
      //    $userId is the Dropbox user ID
      //    $urlState is passed into webAuth->start()
      // Both are unused at this time.
      list($accessToken, $userId, $urlState) = $this->webAuth->finish($_GET);
    }
    catch (Exception $ex) {
      drupal_set_message(t(
        'There was an error authorizing use of your Dropbox account: %err',
        array('%err' => $ex->getMessage())
      ));

      if ($ex instanceof Dropbox\WebAuthException_BadRequest) {
        // Technically, should be a 400, but there's no easy way to return a 400
        // without messing around with headers.
        drupal_not_found();
      }

      if ($ex instanceof Dropbox\WebAuthException_Csrf) {
        drupal_access_denied();
      }

      // drupal_not_found and drupal_access_denied will short-circuit the page
      // rendering pipeline. But if this is not one of those errors, return
      // FALSE and let the calling function handle it.
      return FALSE;
    }

    // Sanity check -- visitors should not have been able to get through
    // authorizeStart as anonymous.
    if (!$this->drupalUser) {
      drupal_access_denied();
    }

    // Otherwise, store the access token for this Drupal user.
    $this->accessToken = $accessToken;

    //  Save the access token for this user.
    db_delete('dropbox_app')
      ->condition('uid', $this->drupalUser)
      ->execute();
    $record = array(
      'uid' => $this->drupalUser,
      'access_token' => $this->accessToken,
    );
    drupal_write_record('dropbox_app', $record);
    return TRUE;
  }

  /**
   * @return string
   */
  protected function getAccessToken() {
    if (empty($this->accessToken)) {
      $token = db_query(
        'SELECT access_token FROM {dropbox_app} WHERE uid = :uid',
        array(':uid' => $this->drupalUser))
        ->fetchField();
      if (empty($token)) {
        $this->authorizeStart();
      }
      else {
        $this->accessToken = $token;
      }
    }
    return $this->accessToken;
  }

  /**
   * Gets/sets the Drupal user whose authorization token should be used for
   * Dropbox API calls.
   *
   * @param $uid
   *   Drupal user's ID.
   *
   * @return bool
   *   FALSE if there is no authorization token for this user. TRUE otherwise.
   */
  public function setDrupalUser($uid) {
    $token = db_query(
      'SELECT access_token FROM {dropbox_app} WHERE uid = :uid',
      array(':uid' => $this->drupalUser))
      ->fetchField();
    if (empty($token)) {
      return FALSE;
    }
    else {
      $this->drupalUser = $uid;
      $this->accessToken = $token;
      return TRUE;
    }
  }
  public function getDrupalUser() {
    return $this->drupalUser;
  }

  /**
   * Returns a \Dropbox\Client object that can be used to call the Dropbox API.
   */
  public function getClient() {
    if (empty($this->client)) {
      $this->client = new Dropbox\Client($this->getAccessToken(), $this->clientId);
    }
    return $this->client;
  }
}
