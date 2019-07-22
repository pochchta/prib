<?php 

$test = array(
	'find' => 'role',
	'text' => 'r',
	'find1' => 'role#"',
	'text1' => 'r'
);

$title = 'Профиль';
include 'model.php';
if ( empty($_SESSION['users']->limit) ) {
	$_SESSION['users'] = new table_settings;
}
if ( ! empty($_GET) ) {		// изменение страницы отображения списка пользователей
	if ( isset($_GET['del']) ) $_SESSION['users']->message = del_user( $_GET['del'] ); 		// удаление
	if ( isset($_GET['st']) ) $_SESSION['users']->message = state_user( $_GET['st'] ); 		// состояние
	if ( isset($_GET['lu']) ) limit_users($_GET['lu']); 										// лимит на страницу
	if ( isset($_GET['p']) ) page_users($_GET['p']); 											// номер страницы
	header('Location: /profile.php');
} else {
	if ( isset($_POST['do_sort']) ) sort_users($_POST);
		$find_form = find_users($test);
	// if ( isset($_POST['do_find']) ) find_users($_POST);

	   // v($_SESSION['users']);
	    // v($_POST);
	     v($_SESSION['users']);
	     // v($_SESSION['users']->where);
	     // v($_SESSION['users']->arr_where);
	    

	
	// v( R::find('users', 'role=?', $test) );

	include 'tpl/head.html';
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