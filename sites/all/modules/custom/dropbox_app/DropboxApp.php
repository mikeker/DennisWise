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

  public function authorizeStart() {
    $url = $this->webAuth->start();
    drupal_goto($url);
  }

  public function authorizeFinish() {
    dpr($_GET);
    dpr($_SESSION);
    dpr(var_dump(drupal_session_started()));
    dpr('trying...');
    try {
      list($accessToken, $userId, $urlState) = $this->webAuth->finish($_GET);
    }
    catch (Dropbox\WebAuthException_BadRequest $ex) {
      dpr("/dropbox-auth-finish: bad request: " . $ex->getMessage());
      // Respond with an HTTP 400 and display error page...
    }
    catch (Dropbox\WebAuthException_BadState $ex) {
      // Auth session expired.  Restart the auth process.
      //drupal_goto('/dropbox_api/authorize/start');
      dpr('Session expired, Going to /dropbox_api/authorize/start');
    }
    catch (Dropbox\WebAuthException_Csrf $ex) {
      dpr("/dropbox-auth-finish: CSRF mismatch: " . $ex->getMessage());
      // Respond with HTTP 403 and display error page...
    }
    catch (Dropbox\WebAuthException_NotApproved $ex) {
      dpr("/dropbox-auth-finish: not approved: " . $ex->getMessage());
    }
    catch (Dropbox\WebAuthException_Provider $ex) {
      dpr("/dropbox-auth-finish: error redirect from Dropbox: " . $ex->getMessage());
    }
    catch (Dropbox\Exception $ex) {
      dpr("/dropbox-auth-finish: error communicating with Dropbox API: " . $ex->getMessage());
    }

    dpr('success!');
    dpr($accessToken);
    dpr($userId);
    dpr($urlState);
    exit;
  }
}
