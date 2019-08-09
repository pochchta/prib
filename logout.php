<?php 
	include 'model.php';

	unset($_SESSION['logged_user']);
	unset($_SESSION['set_item']);
	unset($_SESSION['set_list']);
	header('Location: /');
?>