<?php 
//require 'db.php';
$title = 'Авторизация';
include 'model.php';	
$errors = logging_user($_POST);
if ( empty($errors) ){
	$message =  'Вы вошли как '.$_SESSION['logged_user']->login;
	if ($_SESSION['logged_user']->login != 'guest') $message_class = 'ok';
	else $message_class = 'none';
}else {
	$message = array_shift($errors);
	$message_class = 'error';
}
	
include 'tpl/head.html';
if ( $_SESSION['logged_user']->login != 'guest' ) {
    include 'tpl/header/user.html';
} else {
    include 'tpl/header/guest.html';
}
include 'tpl/body/login.html';
include 'tpl/footer.html'

?>