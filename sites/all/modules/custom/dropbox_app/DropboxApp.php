<?php

//namespace DropboxApp;

use \Dropbox;

class DropboxApp {

  /**
   * Loads the Dropbox SDK library and returns an AppInfo object.
   *
   * @return \Dropbox\AppInfo
   * @throws \Exception
   */
  function dropbox_app_load_sdk() {
    // Only load the SDK once and cache the AppInfo return value.
    static $appInfo = '';

    if (empty($appInfo)) {
      $library = libraries_load('dropbox_sdk');
      if (!$library || empty($library['loaded'])) {
        throw new Exception($library['error message']);
      }

      $appInfo = Dropbox\AppInfo::loadFromJson(array(
        'key' => variable_get('dropbox_app_app_key', ''),
        'secret' => variable_get('dropbox_app_app_secret', ''),
      ));
    }

    return $appInfo;
  }

  protected function getWebAuth() {
    $appInfo = dropbox_app_load_sdk();
    $clientId = 'drupal-photo-gallery/1.0';

    // Local dev.
    $redirectUrl = 'https://vagrant.denniswise.com/dropbox_api/authorize/finish';

    $csrfTokenStore = new Dropbox\ArrayEntryStore($_SESSION, 'dropbox-auth-csrf-token');
    return new Dropbox\WebAuth($appInfo, $clientId, $redirectUrl, $csrfTokenStore);
  }

  public function authorizeStart() {
    $webAuth = $this->getWebAuth();
    $url = $webAuth->start();
    drupal_goto($url);
  }

  public function authorizeFinish() {
    $webAuth = $this->getWebAuth();
    dpr($_GET);
    dpr($_SESSION);
    dpr(var_dump(drupal_session_started()));
    dpr('trying...');
    try {
      list($accessToken, $userId, $urlState) = getWebAuth()->finish($_GET);
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
