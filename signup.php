<?php 
$title = 'Регистрация';
include 'model.php';	
$message_class = 'none';
if ( isset($_POST['do_signup']) ){
	$reg_info = registering_user($_POST);
	if ( $reg_info['ok'] ){
		$message =  'Регистрация прошла успешно';
		$message_class = 'ok';
	}else {
		if ( ! empty($reg_info['errors']) ){
			$message = array_shift($reg_info['errors']);
			$message_class = 'error';
		}	
	}		
}												
include 'tpl/head.html';
if ( isset($_SESSION['logged_user']) ) {
    include 'tpl/header/user.html';
} else {
    include 'tpl/header/guest.html';
}
include 'tpl/body/signup.html';
include 'tpl/footer.html';

?>