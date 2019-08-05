<?php
	$title = 'Список пользователей';
	include 'model.php';
	if ( $_SESSION['logged_user']->role != 'A' ){
		$_SESSION['errors'][] = 'Нет прав для просмотра данной страницы';
	}
	if ( empty($_SESSION['users']->limit) ) {
		$_SESSION['users'] = new table_settings;
	}
	if ( ! empty($_GET) ) {		// изменение страницы отображения списка пользователей
		if ( isset($_GET['del']) )  del_fields( $_GET['del'] , 'users' ); 			            // удаление
		if ( isset($_GET['st']) ) state_fields( $_GET['st'] , 'users' ); 			            // состояние
		if ( isset($_GET['lu']) ) limit_fields( $_GET['lu'] , 'users' ); 			            // лимит на страницу
		header('Location: /userlist.php');	// для очистки адресной строки
	} else {
		if ( isset($_POST['n_page']) )   	  page_fields( $_POST['n_page'] , 'users' ); 		// номер страницы
		if ( isset($_POST['do_sort']) ) 	  sort_fields( $_POST , 'users' );					// сортировка
		if ( isset($_POST['do_find']) ) 	  find_fields( $_POST , 'users' );				    // поиск
		if ( isset($_POST['do_clear_find']) ) find_fields( array() , 'users' );					// сброс поиска
		if ( empty($_SESSION['users']->find_form[0]) ) $find_form[0] = array('id', '');
		else $find_form = $_SESSION['users']->find_form;
		include 'tpl/head.html';
		include 'tpl/errors.html';
		include 'tpl/message.html';
		if ( isset($_SESSION['logged_user']) ) {
		    include 'tpl/header/user.html';
		} else {
		    include 'tpl/header/guest.html';
		}
		$info = list_fields( 'users' );
		include 'tpl/body/userlist.html';
		include 'tpl/footer.html';
	}
?>