<?php
//include google api files
require_once 'library/google/src/Google_Client.php';
require_once 'library/google/src/contrib/Google_Oauth2Service.php';
require 'config/social_config.php';
require 'config/dbconfig.php';
//start session
session_start();

########## Google Settings.. Client ID, Client Secret from https://cloud.google.com/console #############
$google_client_id       = GoogleClientId;
$google_client_secret   = GoogleClientSecret;
$google_redirect_url    = GoogleRedirectUri; //path to your script
$google_developer_key   = GoogleDeveloperKey;
###################################################################

$gClient = new Google_Client();
$gClient->setApplicationName('Login to Sanwebe.com');
$gClient->setClientId($google_client_id);
$gClient->setClientSecret($google_client_secret);
$gClient->setRedirectUri($google_redirect_url);
$gClient->setDeveloperKey($google_developer_key);

$google_oauthV2 = new Google_Oauth2Service($gClient);

//If user wish to log out, we just unset Session variable
if (isset($_REQUEST['reset'])) 
{
  unset($_SESSION['token']);
  $gClient->revokeToken();
  header('Location: ' . filter_var($google_redirect_url, FILTER_SANITIZE_URL)); //redirect user back to page
}

//If code is empty, redirect user to google authentication page for code.
//Code is required to aquire Access Token from google
//Once we have access token, assign token to session variable
//and we can redirect user back to page and login.
if (isset($_GET['code'])) 
{ 
    $gClient->authenticate($_GET['code']);
    $_SESSION['token'] = $gClient->getAccessToken();
    header('Location: ' . filter_var($google_redirect_url, FILTER_SANITIZE_URL));
    return;
}


if (isset($_SESSION['token'])) 
{ 
    $gClient->setAccessToken($_SESSION['token']);
}


if ($gClient->getAccessToken()) 
{
      //For logged in user, get details from google using access token
      $user                 = $google_oauthV2->userinfo->get();
      $user_id              = $user['id'];
      $user_name            = filter_var($user['name'], FILTER_SANITIZE_SPECIAL_CHARS);
      $email                = filter_var($user['email'], FILTER_SANITIZE_EMAIL);
      $profile_url          = filter_var($user['link'], FILTER_VALIDATE_URL);
      $profile_image_url    = filter_var($user['picture'], FILTER_VALIDATE_URL);
      $personMarkup         = "$email<div><img src='$profile_image_url?sz=50'></div>";
      $_SESSION['token']    = $gClient->getAccessToken();
}
else 
{
    //For Guest user, get google login url
    $authUrl = $gClient->createAuthUrl();
}

//HTML page start
echo '<!DOCTYPE HTML><html>';
echo '<head>';
echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
echo '<title>Login with Google</title>';
echo '</head>';
echo '<body>';
echo '<h1>Login with Google</h1>';

if(isset($authUrl)) //user is not logged in, show login button
{
    echo '<a class="login" href="'.$authUrl.'"><img src="images/google-login-button.png" /></a>';
} 
else // user logged in 
{
	
	require 'config/dbconfig.php';
	
     //compare user id in our database
    $user_exist = mysql_query("SELECT COUNT(social_id) as usercount FROM accounts WHERE social_id=$user_id AND oauth_provider = 'google'"); 
    $row = mysql_fetch_assoc($user_exist);
	if(!empty($row['usercount']))
    {
        $_SESSION['id'] = $user_id;
		$_SESSION['oauth_provider'] = 'google';
		$_SESSION['username'] = $user_name;
    }else{      
        mysql_query("INSERT INTO accounts (social_id, name, username, email, link, picture_link,oauth_provider) 
        VALUES ($user_id, '$user_name','$user_name','$email','$profile_url','$profile_image_url','google')");
		$_SESSION['id'] = $user_id;
		$_SESSION['oauth_provider'] = 'google';
		$_SESSION['username'] = '$user_name';
    }

    
    echo '<br /><a href="'.$profile_url.'" target="_blank"><img src="'.$profile_image_url.'?sz=100" /></a>';
    echo '<br /><a class="logout" href="?reset=1">Logout</a>';
    
    //list all user details
    echo '<pre>'; 
    print_r($user);
    echo '</pre>';  
}
 
echo '</body></html>';
header("Location:home.php");
?>