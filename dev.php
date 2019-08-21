<?php 
	$title = 'Создание записи о приборе';
	include 'model.php';
	if ( ! isset($_SESSION['set_item']['dev']->id) ) {
		$_SESSION['set_item']['dev'] = new item_settings;
	}
	$settings = &$_SESSION['set_item']['dev'];
	if ( isset($_GET['p']) && check_numeric($_GET['p']+1) ){
		$settings->id = $_GET['p'];
	}
	if ( isset($_POST['change_test_double']) ){
		if ( $_POST['checkbox'] == 'test' )	$settings->test_double = true;
		else $settings->test_double = false;
	}

	$item_exists = true;
	if ( $settings->id ) {
		$item = one_item( $settings->id , 'devs');
		$title = "Редактирование записи о приборе (id = {$settings->id})";
		if ( $item->id == 0 ) {
			$_SESSION['errors'][] = 'Запись не найдена';
			$item_exists = false;
		}
	}

	$double_item_exists = false;
	if ( isset($_POST['do_change_data']) && $item_exists ){
		$out = change_dev_data($_POST, 'devs' , $settings->id , $settings->test_double);
		$item = $out['item'];
		if ( $item->id ) {
			$settings->id = $item->id;
			$title = "Редактирование записи о приборе (id = {$settings->id})";			
		}
		$double_item_exists = $out['double_item_exists'];
	}
	include 'tpl/head.html';	//v($_POST);
	include 'tpl/errors.html';	//v($settings);//v($item);
	include 'tpl/message.html';
	if ( isset($_SESSION['logged_user']) ) {
		include 'tpl/header/user.html';
	} else {
		include 'tpl/header/guest.html';
	}
	include 'tpl/body/dev.html';
	include 'tpl/footer.html';
?>