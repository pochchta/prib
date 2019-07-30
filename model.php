<?php 
require 'libs/rb.php';					// RedBean php
// R::setup( 'mysql:host=localhost;dbname=devices', 'userbd', '2' ); //for both mysql or mariaDB
R::setup( 'mysql:host=localhost;dbname=devices', 'root', '' ); //for both mysql or mariaDB
if( ! R::testConnection() ) die('No DB connection!');
//include 'libs/phpqrcode.php';
include 'libs/phpqrcode/qrlib.php';		// https://github.com/t0k4rt/phpqrcode
session_start();
if ( ! isset($_SESSION['errors']) ) $_SESSION['errors'] = array();
if ( ! isset($_SESSION['messages']) ) $_SESSION['messages'] = array();

//----------------------------------------константы---------------------------------------
define("LIMIT_LOGIN", 60, true);			// длина строки
define("LIMIT_PASSWORD", 60, true);
define("LIMIT_ROLE", 1, true);
define("LIMIT_FIELDS", 1999999999, true);	// лимит записей в таблице
$arr_limit_users = array(3,5,10,20);   // массивы констант с php 5.6
$arr_sort_users = array('id', 'login', 'role', 'state');
$arr_name_role = array(
	'A' => 'Администратор',
	'R' => 'Чтение',
	'W' => 'Редактирование'
);
$arr_name_state = array(
	'off' => 'включить',
	'on' => 'выключить'
);
//QRcode::png( 'http://localhost/index.php?n=1' , 'img/1.png' , 'H');

class table_settings{
	var $limit;
	var $page;
	var $sort;
	var $desc;
	var $find_form;
	var $where;		    // запрос "where name like ?"
	var $arr_where;		// массив для этого запроса
	var $message;
	var $out;
	function table_settings() {
		$this->limit = 10;
		$this->page = 0;
		$this->sort = 'id';						// сортировка по id
		$this->desc = '';						// обратная сортировка выключена
		$this->find_form[0] = array('id', '');		// массив для сохранения данных из формы поиска
		$this->where = '';
		$this->arr_where = array();
	}
}

function v( $data ){	// var dump с построчным выводом
	echo'<pre>',var_dump($data),'</pre>';
}

