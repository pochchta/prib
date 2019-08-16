<?php 
	$title = 'Создание и редактирование записи о приборе';
	include 'model.php';
	$double_item_exists = false;
	if ( isset($_POST['do_change_data']) ){
		$double_item_exists = change_dev_data($_POST, 'devs');
	}
	include 'tpl/head.html';
	include 'tpl/errors.html';	v($_POST);
	include 'tpl/message.html';
	if ( isset($_SESSION['logged_user']) ) {
		include 'tpl/header/user.html';
	} else {
		include 'tpl/header/guest.html';
	}
	include 'tpl/body/dev.html';
	include 'tpl/footer.html';
?>