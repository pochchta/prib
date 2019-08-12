<?php
	$title = 'Список пользователей';
	include 'model.php';
	if ( test_perm('r_users') )
		if ( empty($_SESSION['set_list']['users']->limit) )
			$_SESSION['set_list']['users'] = new table_settings;
	if ( ! empty($_GET) ) {									// изменение страницы отображения списка пользователей
		if ( test_perm('r_users') ){
			if ( isset($_GET['del']) )   		            									// удаление юзера
				if ( test_perm('w_user_del') )
					del_fields( $_GET['del'] , 'users' );
			if ( isset($_GET['st']) )  															// состояние юзера
				if ( test_perm('w_user_data') )
					state_fields( $_GET['st'] , 'users' ); 		           
			if ( isset($_GET['lu']) ) limit_fields( $_GET['lu'] , 'users' ); 					// лимит записей на страницу
			header('Location: /userlist.php');	// для очистки адресной строки
		}
	} elseif ( ! empty($_POST) ) {
		if ( test_perm('r_users') ){
			if ( isset($_POST['n_page']) )   	  page_fields( $_POST['n_page'] , 'users' ); 		// номер страницы
			if ( isset($_POST['do_sort']) ) 	  sort_fields( $_POST , 'users' );					// сортировка
			if ( isset($_POST['do_find']) ) 	  find_fields( $_POST , 'users' );				    // поиск
			if ( isset($_POST['do_clear_find']) ) find_fields( array() , 'users' );					// сброс поиска
			header('Location: /userlist.php');  // для очистки $_POST массива
		}   
	} else {
		if ( test_perm('r_users') ) {
			if ( empty($_SESSION['set_list']['users']->find_form[0]) ) $find_form[0] = array('id', '');
			else $find_form = $_SESSION['set_list']['users']->find_form;		
			$info = list_fields( 'users' );
		}
		include 'tpl/head.html';
		include 'tpl/errors.html';
		include 'tpl/message.html';
		if ( isset($_SESSION['logged_user']) ) {
			include 'tpl/header/user.html';
		} else {
			include 'tpl/header/guest.html';
		}
		if ( test_perm('r_users') )
			include 'tpl/body/userlist.html';
		include 'tpl/footer.html';
	}
?>

