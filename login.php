<?php 
$title = 'Авторизация';
include 'model.php';
$message_class = 'none';
if ( isset($_POST['do_login']) ){
	$errors = logging_user($_POST);
	if ( empty($errors) ){
		if ( isset($_SESSION['logged_user']) ){
			$message =  'Вы вошли как '.$_SESSION['logged_user']->login;
			$message_class = 'ok';
		}
	}else {
		$message = array_shift($errors);
		$message_class = 'error';
	}
}
include 'tpl/head.html';
if ( isset($_SESSION['logged_user']) ) {
    include 'tpl/header/user.html';
} else {
    include 'tpl/header/guest.html';
}
include 'tpl/body/login.html';
include 'tpl/footer.html';

?>