function logging_user($data){	// аутентификация (POST на вход)
	$errors = array();
	if ( is_null($_SESSION['logged_user']) ){
		if( trim($data['login']) == '' ){
			$errors[] = 'Введите логин';
		}
		if( trim($data['password']) == '' ){
			$errors[] = 'Введите пароль';
		}				
		if ( check_symbol($data['login']) == false) $errors[] = 'Используйте в логине только буквы и цифры';
		if ( check_symbol($data['password']) == false) $errors[] = 'Используйте в пароле только буквы и цифры';			
		if ( empty($errors) ){		
			$user = R::findOne( 'users' , 'login = ?' , array($data['login']));			
			if ( $user->id ){
				if ( hash( 'SHA256' , $data['password'] ) == $user->password){
					// вход
					$_SESSION['logged_user'] = $user;
					$_SESSION['logged_user']->password = '';
				} else{
					$errors[] = 'Неверно введен пароль';
				}
			} else {
				$errors[] = 'Пользователь не найден';
			}
		}
	} else{
		$errors[] = 'Вы уже авторизованы';
	}
	return $errors;
}
function registering_user($data){		// регистрация (POST на вход)
	$out['errors'] = array();
	$out['ok'] = false;
	if( trim($data['login']) == '' ){
		$out['errors'][] = 'Введите логин';
	}
	if( trim($data['password']) == '' ){
		$out['errors'][] = 'Введите пароль';
	}
	if( trim($data['role']) == '' ){
		$out['errors'][] = 'Выберите роль';
	}		
	if ( check_symbol($data['login']) == false) $out['errors'][] = 'Используйте в логине только буквы и цифры';
	if ( check_symbol($data['password']) == false) $out['errors'][] = 'Используйте в пароле только буквы и цифры';	
	if ( check_role($data['role']) == false) $out['errors'][] = 'Выберите роль из списка';
	if ( mb_strlen($data['login'] , 'UTF-8') > LIMIT_LOGIN) 
		$out['errors'][] = 'Длина логина должна быть не больше '.LIMIT_LOGIN.' символов';
	if ( mb_strlen($data['password'] , 'UTF-8') > LIMIT_PASSWORD) 
		$out['errors'][] = 'Длина пароля должна быть не больше '.LIMIT_PASSWORD.' символов';
	if ( mb_strlen($data['role'] , 'UTF-8') > LIMIT_ROLE) 
		$out['errors'][] = 'Длина роли должна быть не больше '.LIMIT_ROLE.' символов';
	if ( empty($out['errors']) ){
		if( R::count( 'users' , "login = ?" , array($data['login']) ) ){
			$out['errors'][] = 'Этот логин занят, введите другой';
		}				
	}
	if ( empty($out['errors']) ){
		// регистрируем
		$user = R::dispense('users');
		$user->login = $data['login'];
		$user->role = $data['role'];
		$user->state = 'on';
		$user->password = hash( 'SHA256' , $data['password'] );
		R::begin();
		try{
			R::store($user);
			R::commit();
			$out['ok'] = true;
			$out['login'] = $data['login'];
		}catch (Exception $e){
			R::rollback();
			$out['errors'][] = 'Нет связи';
			// echo $e->getMessage();
		}
	}
	return $out;
}
function check_symbol( $s ) {		// проверка на содержание только букв и цифр
    $s = preg_replace( "/[a-zA-ZА-Яа-яЁё0-9]/u", '', $s );
    $out = false;
    if ($s == '') $out = true;
    return $out;
}
function check_symbol_en( $s ) {		// проверка на содержание только латинских букв
    $s = preg_replace( "/[a-zA-Z]/", '', $s );
    $out = false;
    if ($s == '') $out = true;
    return $out;
}
function check_role( $s ) {		// проверка на содержание только букв AWR
    $out = false;
    if  ( preg_match( "/[AWR]/", $s ) ) $out = true;
    return $out;
}
function check_numeric( $num ){		// проверка на целое положительное число 
	$out = false;
	if ( is_numeric($num) && ($num > 0) && ($num == (int)$num) ){
		$out = true;
	}
	return $out;
}
function check_like_query( $data , $table_name ){	// если разрешить пробелы, то нужно проверять что есть что-то кроме пробелов
	$out = false;
	switch ( $table_name ) {
		case 'users':
			if ( preg_match("/^%?[a-zA-ZА-Яа-яЁё0-9]+%?\z/u", $data) ) $out = true;
			break;
	}
	return $out;
}
// function name_user( $id ){	// получить имя по id
// 	$out = '';
// 	if ( check_numeric($id) ){
// 		$user = R::load( 'users' , $id);
// 		if ($user->name) $out = $user->name;
// 	}
// 	return $out;
// }
function del_fields( $id , $table_name ){
	$errors = array();
	if ( check_numeric($id) ) {
		$user = R::load( $table_name , $id);
		if ( $user->id ) {
			if ($user->id != $_SESSION['logged_user']->id){
				R::begin();
				try{
					R::trash($user);
					R::commit();
				}catch (Exception $e){
					R::rollback();
					$errors[] = 'При удалении произошла ошибка';
					// echo $e->getMessage();
				}
			} else{
				$errors[] = 'Это ваш аккаунт';
			}
		} else {
			$errors[] = 'Пользователь не найден';
		}
	} else {
		$errors[] = 'Недопустимый параметр';
	}
	if ( empty($errors) ) $_SESSION['messages'][] = 'Удалено успешно';
	$_SESSION['errors'] = array_merge( $_SESSION['errors'] , $errors );
}
function state_fields( $id , $table_name ){
	$errors = array();
	if ( check_numeric($id) ) {
		$user = R::load( $table_name , $id);
		if ( $user->id ) {
			if ($user->id != $_SESSION['logged_user']->id){
				R::begin();
				try{
					if ( $user->state == 'off') $user->state = 'on';
					else $user->state = 'off';
					R::store($user);
					R::commit();
				}catch (Exception $e){
					R::rollback();
					$errors[] = 'Ошибка при изменении свойства';
					// echo $e->getMessage();
				}
			} else{
				$errors[] = 'Это ваш аккаунт';
			}
		} else {
			$errors[] = 'Пользователь не найден';
		}
	} else {
		$errors[] = 'Недопустимый параметр';
	}
	if ( empty($errors) ) $_SESSION['messages'][] = 'Состояние изменено';
	$_SESSION['errors'] = array_merge( $_SESSION['errors'] , $errors );
}
function limit_fields( $limit , $table_name ){
	$errors = array();	
	$arr_valid_limit = array();
	switch ( $table_name ) {
		case 'users':
			global $arr_limit_users;
			$arr_valid_limit = $arr_limit_users;
			break;
	}	
	if ( check_numeric($limit) ) {
		if ( in_array($limit, $arr_valid_limit) ) {
			// $_SESSION[$table_name]->page = (int)($_SESSION[$table_name]->page * $_SESSION[$table_name]->limit / $limit);
			$_SESSION[$table_name]->limit = $limit;
		} else{
			$errors[] = 'Недопустимый параметр лимита';
		}
	} else{
		$errors[] = 'Недопустимый параметр лимита';
	}
	$_SESSION['errors'] = array_merge( $_SESSION['errors'] , $errors );
}
function page_fields( $page , $table_name  ){
	$errors = array();
	if ( check_numeric($page + 1) ) {
		if ( $_SESSION[$table_name]->limit * $page < LIMIT_FIELDS ) {
			$_SESSION[$table_name]->page = (int)$page;
		} else{
			$errors[] = 'Страница не найдена';
		}
	} else{
		$errors[] = 'Недопустимый параметр номера страницы';
	}
	$_SESSION['errors'] = array_merge( $_SESSION['errors'] , $errors );
}
function sort_fields( $data , $table_name ){
	$errors = array();
	$arr_valid_sort = array();	// массив допустимых категорий сортировки
	switch ( $table_name ) {
		case 'users':
			global $arr_sort_users;
			$arr_valid_sort = $arr_sort_users;		
			break;
	}
	if ( in_array($data['sort'], $arr_valid_sort) ){
		$_SESSION[$table_name]->sort = $data['sort'];
		if ( $data['desc'] == 'on' ) $_SESSION[$table_name]->desc = 'on';
		else $_SESSION[$table_name]->desc = '';
	} else{
		$errors[] = 'Недопустимый параметр сортировки';
	}
	$_SESSION['errors'] = array_merge( $_SESSION['errors'] , $errors );
}
function find_fields( $data , $table_name ){		// поиск в таблице (POST, имя)
	$arr_valid_find = array();	// массив допустимых категорий поиска
	switch ( $table_name ) {
		case 'users':
			global $arr_sort_users;
			$arr_valid_find = $arr_sort_users;
			break;
		default:
			# code...
			break;
	}
	$out = array();
	$one_find = array();
	$find = '';
	$errors = array();		
	$error = '';
	$where = '';
	$arr_where = array();
	foreach ($data as $key => $value){
		$value = trim($value);
		if ( strpos($key, 'find') === 0 ) {
			$find = $value;
			if ( ! in_array($value, $arr_valid_find) ) {
				$error = 'Недопустимый параметр поиска';
				$errors[] = $error;
			}	
		}
		if ( strpos($key, 'text') === 0 ){
			if ( ($error == '') && ($value != '') ){
				if ( check_like_query($value, $table_name) ){
					if ($where != '') $where = $where.' AND';		// нужно добавить кавычки `` в запрос ???
					else $where = 'WHERE';
					$where = $where.' '.$find.' LIKE ?';
					$arr_where[] = $value;
				} else{
					$error = 'Недопустимый текст поиска';
					$errors[] = $error;
				}
			}
			if ($value != ''){
				$one_find['find'] = htmlspecialchars($find);
				$one_find['text'] = htmlspecialchars($value);
				$one_find['error'] = $error;
				$out[] = $one_find;
			}
			$error = '';	
			$find = '';
		}
	}
	$_SESSION[$table_name]->find_form = $out;
	$_SESSION[$table_name]->where = $where;
	$_SESSION[$table_name]->arr_where = $arr_where;
	// $_SESSION[$table_name]->page = 0;
	$_SESSION['errors'] = array_merge( $_SESSION['errors'] , $errors );
	// return $out;
}
function list_fields( $table_name ){
	$count = R::count($table_name, "{$_SESSION[$table_name]->where}", $_SESSION[$table_name]->arr_where);
	$page = $_SESSION[$table_name]->page;
	$limit = $_SESSION[$table_name]->limit;
	if ( (int)( ($count-1) / $limit ) < $page ) {
		$page = (int)( ($count-1) / $limit );
		$_SESSION[$table_name]->page = $page;
	}
	if ( $_SESSION[$table_name]->desc == 'on' ) $direction = 'DESC';
	else $direction = 'ASC';
	$start = $page * $limit;
	$out[$table_name] = R::find($table_name, "{$_SESSION[$table_name]->where} ORDER BY {$_SESSION[$table_name]->sort} {$direction} LIMIT {$start},{$limit}", $_SESSION[$table_name]->arr_where);
	
	$out['count'] = $count;
	if ($page > 0) $out['prev'] = "href='?p=".($page-1)."'";
	$out['curr'] = $page;
	if ( $count / $limit > $page + 1 ) $out['next'] = "href='?p=".($page+1)."'";
	if ($page != $out['first'] ) $out['first'] = "href='?p="."0"."'";
	$out['last'] = (int)( ($count-1) / $limit ) ;
	if ($page != $out['last'] ) $out['last'] = "href='?p={$out['last']}'";
	return $out;
}
?>