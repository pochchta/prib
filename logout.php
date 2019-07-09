<?php 
	include 'model.php';

	unset($_SESSION['logged_user']);
	header('Location: /');
?>