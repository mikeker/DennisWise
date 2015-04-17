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

    // Load the Dropbox SDK library.
    $library = libraries_load('dropbox_sdk');
    if (!$library || empty($library['loaded'])) {
      throw new Exception($library['error message']);
    }

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

      // Needed to ensure page rendering continues if this is called from a menu
      // callback function. Otherwise the visitor won't see the above message.
      return '';
    }

    // Sanity check -- visitors should not have been able to get through
    // authorizeStart as anonymous.
    global $user;
    if (!$user->uid) {
      drupal_access_denied();
    }

    $this->accessToken = $accessToken;

    //  Save the access token for this user.
    db_delete('dropbox_app')
      ->condition('uid', $user->uid)
      ->execute();
    $record = array(
      'uid' => $user->uid,
      'access_token' => $this->accessToken,
    );
    drupal_write_record('dropbox_app', $record);
  }

  /**
   * @return string
   */
  protected function getAccessToken() {
    global $user;
    if (empty($this->accessToken)) {
      $token = db_query(
        'SELECT access_token FROM {dropbox_app} WHERE uid = :uid',
        array(':uid' => $user->uid))
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
}
