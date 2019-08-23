<?php 
	$title = 'Создание записи о приборе';
	$address = '/dev.php';
	include 'model.php';
	if ( empty($_POST) == false ){
		$_SESSION['POST'] = $_POST;
		header('Location: '.$address);
	} elseif ( empty($_GET) == false ){
		$_SESSION['GET'] = $_GET;
		header('Location: '.$address);
	} else{

		if ( ! isset($_SESSION['set_item']['dev']->id) ) {
			$_SESSION['set_item']['dev'] = new item_settings;
		}
		$settings = &$_SESSION['set_item']['dev'];
		if ( isset($_SESSION['GET']['p']) && check_numeric($_SESSION['GET']['p']+1) ){
			$settings->id = $_SESSION['GET']['p'];
		}
		if ( isset($_SESSION['POST']['change_test_double']) ){
			if ( $_SESSION['POST']['checkbox'] == 'test' )	$settings->test_double = true;
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
		if ( isset($_SESSION['POST']['do_change_data']) && $item_exists ){
			$out = change_dev_data($_SESSION['POST'], 'devs' , $settings->id , $settings->test_double);
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

		unset($_SESSION['POST']);
		unset($_SESSION['GET']);
	}
?>