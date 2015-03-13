<?php
// Include the YOS library.
require 'library/yahoo/Yahoo.inc';
require 'config/social_config.php';
require 'config/dbconfig.php';
error_reporting(E_ALL | E_NOTICE); # do not show notices as library is php4 compatable
session_start();

if (array_key_exists("login", $_GET)) {
    $session = YahooSession::requireSession(OAUTH_CONSUMER_KEY, OAUTH_CONSUMER_SECRET, OAUTH_APP_ID);
    if (is_object($session)) {
        $user = $session->getSessionedUser();
        $profile = $user->getProfile();
		$name = $profile->nickname; // Getting user name
        $guid = $profile->guid; // Getting Yahoo ID

        //Retriving the user
        $query = mysql_query("SELECT social_id,name from accounts where social_id = '$guid' and oauth_provider = 'yahoo'") or die (mysql_error());
        $result = mysql_fetch_array($query);

        if (empty($result)) {
            // user not present in Database. Store a new user and Create new account for him
            $query = mysql_query("INSERT INTO accounts(oauth_provider , social_id, name) VALUES('yahoo', '$guid', '$name')") or die (mysql_error());
            $query = mysql_query("SELECT social_id,name from accounts where social_id = '$guid' and oauth_provider  = 'yahoo'") or die (mysql_error());
            $result = mysql_fetch_array($query);
        }
        // Creating session variable for User
        $_SESSION['login'] = true;
        $_SESSION['name'] = $result['name'];
        $_SESSION['id'] = $result['guid'];
        $_SESSION['oauth_provider'] = 'yahoo';
    }
}

if (array_key_exists("logout", $_GET)) {
    // User logging out and Clearing all Session data
    YahooSession::clearSession();
    unset ($_SESSION['login']);
    unset($_SESSION['name']);
    unset($_SESSION['id']);
    unset($_SESSION['oauth_provider']);
    // After logout Redirection here
    header("Location: index.php");
}
?>