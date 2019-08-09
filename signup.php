<?php 
$title = 'Регистрация';
include 'model.php';	
if ( isset($_POST['do_signup']) ){
	$reg_info = registering_user($_POST);		
}												
include 'tpl/head.html';
include 'tpl/errors.html';
include 'tpl/message.html';
if ( isset($_SESSION['logged_user']) ) {
    include 'tpl/header/user.html';
} else {
    include 'tpl/header/guest.html';
}
include 'tpl/body/signup.html';
include 'tpl/footer.html';

?>