<?php 
require 'libs/rb.php';
R::setup( 'mysql:host=localhost;dbname=devices', 'root', '' ); //for both mysql or mariaDB

//include 'libs/phpqrcode.php';
include 'libs/phpqrcode/qrlib.php';		// https://github.com/t0k4rt/phpqrcode
inic_session();
//QRcode::png( 'http://localhost/index.php?n=1' , 'img/1.png' , 'H');

class user_info{
	var $login;
	var $role;
	function user_info() {
		$this->login = 'guest';
		$this->role = 'guest';
	}
}

function inic_session(){
	session_start();
	if ( ! isset($_SESSION['logged_user']) ) {
		$_SESSION['logged_user'] = new user_info;
	}
}
function logging_user($data){	// POST на вход
	if ( isset($data['do_login']) ){
		// аутентификация
		$errors = array();
		if ($_SESSION['logged_user']->login == 'guest'){
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
			if( trim($data['login']) == '' ){
				$errors[] = 'Введите логин';
			}

			if( trim($data['password']) == '' ){
				$errors[] = 'Введите пароль';
			}	
		} else{
			$errors[] = 'Вы уже авторизованы';
		}
	}
	return $errors;
}
?>
