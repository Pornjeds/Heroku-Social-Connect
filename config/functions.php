<?php
require 'social_config.php';
require 'dbconfig.php';
class User {
    function checkUser($social_id, $name, $email, $username, $link, $picture_link, $oauth_token, $oauth_token_secret, $oauth_provider) 
	{
        $query = mysql_query("SELECT * FROM `accounts` WHERE social_id  = '$social_id' and oauth_provider = '$oauth_provider'") or die(mysql_error());
        $result = mysql_fetch_array($query);
        if (!empty($result)) {
            # User is already present
        } else {
            #user not present. Insert a new Record
            $query = mysql_query("INSERT INTO `accounts` (social_id, name, email, username, link, picture_link, oauth_token, oauth_token_secret, oauth_provider) VALUES ('$social_id', '$name', '$email', '$username', '$link', '$picture_link', '$oauth_token', '$oauth_token_secret', '$oauth_provider')") or die(mysql_error());
            $query = mysql_query("SELECT * FROM `accounts` WHERE social_id  = '$social_id' and oauth_provider = '$oauth_provider'");
            $result = mysql_fetch_array($query);
            return $result;
        }
        return $result;
    }
}
?>
