<?php
//Always place this code at the top of the Page
session_start();
if (isset($_SESSION['id'])) {
    // Redirection to login page twitter or facebook
    header("location: home.php");
}

if (array_key_exists("login", $_GET)) {
    $oauth_provider = $_GET['oauth_provider'];
    if ($oauth_provider == 'twitter') {
        header("Location: login-twitter.php");
    } else if ($oauth_provider == 'facebook') {
        header("Location: login-facebook.php");
    } else if ($oauth_provider == 'google') {
        header("Location: login-google.php");
    } else if ($oauth_provider == 'yahoo') {
        header("Location: login-yahoo.php");
  	} else if ($oauth_provider == 'linkedin') {
        header("Location: login-linkedin.php");
    } else if ($oauth_provider == 'instagram') {
        header("Location: login-instagram.php");
    } else if ($oauth_provider == 'wordpress') {
        header("Location: login-wordpress.php");
    }
}
?>
<title>Social Connect Login (Facebook | Twitter | Yahoo | Gmail | LinkedIn)</title>
<style type="text/css">
#buttons {
	text-align: center
}
#buttons img,  #buttons a img {
	border: none;
}
h1 {
	font-family: Arial, Helvetica, sans-serif;
	color: #999999;
}
</style>

<div id="buttons">
  <h1>Google, Twitter, Facebook & LinkedIn Login </h1>
  <a href='?login&oauth_provider=google'><img style='cursor: pointer; cursor: hand;' src='images/google.png'></a><BR/><BR/><a href="?login&oauth_provider=twitter"><img src="images/twitter.png"></a><BR/><BR/><a href="?login&oauth_provider=facebook"><img src="images/facebook.png"></a><BR/><BR/><a href="?login&oauth_provider=linkedin"><img src="images/linkedin.png"></a><BR/><BR/><a href="?login&oauth_provider=yahoo"><img src="images/yahoo.png"></a> <BR/><BR/><a href="?login&oauth_provider=instagram"><img width="157" src="images/instagram-login-button.png"></a> <BR/><BR/><a href="?login&oauth_provider=wordpress"><img width="157" src="images/wordpress.png"></a> <br />
</div>
