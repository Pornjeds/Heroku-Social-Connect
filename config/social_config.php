<?php
	define("SITE_URL","");

/* Google API credentials */
	define("GoogleClientId","");
	define("GoogleClientSecret","");
	define("GoogleRedirectUri",SITE_URL."");
	define("GoogleDeveloperKey","");
/* End */

/* Yahoo API credentials */
	define("OAUTH_CONSUMER_KEY","");
	define("OAUTH_CONSUMER_SECRET","");
	define("OAUTH_DOMAIN",SITE_URL);
	define("OAUTH_APP_ID","");
/* End */

/* Facebook API credentials */
	define('APP_ID', '');
	define('APP_SECRET', '');
/* End */

/* Twitter API credentials */
	define('YOUR_CONSUMER_KEY', '');
	define('YOUR_CONSUMER_SECRET', '');
/* End */

/* Instagram API credentials */
	define('InstagramKey', '');
	define('InstagramSecret', '');
	define("InstagramRedirectUri",SITE_URL."/instagram.php");
/* End */

/* LinkedIn API credentials */
	define('LinkedInKey', '');
	define('LinkedInSecret', '');
	define("LinkedInRedirectURL","http://" . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME']);
	define('SCOPE','r_fullprofile r_emailaddress rw_nus'); 
/* End */	

	define ( 'CLIENT_ID', '' ); //TODO
	define ( 'CLIENT_SECRET', '' ); //TODO
	define ( 'LOGIN_URL', SITE_URL.'/login-wordpress.php' ); //TODO
	define ( 'REDIRECT_URL', SITE_URL.'/wordpress.php' ); //TODO
	define ( 'REQUEST_TOKEN_URL', 'https://public-api.wordpress.com/oauth2/token' );
	define ( 'AUTHENTICATE_URL', 'https://public-api.wordpress.com/oauth2/authenticate' );

define('DB_SERVER', '');
define('DB_USERNAME', '');
define('DB_PASSWORD', '');
define('DB_DATABASE', '');
?>