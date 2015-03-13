<?php
include 'yahoo.php';
if ($_SESSION['login'] == true) {header("Location:home.php");} else {header("Location:?login");}
?>