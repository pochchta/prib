<?php 
$title = 'Профиль';
include 'model.php';
if ( empty($_SESSION['users']->limit) ) {
	$_SESSION['users'] = new table_settings;
}
if ( ! empty($_GET) ) {		// изменение страницы отображения списка пользователей
	if ( isset($_GET['del']) ) $_SESSION['users']->message = del_user( $_GET['del'] ); 			// удаление
	if ( isset($_GET['st']) ) $_SESSION['users']->message = state_user( $_GET['st'] ); 			// состояние
	if ( isset($_GET['lu']) ) limit_users($_GET['lu']); 										// лимит на страницу
	if ( isset($_GET['p']) ) page_users($_GET['p']); 											// номер страницы
	header('Location: /profile.php');
} else {
	if ( isset($_POST['do_sort']) ) sort_users($_POST);
	if ( isset($_POST['do_find']) ) $_SESSION['users']->find_form = find_users($_POST);
	if ( isset($_POST['do_clear_find']) ) $_SESSION['users']->find_form = find_users(array());
	if ( empty($_SESSION['users']->find_form[0]) ) $find_form[0] = array('id', '');
	else $find_form = $_SESSION['users']->find_form;
// v($_POST);
 // v($find_form);
 // v($_SESSION['errors']);
	include 'tpl/head.html';
	include 'tpl/errors.html';
	if ( isset($_SESSION['logged_user']) ) {
	    include 'tpl/header/user.html';
	} else {
	    include 'tpl/header/guest.html';
	}
	$info = list_users();
	include 'tpl/body/profile.html';
	include 'tpl/footer.html';
	$_SESSION['users']->message = '';
}
?>