<?php 
$title = 'Профиль';
include 'model.php';

include 'tpl/head.html';
if ( isset($_SESSION['logged_user']) ) {
    include 'tpl/header/user.html';
} else {
    include 'tpl/header/guest.html';
}
var_dump(mb_strlen('Федор Васильевич 12 abcde' , 'UTF-8'));
include 'tpl/body/profile.html';
include 'tpl/footer.html';
?>