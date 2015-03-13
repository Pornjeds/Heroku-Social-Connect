<?php
	require 'config/social_config.php';
	require 'library/instagram/instagram.class.php';
	require 'config/dbconfig.php';
	
	// Receive OAuth code parameter
	$code = $_GET['code'];
	$instagram = new Instagram(array(
    	'apiKey'      => InstagramKey,
    	'apiSecret'   => InstagramSecret,
    	'apiCallback' => InstagramRedirectUri 
  	));
	// Check whether the user has granted access
	if (true === isset($code)) {
	// Receive OAuth token object
  	$data = $instagram->getOAuthToken($code);
  	// Take a look at the API response
   
	if(empty($data->user->username))
	{
		header('Location: index.php');
	}
	else
	{
		session_start();
		$_SESSION['userdetails']=$data;
		$user=$data->user->username;
		$fullname=$data->user->full_name;
		$bio=$data->user->bio;
		$website=$data->user->website;
		$user_id=$data->user->id;
		$token=$data->access_token;
		$profile_picture=$data->user->profile_picture;

		$mysql_query=mysql_query("SELECT social_id FROM accounts WHERE social_id='$user_id'");
		if(mysql_num_rows($mysql_query) <= 0)
		{	
			mysql_query("INSERT INTO accounts(username,name,link,social_id,oauth_token,oauth_provider,picture_link) values('$user','$fullname','$website','$user_id','$token','instagram','$profile_picture')");
		}
	header('Location: home.php');
	}
} 
else 
{
	// Check whether an error occurred
	if (true === isset($_GET['error'])) 
	{
    	echo 'An error occurred: '.$_GET['error_description'];
	}
}
?>