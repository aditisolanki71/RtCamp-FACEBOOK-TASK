<?php
session_start();
if(isset($_SESSION['facebook_access_token'])){
			session_unset('facebook_access_token');
			header('location:index.php');
}
session_destroy();
?>