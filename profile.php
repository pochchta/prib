<?php 
	$title = 'Профиль';
	include 'model.php';
	if ( empty($_SESSION['set_item']['user']) ) {
		$_SESSION['set_item']['user'] = new item_settings;
	}
	$settings = &$_SESSION['set_item']['user'];
	if ( isset($_POST['user_id']) ) {
		$settings->id = $_POST['user_id'];
	}
	if ( isset($_POST['reset_user_id']) ) {
		unset( $settings->id );
	}
	if ( $settings->id ) {
		$user_info = one_item( $settings->id , 'users' );
	}
	if ( ! $user_info->id ) $user_info = $_SESSION['logged_user'];

	if ( isset($_POST['do_change_pass']) ) change_pass( $_POST , 'users' , $user_info->id );
	if ( isset($_POST['do_change_data']) ) {
		change_data( $_POST , 'users' , $user_info->id );
		if ( ! $user_info->id ) $user_info = $_SESSION['logged_user'];
		else $user_info = one_item( $user_info->id , 'users' );
	}	
			  // $test = '12345678901234567890123456789012345678901234567890123456789Ё';
			  // v(check_symbol($test));
			// v($_SESSION['users']->find_form);
			// v($_SESSION['messages']);
			// v($_POST);
			// $_SESSION['users']->where = 'WHERE role LIKE ? AND state LIKE ? AND state LIKE ?';

			// $user = R::load('users', 29);
			// $enter = R::dispense('enter');
			// $enter->date = 'Вчера заходил вроде';
			// $user->xownEnterList[] = $enter;
			// R::store($user);

	include 'tpl/head.html';
	include 'tpl/errors.html';
	include 'tpl/message.html';
	if ( isset($_SESSION['logged_user']) ) {
		include 'tpl/header/user.html';
	} else {
		include 'tpl/header/guest.html';
	}
	include 'tpl/body/profile.html';
	include 'tpl/footer.html';
?>