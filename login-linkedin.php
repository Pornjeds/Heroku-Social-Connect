<?php
// Change these

	require 'config/social_config.php';
	require 'config/dbconfig.php';
	
	function oauth_session_exists() {
		if((is_array($_SESSION)) && (array_key_exists('oauth', $_SESSION))) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	try 
	{
		// include the LinkedIn class
		
		require_once('library/linkedin/linkedin_3.2.0.class.php');
	
		// start the session
		if(!session_start()) {
			throw new LinkedInException('This script requires session support, which appears to be disabled according to session_start().');
		}
	
		// display constants
		$API_CONFIG = array(
			'appKey'       => LinkedInKey,
			'appSecret'    => LinkedInSecret,
			'callbackUrl'  => LinkedInRedirectURL 
		);
	
		define('PORT_HTTP', '80');
		define('PORT_HTTP_SSL', '443');
	
		// set index
		$_REQUEST[LINKEDIN::_GET_TYPE] = (isset($_REQUEST[LINKEDIN::_GET_TYPE])) ? $_REQUEST[LINKEDIN::_GET_TYPE] : '';
		switch($_REQUEST[LINKEDIN::_GET_TYPE]) {
			case 'initiate':
			
					
			/**
			* Handle user initiated LinkedIn connection, create the LinkedIn object.
			*/
			
			// check for the correct http protocol (i.e. is this script being served via http or https)
			if($_SERVER['HTTPS'] == 'on') {
				$protocol = 'https';
			} else {
				$protocol = 'http';
			}
			
			// set the callback url
			$API_CONFIG['callbackUrl'] = $protocol . '://' . $_SERVER['SERVER_NAME'] . ((($_SERVER['SERVER_PORT'] != PORT_HTTP) || ($_SERVER['SERVER_PORT'] != PORT_HTTP_SSL)) ? ':' . $_SERVER['SERVER_PORT'] : '') . $_SERVER['PHP_SELF'] . '?' . LINKEDIN::_GET_TYPE . '=initiate&' . LINKEDIN::_GET_RESPONSE . '=1';
			$OBJ_linkedin = new LinkedIn($API_CONFIG);
			
			// check for response from LinkedIn
			$_GET[LINKEDIN::_GET_RESPONSE] = (isset($_GET[LINKEDIN::_GET_RESPONSE])) ? $_GET[LINKEDIN::_GET_RESPONSE] : '';
			if(!$_GET[LINKEDIN::_GET_RESPONSE]) {
			// LinkedIn hasn't sent us a response, the user is initiating the connection
			
			// send a request for a LinkedIn access token
				$response = $OBJ_linkedin->retrieveTokenRequest();
				if($response['success'] === TRUE) {
				// store the request token
					$_SESSION['oauth']['linkedin']['request'] = $response['linkedin'];
					
					// redirect the user to the LinkedIn authentication/authorisation page to initiate validation.
					header('Location: ' . LINKEDIN::_URL_AUTH . $response['linkedin']['oauth_token']);
				} else {
					// bad token request
					echo "Request token retrieval failed:<br /><br />RESPONSE:<br /><br /><pre>" . print_r($response, TRUE) . "</pre><br /><br />LINKEDIN OBJ:<br /><br /><pre>" . print_r($OBJ_linkedin, TRUE) . "</pre>";
				}
			} else {
				// LinkedIn has sent a response, user has granted permission, take the temp access token, the user's secret and the verifier to request the user's real secret key
				$response = $OBJ_linkedin->retrieveTokenAccess($_SESSION['oauth']['linkedin']['request']['oauth_token'], $_SESSION['oauth']['linkedin']['request']['oauth_token_secret'], $_GET['oauth_verifier']);
				if($response['success'] === TRUE) {
					// the request went through without an error, gather user's 'access' tokens
					
				
					$_SESSION['oauth']['linkedin']['access'] = $response['linkedin'];
					$OBJ_linkedin = new LinkedIn($API_CONFIG);
					$OBJ_linkedin->setTokenAccess($_SESSION['oauth']['linkedin']['access']);
					
					$response = $OBJ_linkedin->profile('~:(id,first-name,last-name,public-profile-url,picture-url,current-status,positions:(title,company),picture-urls::(original))');
					if($response['success'] === TRUE) {
						$response['linkedin'] = new SimpleXMLElement($response['linkedin']);
						$profile = $response['linkedin'];
					} else {
						echo "Error retrieving profile information:<br /><br />RESPONSE:<br /><br /><pre>" . print_r($response) . "</pre>";
					} 
					$xml = simplexml2array($profile);
					require 'config/social_config.php';
	require 'config/dbconfig.php';
					$ProfileId = $xml['id'];
					$verifyConnection = mysql_query("SELECT * FROM accounts WHERE social_id = '".$ProfileId."'");
					if(mysql_num_rows($verifyConnection)<=0){
						$InsertConnection = "
							INSERT INTO accounts
							SET
							social_id 		= '".$ProfileId."',
							oauth_token 		= '".$_SESSION['oauth']['linkedin']['access']['oauth_token']."',
							oauth_token_secret 	= '".$_SESSION['oauth']['linkedin']['access']['oauth_token_secret']."',
							picture_link			= '".$xml['picture-url']."',
							name 				= '".$xml['first-name']." ".$xml['last-name']."',
							link 					= '".$xml['public-profile-url']."'	,
							oauth_provider = 'linkedin';		
						";
						mysql_query($InsertConnection);
						
						session_start();
						$_SESSION['id'] = $ProfileId;
						$_SESSION['oauth_id'] = $ProfileId;
						$_SESSION['username'] = $xml['first-name']." ".$xml['last-name'];
						$_SESSION['oauth_provider'] = 'linkedin';
						header("Location: home.php");
					}else{
						session_start();
						$_SESSION['id'] = $ProfileId;
						$_SESSION['oauth_id'] = $ProfileId;
						$_SESSION['username'] = $xml['first-name']." ".$xml['last-name'];
						$_SESSION['oauth_provider'] = 'linkedin';
						header("Location: home.php");
					}				
				} else {
					// bad token access
					echo "Access token retrieval failed:<br /><br />RESPONSE:<br /><br /><pre>" . print_r($response, TRUE) . "</pre><br /><br />LINKEDIN OBJ:<br /><br /><pre>" . print_r($OBJ_linkedin, TRUE) . "</pre>";
				}
			}
			break;
			
			case 'revoke':
			/**
			* Handle authorization revocation.
			*/
			
			// check the session
			if(!oauth_session_exists()) {
				throw new LinkedInException('This script requires session support, which doesn\'t appear to be working correctly.');
			}
			
			$OBJ_linkedin = new LinkedIn($API_CONFIG);
			$OBJ_linkedin->setTokenAccess($_SESSION['oauth']['linkedin']['access']);
			$response = $OBJ_linkedin->revoke();
			if($response['success'] === TRUE) {
				// revocation successful, clear session
				session_unset();
				$_SESSION = array();
				if(session_destroy()) {
					// session destroyed
					header('Location: ' . $_SERVER['PHP_SELF']);
				} else {
					// session not destroyed
					echo "Error clearing user's session";
				}
			} else {
				// revocation failed
				echo "Error revoking user's token:<br /><br />RESPONSE:<br /><br /><pre>" . print_r($response, TRUE) . "</pre><br /><br />LINKEDIN OBJ:<br /><br /><pre>" . print_r($OBJ_linkedin, TRUE) . "</pre>";
			}
			break;
			
			default:
			// nothing being passed back, display demo page
			
			// check PHP version
			if(version_compare(PHP_VERSION, '5.0.0', '<')) {
				throw new LinkedInException('You must be running version 5.x or greater of PHP to use this library.'); 
			} 
			
			
			
			// check for cURL
			if(extension_loaded('curl')) {
				$curl_version = curl_version();
				$curl_version = $curl_version['version'];
			} else {
				throw new LinkedInException('You must load the cURL extension to use this library.'); 
			}
			?>
			<h2 align="center" id="manage">Processing please wait...</h2>
			<?php
			if($_SESSION['oauth']['linkedin']['authorized'] === TRUE) {
			// user is already connected
			?>
				<form id="linkedin_revoke_form" action="<?php echo $_SERVER['PHP_SELF'];?>" method="get">
				<input type="hidden" name="<?php echo LINKEDIN::_GET_TYPE;?>" id="<?php echo LINKEDIN::_GET_TYPE;?>" value="revoke" />
				<input type="hidden" value="Revoke Authorization" />
				</form>
			<?php
			} else {
			// user isn't connected
			?><?php echo LINKEDIN::_GET_TYPE;?>
				<form id="linkedin_connect_form" name="linkedin_connect_form" action="<?php echo $_SERVER['PHP_SELF'];?>" method="get">
				<input type="hidden" name="<?php echo LINKEDIN::_GET_TYPE;?>" id="<?php echo LINKEDIN::_GET_TYPE;?>" value="initiate" />
				<input type="hidden" value="Connect to LinkedIn" />
				</form><script>document.linkedin_connect_form.submit();</script>
			<?php
			}
			?>
            
			<?php
			break;
		}
	}catch(LinkedInException $e) {
		echo $e->getMessage();
	}
	
function simplexml2array($xml) {
		if (@get_class($xml) == 'SimpleXMLElement') {
			$attributes = $xml->attributes();
			foreach($attributes as $k=>$v) {
				if ($v) $a[$k] = (string) $v;
			}
			$x = $xml;
			$xml = get_object_vars($xml);
		}
		if (is_array($xml)) {
			if (count($xml) == 0) return (string) $x; // for CDATA
			foreach($xml as $key=>$value) {
				$r[$key] = simplexml2array($value);
			}
			if (isset($a)) $r['@attributes'] = $a;    // Attributes
			return $r;
		}
		return (string) $xml;
	}	
 
 
exit; 
// You'll probably use a database
session_name('linkedin');
session_start();
 
// OAuth 2 Control Flow
if (isset($_GET['error'])) {
    // LinkedIn returned an error
    print $_GET['error'] . ': ' . $_GET['error_description'];
    exit;
} elseif (isset($_GET['code'])) {
    // User authorized your application
    if ($_SESSION['state'] == $_GET['state']) {
        // Get token so you can make API calls
        getAccessToken();
    } else {
        // CSRF attack? Or did you mix up your states?
        exit;
    }
} else { 
    if ((empty($_SESSION['expires_at'])) || (time() > $_SESSION['expires_at'])) {
        // Token has expired, clear the state
        $_SESSION = array();
    }
    if (empty($_SESSION['access_token'])) {
        // Start authorization process
        getAuthorizationCode();
    }
}
 
// Congratulations! You have a valid token. Now fetch your profile 

function getAuthorizationCode() {
    $params = array('response_type' => 'code',
                    'client_id' => LinkedInKey,
                    'scope' => SCOPE,
                    'state' => uniqid('', true), // unique long string
                    'redirect_uri' => LinkedInRedirectURL,
              );
 
    // Authentication request
    $url = 'https://www.linkedin.com/uas/oauth2/authorization?' . http_build_query($params);
     
    // Needed to identify request when it returns to us
    $_SESSION['state'] = $params['state'];
 
    // Redirect user to authenticate
    header("Location: $url");
    exit;
}
     
function getAccessToken() {
    $params = array('grant_type' => 'authorization_code',
                    'client_id' => LinkedInKey,
                    'client_secret' => LinkedInSecret,
                    'code' => $_GET['code'],
                    'redirect_uri' => LinkedInRedirectURL,
              );
     
    // Access Token request
    $url = 'https://www.linkedin.com/uas/oauth2/accessToken?' . http_build_query($params);
     
    // Tell streams to make a POST request
    $context = stream_context_create(
                    array('http' => 
                        array('method' => 'POST',
                        )
                    )
                );
 
    // Retrieve access token information
    $response = @file_get_contents($url, false, $context);
 
    // Native PHP object, please
    $token = json_decode($response);
 
    // Store access token and expiration time
    $_SESSION['access_token'] = $token->access_token; // guard this! 
    $_SESSION['expires_in']   = $token->expires_in; // relative time (in seconds)
    $_SESSION['expires_at']   = time() + $_SESSION['expires_in']; // absolute time
     
    return true;
}
 
function fetch($method, $resource, $body = '') {
    $params = array('oauth2_access_token' => $_SESSION['access_token'],
                    'format' => 'json',
              );
     
    // Need to use HTTPS
    $url = 'https://api.linkedin.com' . $resource . '?' . http_build_query($params);
    // Tell streams to make a (GET, POST, PUT, or DELETE) request
    $context = stream_context_create(
                    array('http' => 
                        array('method' => $method,
                        )
                    )
                );
 
 
    // Hocus Pocus
    $response = @file_get_contents($url, false, $context);
 
    // Native PHP object, please
    return json_decode($response);
}

$user = fetch('GET', '/v1/people/~:(firstName,lastName)');
print_r($user);
print "Hello $user->firstName $user->lastName.";
exit;