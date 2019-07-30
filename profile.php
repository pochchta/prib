<?php 
$title = 'Профиль';
include 'model.php';
if ( empty($_SESSION['users']->limit) ) {
	$_SESSION['users'] = new table_settings;
}
if ( ! empty($_GET) ) {		// изменение страницы отображения списка пользователей
	if ( isset($_GET['del']) )    del_user( $_GET['del'] , 'users' ); 			// удаление
	if ( isset($_GET['st']) )   state_user( $_GET['st'] , 'users' ); 			// состояние
	if ( isset($_GET['lu']) ) limit_fields( $_GET['lu'] , 'users' ); 			// лимит на страницу
	if ( isset($_GET['p']) )   page_fields( $_GET['p'] , 'users' ); 			// номер страницы
	header('Location: /profile.php');
} else {
	if ( isset($_POST['do_sort']) ) sort_fields( $_POST , 'users' );
	if ( isset($_POST['do_find']) ) 	  find_fields( $_POST , 'users' );
	if ( isset($_POST['do_clear_find']) ) find_fields( array() , 'users' );
	if ( empty($_SESSION['users']->find_form[0]) ) $find_form[0] = array('id', '');
	else $find_form = $_SESSION['users']->find_form;

// v($_POST);
 // v($find_form);
 // v($_SESSION['users']);
	// $_SESSION['users']->where = 'WHERE role LIKE ? AND state LIKE ? AND state LIKE ?';

	// $user = R::load('users', 29);
	// $enter = R::dispense('enter');
	// $enter->date = 'Вчера заходил вроде';
	// $user->xownEnterList[] = $enter;
	// R::store($user);

	include 'tpl/head.html';
	include 'tpl/errors.html';
	if ( isset($_SESSION['logged_user']) ) {
	    include 'tpl/header/user.html';
	} else {
	    include 'tpl/header/guest.html';
	}
	$info = list_fields( 'users' );
	include 'tpl/body/profile.html';
	include 'tpl/footer.html';
	$_SESSION['errors'] = array();
}
?>