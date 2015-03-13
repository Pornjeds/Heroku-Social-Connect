<?php
require 'config/social_config.php';
if ( isset( $_GET[ 'code' ] ) ) {
	if ( false == isset( $_GET[ 'state' ] ) )
    	die( 'Warning! State variable missing after authentication' );
  	
	session_start();
  	if ( $_GET[ 'state' ] != $_SESSION[ 'wpcc_state' ] )
    	die( 'Warning! State mismatch. Authentication attempt may have been compromised.' );
 
  	$curl = curl_init( REQUEST_TOKEN_URL );
  	curl_setopt( $curl, CURLOPT_POST, true );
  	curl_setopt( $curl, CURLOPT_POSTFIELDS, array(
    	'client_id' => CLIENT_ID,
    	'redirect_uri' => REDIRECT_URL,
    	'client_secret' => CLIENT_SECRET,
    	'code' => $_GET[ 'code' ], // The code from the previous request
    	'grant_type' => 'authorization_code'
  	) );
 
  	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1);
  	$auth = curl_exec( $curl );
  	$secret = json_decode( $auth );
	
	$access_token = $secret->access_token;
 
	/*$curl = curl_init( "https://public-api.wordpress.com/rest/v1/me" );
	curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Authorization: Bearer ' . $access_token ) );
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1);
	$me = json_decode( curl_exec( $curl ) );*/
	
	$response = file_get_contents('https://public-api.wordpress.com/rest/v1/me/?pretty=1',false,$context);
	$response = json_decode( $response );
	
	echo '<pre>'; print_r($secret);
	echo '<pre>'; print_r($me);

	//TODO: in real app, store the returned token
	echo "Connection successful!";exit;
}
//redirect errors or cancelled requests back to login page
header( "Location: " . LOGIN_URL );
die();