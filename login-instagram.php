<?php
	session_start();
	require 'library/instagram/instagram.class.php';
	require 'config/social_config.php';
	$instagram = new Instagram(array(
    	'apiKey'      => InstagramKey,
    	'apiSecret'   => InstagramSecret,
    	'apiCallback' => InstagramRedirectUri 
  	));
	$loginUrl = $instagram->getLoginUrl();
	header("Location:".$loginUrl);
?>