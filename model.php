<?php 
require 'libs/rb.php';					// RedBean php
R::setup( 'mysql:host=localhost;dbname=devices', 'userbd', '2' ); //for both mysql or mariaDB
if( ! R::testConnection() ) die('No DB connection!');
//include 'libs/phpqrcode.php';
include 'libs/phpqrcode/qrlib.php';		// https://github.com/t0k4rt/phpqrcode
// inic_session();
session_start();
if ( empty($_SESSION['settings']) ) {
	$_SESSION['settings'] = new user_settings;
}

//----------------------------------------константы---------------------------------------
define("LOGIN_LIMIT", 60, true);
define("PASSWORD_LIMIT", 60, true);
define("ROLE_LIMIT", 1, true);
define("USERS_LIMIT", 999999999999, true);	// количество юзеров в системе
$arr_users_limit = array(3,5,10,20);   // массивы констант с php 5.6
$arr_name_role = array(
	'A' => 'Администратор',
	'R' => 'Чтение',
	'W' => 'Редактирование'
);

//QRcode::png( 'http://localhost/index.php?n=1' , 'img/1.png' , 'H');

class user_settings{
	var $users_limit;
	var $data_limit;
	function user_info() {
		$this->users_limit = $arr_users_limit[0];
		$this->data_limit = 30;
	}
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
			if ( $user ){
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
	if ( mb_strlen($data['login'] , 'UTF-8') > LOGIN_LIMIT) 
		$out['errors'][] = 'Длина логина должна быть не больше '.LOGIN_LIMIT.' символов';
	if ( mb_strlen($data['password'] , 'UTF-8') > PASSWORD_LIMIT) 
		$out['errors'][] = 'Длина пароля должна быть не больше '.PASSWORD_LIMIT.' символов';
	if ( mb_strlen($data['role'] , 'UTF-8') > ROLE_LIMIT) 
		$out['errors'][] = 'Длина роли должна быть не больше '.ROLE_LIMIT.' символов';
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
function check_role( $s ) {		// проверка на содержание только букв AWR
    $out = false;
    if  ( preg_match( "/[AWR]/", $s ) ) $out = true;
    return $out;
}
function del_user(){
	if ( check_numeric_get('del') ) {
		$user = R::findOne( 'users' , "id = $id"); 
		var_dump($user);
	}
}
function read_users(){
	global $arr_users_limit;
	if ( check_numeric_get('lu') ) {
		if ( in_array($_GET['lu'], $arr_users_limit, true)) $_SESSION['settings']->users_limit = $_GET['lu'];
	}	
	$page = 0;
	if ( check_numeric_get('p') ) {
		if ( $_SESSION['settings']->users_limit * $_GET['p'] < USERS_LIMIT) $page = $_GET['p'];
	}
	$start = $page * $_SESSION['settings']->users_limit;
	$out['users'] = R::findAll('users', "ORDER BY id ASC LIMIT {$start},{$_SESSION['settings']->users_limit}");
	$count = R::count('users');	
	if ($page > 0) 	$out['prev'] = "href='?p=".($page-1)."'";
	$out['curr'] = $page;
	if ( $count / $_SESSION['settings']->users_limit > $page + 1 ) $out['next'] = "href='?p=".($page+1)."'";
	return $out;
}
function check_numeric_get( $name_param ){		// проверка параметр - целое полож. число в GET массиве (на вход название параметра)
	$out = false;
	if ( isset($_GET[$name_param]) ){
		if ( is_numeric($_GET[$name_param]) && ($_GET[$name_param] > 0) && ($_GET[$name_param] == (int)$_GET[$name_param]) ){
			$out = true;
		}
	}
	return $out;
}

?>