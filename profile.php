<?php 
$title = 'Профиль';
include 'model.php';

include 'tpl/head.html';
if ( isset($_SESSION['logged_user']) ) {
    include 'tpl/header/user.html';
} else {
    include 'tpl/header/guest.html';
}
include 'tpl/body/profile.html';

echo '<hr>Список пользователей:<br>';
// var_dump(10);
// var_dump(check_numeric('p'));
read_users(1);

include 'tpl/footer.html';
?>