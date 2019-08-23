<?php 
	include 'model.php';

	unset($_SESSION['logged_user']);
	unset($_SESSION['set_item']);
	unset($_SESSION['set_list']);
	unset($_SESSION['errors']);
	unset($_SESSION['messages']);
	unset($_SESSION['POST']);
	unset($_SESSION['GET']);
	header('Location: /');
?>