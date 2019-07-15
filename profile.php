<?php 
$title = 'Профиль';
include 'model.php';
if ( ! empty($_GET) ) {		// изменение страницы отображения списка пользователей
	if ( isset($_GET['del']) ) $_SESSION['message'] = del_user( $_GET['del'] ); 		// удаление
	if ( isset($_GET['st']) ) $_SESSION['message'] = state_user( $_GET['st'] ); 		// состояние
	if ( isset($_GET['lu']) ) limit_users($_GET['lu']); 											// лимит на страницу
	if ( isset($_GET['p']) ) page_users($_GET['p']); 												// номер страницы
	header('Location: /profile.php');
}
// $info_del = del_user();
// $info_state = state_user();
// v($info_state);

include 'tpl/head.html';
if ( isset($_SESSION['logged_user']) ) {
    include 'tpl/header/user.html';
} else {
    include 'tpl/header/guest.html';
}
// v($_SESSION);

$info = list_users();
include 'tpl/body/profile.html';





include 'tpl/footer.html';
?>