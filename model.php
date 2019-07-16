<?php 
require 'libs/rb.php';					// RedBean php
// R::setup( 'mysql:host=localhost;dbname=devices', 'userbd', '2' ); //for both mysql or mariaDB
R::setup( 'mysql:host=localhost;dbname=devices', 'root', '' ); //for both mysql or mariaDB
if( ! R::testConnection() ) die('No DB connection!');
//include 'libs/phpqrcode.php';
include 'libs/phpqrcode/qrlib.php';		// https://github.com/t0k4rt/phpqrcode
// inic_session();
session_start();
if ( empty($_SESSION['settings']->limit_users) ) {
	$_SESSION['settings'] = new user_settings;
}

//----------------------------------------константы---------------------------------------
define("LIMIT_LOGIN", 60, true);			// длина строки
define("LIMIT_PASSWORD", 60, true);
define("LIMIT_ROLE", 1, true);
define("LIMIT_USERS", 999999999999, true);	// количество юзеров в системе
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

class user_settings{
	var $limit_users;
	//var $limit_data;
	var $message;
	var $out;
	var $page_users;
	var $sort_users;
	var $desc_users;
	var $find_name_users;
	var $find_text_users;
	function user_settings() {
		$this->limit_users = 10;
		//$this->limit_data = 30;
		$this->page_users = 0;
		$this->sort_users = 'id';				// сортировка по id
		$this->desc_users = '';				// обратная сортировка выключена
		$this->find_name_users = '';
		$this->find_text_users = '';
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
function del_user( $id ){
	$errors = array();
	if ( check_numeric($id) ) {
		$user = R::load( 'users' , $id);
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
	return $errors;
}
function state_user( $id ){
	$errors = array();
	if ( check_numeric($id) ) {
		$user = R::load( 'users' , $id);
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
	return $errors;
}
function limit_users( $limit ){
	global $arr_limit_users;
	if ( check_numeric($limit) ) {
		if ( in_array($limit, $arr_limit_users)) $_SESSION['settings']->limit_users = $limit;
	}
}
function page_users( $page ){
	if ( check_numeric($page + 1) ) {
		if ( $_SESSION['settings']->limit_users * $page < LIMIT_USERS) $_SESSION['settings']->page_users = (int)$page;
	}
}
function sort_users( $data ){
	global $arr_sort_users;
	$errors = array();
	if ( in_array($data['sort'], $arr_sort_users) ){
		$_SESSION['settings']->sort_users = $data['sort'];
		if ( isset($data['desc']) && ($data['desc'] == 'on') ) $_SESSION['settings']->desc_users = 'on';
		else $_SESSION['settings']->desc_users = '';
	} else{
		$errors[] = 'Недопустимый параметр сортировки';
	}
}
function find_users( $data ){
	global $arr_sort_users;
	$errors = array();
	if ( in_array($data['find'], $arr_sort_users) && check_symbol($data['text'] ) ){
		$_SESSION['settings']->find_name_users = $data['find'];
		$_SESSION['settings']->find_text_users = $data['text'];
	} else{
		$errors[] = 'Недопустимый параметр поиска';
	}
}
function list_users(){
	$page = $_SESSION['settings']->page_users;
	$limit = $_SESSION['settings']->limit_users;
	if ( $_SESSION['settings']->desc_users == 'on' ) $direction = 'DESC';
	else $direction = 'ASC';
	$start = $page * $limit;
	if ( $_SESSION['settings']->find_text_users == '' ){
		$out['users'] = R::findAll('users', "ORDER BY {$_SESSION['settings']->sort_users} {$direction} LIMIT {$start},{$limit}");
	} else{
		$out['users'] = R::find('users', "{$_SESSION['settings']->find_name_users} LIKE ? ORDER BY {$_SESSION['settings']->sort_users} {$direction} LIMIT {$start},{$limit}", array(($_SESSION['settings']->find_text_users).'%'));
		// $out['users'] = R::find('users', "login LIKE ?", array(($_SESSION['settings']->find_text_users).'%'));
	}
	$count = R::count('users');	
	if ($page > 0) 	$out['prev'] = "href='?p=".($page-1)."'";
	$out['curr'] = $page;
	if ( $count / $limit > $page + 1 ) $out['next'] = "href='?p=".($page+1)."'";
	return $out;
}


?>