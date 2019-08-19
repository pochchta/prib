<?php 
	$title = 'Редактирование записи о приборе';
	include 'model.php';
	if ( ! isset($_SESSION['set_item']['dev']->id) ) {
		$_SESSION['set_item']['dev'] = new item_settings;
		$settings = &$_SESSION['set_item']['dev'];
		$settings->id = 0;		
	}
	if ( $settings->id == 0 )	{
		$title = 'Создание записи о приборе';
	} else {
		$item = one_item( $settings->id , 'devs');
	}
	$double_item_exists = false;
	if ( isset($_POST['do_change_data']) ){
		$double_item_exists = change_dev_data($_POST, 'devs');
		$item = $_POST;
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