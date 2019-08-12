<?php 
	$title = 'Профиль';
	include 'model.php';
	if (  test_perm('r_self') || test_perm('r_user') ){
		if ( test_perm('r_user' , true) ){
			if ( ! isset($_SESSION['set_item']['user']->id) ) {
				$_SESSION['set_item']['user'] = new item_settings;
			}
			$settings = &$_SESSION['set_item']['user'];
			if ( isset($_POST['user_id']) )
				if ( test_perm('r_user') )
					$settings->id = $_POST['user_id'];
			if ( isset($_POST['reset_user_id']) )
				if ( test_perm('r_user') )
					unset( $settings->id );
			if ( $settings->id ) {
				$user_info = one_item( $settings->id , 'users' );
			}
		}
		if ( ! $user_info->id ) $user_info = $_SESSION['logged_user'];

		if ( isset($_POST['do_change_data']) ) {
			if ( test_perm('w_user_data') && test_perm('r_user') ){
				change_data( $_POST , 'users' , $user_info->id );
				$user_info = one_item( $user_info->id , 'users' );
				if ( $user_info->id == $_SESSION['logged_user']->id ) $_SESSION['logged_user'] = $user_info;
			}
		}
		if ( isset($_POST['do_change_pass']) )
			if ( ( ($user_info->id == $_SESSION['logged_user']->id) && test_perm('w_self_pass') ) || test_perm('w_user_pass') )
				change_pass( $_POST , 'users' , $user_info->id );
	}
	if ( ! empty($_POST) ) header('Location: /profile.php');  // для очистки $_POST массива	
	else {
		$perm_profile = false;
		if ( ( ($user_info->id == $_SESSION['logged_user']->id) && test_perm('r_self') ) || test_perm('r_user') )
			$perm_profile = true;
		include 'tpl/head.html';
		include 'tpl/errors.html';
		include 'tpl/message.html';
		if ( isset($_SESSION['logged_user']) ) {
			include 'tpl/header/user.html';
		} else {
			include 'tpl/header/guest.html';
		}
		if ( $perm_profile )
			include 'tpl/body/profile.html';
		include 'tpl/footer.html';
	}
?>