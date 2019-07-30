<?php 
	include 'model.php';

	unset($_SESSION['logged_user']);
	// unset($_SESSION);
	header('Location: /');
?>