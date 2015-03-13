<?php
  	// Remember to copy files from the SDK's src/ directory to a
  	// directory in your application on the server, such as php-sdk/
 	require 'config/social_config.php';
	require 'library/facebook/src/facebook.php';
	require 'config/dbconfig.php';
	
	$config = array(
		'appId' => APP_ID,
		'secret' => APP_SECRET,
		'allowSignedRequest' => false // optional but should be set to false for non-canvas apps
	);

	$facebook = new Facebook($config);
	$user_id = $facebook->getUser();
	if($user_id) {
	
	  // We have a user ID, so probably a logged in user.
	  // If not, we'll get an exception, which we handle below.
	  try {
	
		$user_profile = $facebook->api('/me','GET');
		$verifyConnection = mysql_query("SELECT * FROM accounts WHERE social_id = '".$user_profile['id']."'");
		if(mysql_num_rows($verifyConnection)<=0){
			$InsertConnection = "
				INSERT INTO accounts
				SET
				social_id 			= '".$user_profile['id']."',
				oauth_token 		= '',
				oauth_token_secret 	= '',
				picture_link		= '',
				name 				= '".$user_profile['name']."',
				link 				= '".$user_profile['link']."'	,
				oauth_provider 		= 'facebook';		
			";
			mysql_query($InsertConnection);
			
			session_start();
			$_SESSION['id'] 		= $user_profile['id'];
			$_SESSION['oauth_id']	= $user_profile['id'];
			$_SESSION['username'] 	= $user_profile['name'];
			$_SESSION['oauth_provider'] = 'facebook';
			header("Location: home.php");
		}else{
			session_start();
			$_SESSION['id'] 		= $user_profile['id'];
			$_SESSION['oauth_id'] 	= $user_profile['id'];
			$_SESSION['username'] 	= $user_profile['name'];
			$_SESSION['oauth_provider'] = 'facebook';
			header("Location: home.php");
		}			
	  } catch(FacebookApiException $e) {
		// If the user is logged out, you can have a 
		// user ID even though the access token is invalid.
		// In this case, we'll get an exception, so we'll
		// just ask the user to login again here.
		$login_url = $facebook->getLoginUrl(); 
		echo 'Please <a href="' . $login_url . '">login.</a>';
		error_log($e->getType());
		error_log($e->getMessage());
	  }   
	} else {
	
	  // No user, print a link for the user to login
	  $login_url = $facebook->getLoginUrl();
	  header("Location:".$login_url);
	
	}
?>