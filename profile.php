<?php 
$title = 'Профиль';
include 'model.php';

include 'tpl/head.html';
if ( isset($_SESSION['logged_user']) ) {
    include 'tpl/header/user.html';
} else {
    include 'tpl/header/guest.html';
}
$info = read_users();
del_user();
include 'tpl/body/profile.html';


 // var_dump($_GET['lu']);
// var_dump(check_numeric('p'));


include 'tpl/footer.html';
?>