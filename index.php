<?php 
$title = 'Главная страница';
include 'model.php';

include 'tpl/head.html';
if ( $_SESSION['logged_user']->login != 'guest' ) {
    include 'tpl/header/user.html';
} else {
    include 'tpl/header/guest.html';
}
//include 'tpl/body/one.html';
include 'tpl/footer.html'
?>