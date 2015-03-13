<?php

require("library/twitter/twitteroauth.php");
require 'config/social_config.php';
require 'config/functions.php';
session_start();

if (!empty($_GET['oauth_verifier']) && !empty($_SESSION['oauth_token']) && !empty($_SESSION['oauth_token_secret'])) {
    // We've got everything we need
    $twitteroauth = new TwitterOAuth(YOUR_CONSUMER_KEY, YOUR_CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
// Let's request the access token
    $access_token = $twitteroauth->getAccessToken($_GET['oauth_verifier']);
// Save it in a session var
    $_SESSION['access_token'] = $access_token;
// Let's get the user's info
    $user_info = $twitteroauth->get('account/verify_credentials');
// Print user's info
    echo '<pre>';
    print_r($user_info);
    echo '</pre><br/>';
    if (isset($user_info->error)) {
        // Something's wrong, go back to square 1  
        header('Location: login-twitter.php');
    } else {
        $social_id = $user_info->id;
        $username = $user_info->name;
        $user = new User();
		$userdata = $user->checkUser($user_info->id, $user_info->name, $user_info->email, $user_info->screen_name, $user_info->url, $user_info->profile_image_url, $access_token, $oauth_token_secret='', "twitter");
		if(!empty($userdata)){
            session_start();
            $_SESSION['id'] = $social_id;
 			$_SESSION['oauth_id'] = $social_id;
            $_SESSION['username'] = $userdata['username'];
            $_SESSION['oauth_provider'] = $userdata['oauth_provider'];
            header("Location: home.php");
        }
    }
} else {
    // Something's missing, go back to square 1
    header('Location: login-twitter.php');
}
?>
