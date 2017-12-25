<?php

if (isset($_GET['cgephemeral']) || isset($_GET['cgvalidated'])) {
  add_action('plugins_loaded','codeguard_authenticate');
}

function codeguard_authenticate() {
  try {
    $options = get_option(PREFIX_CODEGUARD . 'setting');
    if (isset($_GET['cgvalidated']) ) {
      if (is_user_logged_in()) {
        $nonce = wp_create_nonce('wp_rest');
        header( 'X-WP-Nonce: ' . $nonce);
      }
      $destination = remove_query_arg(array('cgephemeral','cgvalidated'));
      wp_redirect($destination == "/" ? admin_url() : $destination);
      exit();
    } elseif (isset($options['access_key_id']) && isset($options['secret_access_key']) && isset($options['prefix'])) {
      require_once (main::getPluginDir() . '/libs/classes/S3.php');
       $s3 = new S3($options['access_key_id'], $options['secret_access_key']);

      $result = $s3->getObject('codeguard-wordpress-tokens', $options['prefix'] . '/' . $_GET['cgephemeral']);
      $s3->deleteObject('codeguard-wordpress-tokens', $options['prefix'] . '/' . $_GET['cgephemeral']);
      $bodyAsString = (string) trim($result['Body']);
      if ($bodyAsString == "") {
        $users = get_users('role=administrator');
        $firstUser = $users[0];
        $bodyAsString = $firstUser->user_login;
      }
      $user = new WP_User('',$bodyAsString);
      wp_set_auth_cookie($user->ID);
      wpe_authenticate();
      wp_redirect(add_query_arg(array('cgvalidated' => 'true')));
      exit();
    }
  } catch (Exception $e) {
    error_log($e);
  } // All my techniques are useless here.
}

// Authenticate with WP Engine
function wpe_authenticate() {
  if ( defined( 'WPE_APIKEY' ) && WPE_APIKEY ) {
    $cookie_value = md5( 'wpe_auth_salty_dog|' . WPE_APIKEY );
    setcookie( 'wpe-auth', $cookie_value, 0, '/' );
  }
}
