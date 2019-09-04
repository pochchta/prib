<?php 
date_default_timezone_set('Asia/Irkutsk');
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
define("LIMIT_QUERY", 100, true);			// лимит на одно поле поиска
define("LIMIT_FIELDS", 1999999999, true);	// лимит записей в таблице
define("LIMIT_DEV_TEXT", 999, true);		// лимит полей в таблице DEV
$arr_limit_users = array(3,5,10,20);   // массивы констант с php 5.6
$arr_sort_users = array('id', 'login', 'role', 'state');
$arr_role = array('A' , 'W' , 'R');
$arr_name_role = array(
	'A' => 'Администратор',
	'R' => 'Чтение',
	'W' => 'Редактирование'
);
$arr_state = array('on' , 'off');
$arr_name_state = array(
	'off' => 'заблокирован',
	'on' => 'активен'
);
$arr_button_state = array(		// инвертированные названия для кнопок
	'off' => 'включить',
	'on' => 'выключить'
);
$arr_perm = array(				// разрешения
	'w_user_data' =>  array ('A'),		// запись
	'w_user_pass' =>  array ('A'),
	'w_user_del' =>   array ('A'),

	'w_self_pass' =>  array ('A', 'W', 'R'),

	'r_users' => array ('A'),		    // чтение
	'r_user' =>  array ('A'),

	'r_self' =>  array ('A', 'W', 'R')

);
$arr_fields = array(	// поля для редактирования пользователем
	'devs' => array(			
		'name',
		'type',
		'number',
		'date_release',
		'state'
	),
	'repairs' => array(	
		'text',
		'parts',
		'date_release',
		'state'
	),
	'povs' => array(			
		'text',
		'parts',
		'date_release',
		'state'
	)
); 
//QRcode::png( 'http://localhost/index.php?n=1' , 'img/1.png' , 'H');

class table_settings{
	var $limit;
	var $page;
	var $sort;
	var $desc;
	var $find_form;
	var $where;		    // запрос вида "where name like ?"
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
class item_settings{
	var $id;
	var $test_double;
	function item_settings(){
		$this->id = 0;
		$this->test_double = true;
	}
}
// class dev_settings{
// 	var $id;
// 	var $name;
// 	var $type;
// 	var $number;
// 	var $date_release;
// 	var $last_author;
// 	var $date_last_modif;
// 	var $state;
// }
function v( $data ){	// var dump с построчным выводом
	echo'<pre>',var_dump($data),'</pre>';
}

function test_perm( $perm_name , $not_errors=false){	// имя операции, подавление ошибок
	$out = false;
	$errors = array();
	global $arr_perm;
	if ( $arr_perm[$perm_name] )
		if ( isset($_SESSION['logged_user']) )
			if ( $_SESSION['logged_user']->state == 'on' )
				if ( in_array($_SESSION['logged_user']->role, $arr_perm[$perm_name]) ) $out = true;
				else $errors[] = 'Нет прав доступа';
			else $errors[] = 'Ваш аккаунт заблокирован';
		else $errors[] = 'Нет прав доступа. Выполните вход в систему';
	else $errors[] = 'Неизвестная операция';
	if ( $not_errors == false )
		$_SESSION['errors'] = array_merge( $_SESSION['errors'] , $errors );
	return $out;
}
function logging_user($data){	// аутентификация (POST на вход)
	$errors = array();
	if ( is_null($_SESSION['logged_user']) ){			
		if ( check_login($data['login']) == false) $errors[] = 'Логин - строка из букв и цифр длиной не более '.LIMIT_LOGIN;
		if ( check_pass($data['password']) == false) $errors[] = 'Пароль - строка из букв и цифр длиной не более '.LIMIT_PASSWORD;
		if ( empty($errors) ){		
			$user = R::findOne( 'users' , 'login = ?' , array($data['login']));			
			if ( $user->id ){
				if ( hash( 'SHA256' , $data['password'].$user->time ) == $user->password){
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
	if ( empty($errors) ) $_SESSION['messages'][] = 'Вы вошли как '.$_SESSION['logged_user']->login;
	$_SESSION['errors'] = array_merge( $_SESSION['errors'] , $errors );	
}
function registering_user($data){		// регистрация (POST на вход)
	$errors = array();
	// $data['login'] = trim( $data['login'] );
	// $data['password'] = trim( $data['password'] );
	if ( check_login($data['login']) == false) $errors[] = 'Логин - строка из букв и цифр длиной не более '.LIMIT_LOGIN;
	if ( check_pass($data['password']) == false) $errors[] = 'Пароль - строка из букв и цифр длиной не более '.LIMIT_PASSWORD;
	if ( check_role($data['role']) == false) $errors[] = 'Выберите роль из списка';
	if ( empty($errors) ){
		if( R::count( 'users' , "login = ?" , array($data['login']) ) ){
			$errors[] = 'Этот логин занят, введите другой';
		}				
	}
	if ( empty($errors) ){
		// регистрируем
		$user = R::dispense('users');
		$user->login = $data['login'];
		$user->role = $data['role'];
		$user->state = 'on';
		$user->time = date("m.d.y H:i:s");
		$user->password = hash( 'SHA256' , $data['password'].$user->time );
		R::begin();
		try{
			R::store($user);
			R::commit();
		}catch (Exception $e){
			R::rollback();
			$errors[] = 'Нет связи';
			// echo $e->getMessage();
		}
	}
	if ( empty($errors) ) $_SESSION['messages'][] = 'Регистрация прошла успешно, '.$user->login;
	$_SESSION['errors'] = array_merge( $_SESSION['errors'] , $errors );	
}
function check_login( $s ) {		// проверка на содержание только букв и цифр
    $out = false;
    if ( trim($s) != '' )
   		if ( preg_match("/\A[a-zA-ZА-Яа-яЁё0-9]{1,".LIMIT_LOGIN."}\z/u", $s) ) $out = true;
    return $out;
}
function check_pass( $s ) {		// проверка на содержание только букв и цифр
    $out = false;
    if ( preg_match("/\A[a-zA-ZА-Яа-яЁё0-9]{1,".LIMIT_PASSWORD."}\z/u", $s) ) $out = true;
    return $out;
}
function check_symbol( $s ) {		// проверка на содержание только букв и цифр
    $out = false;
    if ( preg_match("/\A[a-zA-ZА-Яа-яЁё0-9]{1,".LIMIT_LOGIN."}\z/u", $s) ) $out = true;
    return $out;
}
function check_symbol_en( $s ) {		// проверка на содержание только латинских букв
    $out = false;
    if ( preg_match("/\A[a-zA-Z]{1,".LIMIT_LOGIN."}\z/", $s) ) $out = true;
    return $out;
}
function check_role( $s ) {		// проверка на содержание только букв AWR
    $out = false;
    if  ( preg_match( "/\A[AWR]\z/", $s ) ) $out = true;
    return $out;
}
function check_numeric( $num ){		// проверка на целое положительное число 
	$out = false;
	if ( is_numeric($num) && ($num > 0) && ($num == (int)$num) ){
		$out = true;
	}
	return $out;
}
function check_like_query( $s , $table_name ){
	$out = false;
    if ( trim($s) != '' )
		switch ( $table_name ) {
			case 'users':
				if ( preg_match("/\A%?[a-zA-ZА-Яа-яЁё0-9]{1,".LIMIT_QUERY."}%?\z/u", $s) ) $out = true;
				break;
		}
	return $out;
}
function check_date( $s ) {		// проверка на формат yyyy-mm-dd
    $out = false;
    if  ( preg_match( "/\A[0-9]{4}-[0-9]{2}-[0-9]{2}\z/", $s ) ) $out = true;
    return $out;
}
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
			// $_SESSION['set_list'][$table_name]->page = (int)($_SESSION['set_list'][$table_name]->page * $_SESSION['set_list'][$table_name]->limit / $limit);
			$_SESSION['set_list'][$table_name]->limit = $limit;
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
		if ( $_SESSION['set_list'][$table_name]->limit * $page < LIMIT_FIELDS ) {
			$_SESSION['set_list'][$table_name]->page = (int)$page;
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
		$_SESSION['set_list'][$table_name]->sort = $data['sort'];
		if ( $data['desc'] == 'on' ) $_SESSION['set_list'][$table_name]->desc = 'on';
		else $_SESSION['set_list'][$table_name]->desc = '';
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
	$_SESSION['set_list'][$table_name]->find_form = $out;
	$_SESSION['set_list'][$table_name]->where = $where;
	$_SESSION['set_list'][$table_name]->arr_where = $arr_where;
	// $_SESSION['set_list'][$table_name]->page = 0;
	$_SESSION['errors'] = array_merge( $_SESSION['errors'] , $errors );
	// return $out;
}
function list_fields( $table_name ){
	$count = R::count($table_name, "{$_SESSION['set_list'][$table_name]->where}", $_SESSION['set_list'][$table_name]->arr_where);
	$page = $_SESSION['set_list'][$table_name]->page;
	$limit = $_SESSION['set_list'][$table_name]->limit;
	if ( (int)( ($count-1) / $limit ) < $page ) {
		$page = (int)( ($count-1) / $limit );
		$_SESSION['set_list'][$table_name]->page = $page;
	}
	if ( $_SESSION['set_list'][$table_name]->desc == 'on' ) $direction = 'DESC';
	else $direction = 'ASC';
	$start = $page * $limit;
	$out[$table_name] = R::find($table_name, "{$_SESSION['set_list'][$table_name]->where} ORDER BY {$_SESSION['set_list'][$table_name]->sort} {$direction} LIMIT {$start},{$limit}", $_SESSION['set_list'][$table_name]->arr_where);
	
	$out['count'] = $count;
	$out['curr'] = $page;
	$out['first'] = 0;
	if ( $page == $out['first'] ) $out['first'] = false;
	$out['last'] = (int)( ($count-1) / $limit ) ;
	if ( $page == $out['last'] ) $out['last'] = false;
	if ( $page > 0) $out['prev'] = $page-1;
	else $out['prev'] = false;
	if ( $page < $out['last'] ) $out['next'] = $page+1;
	else $out['next'] = false;	
	return $out;
}
function one_item( $id , $table_name , $own_tables=array() ){
	if ( check_numeric($id) ){
		$item = R::load( $table_name , $id );
		if ( $item->id ) {
			$out = $item;
			if( count($own_tables) ){
				foreach ($own_tables as $value) {
					$own_item = 'own'.$value.'List';
					$item->$own_item;
				}
			}
			if ( $out->password ) $out->password = '';
		} else{
			// $_SESSION['errors'][] = 'Запись не найдена';
		}
	}
	return $out;
}
function change_pass( $data , $table_name , $id ){
	$errors = array();
	if ( check_pass($data['old_pass']) == false) $errors[] = 'Старый пароль - строка из букв и цифр длиной не более '.LIMIT_PASSWORD;
	if ( check_pass($data['new_pass']) == false) $errors[] = 'Новый пароль - строка из букв и цифр длиной не более '.LIMIT_PASSWORD;
	if ( $data['old_pass'] == $data['new_pass'] ) $errors[] = 'Вы ввели одинаковые пароли';
	if ( empty($errors) ){
		$item = R::load( $table_name , $id );
		if ( $item->id ) {
			if ( $item->password == hash('SHA256' , $data['old_pass'].$item->time) ){
				$item->password = hash('SHA256' , $data['new_pass'].$item->time);
				R::begin();
				try{
					R::store($item);
					R::commit();
				}catch (Exception $e){
					R::rollback();
					$errors[] = 'Нет связи';
				}
			} else $errors[] = 'Старый пароль не верен';
		} else $errors[] = 'Пользователь не найден';
	}
	if ( empty($errors) ) $_SESSION['messages'][] = 'Пароль изменен успешно';
	$_SESSION['errors'] = array_merge( $_SESSION['errors'] , $errors );
}
function change_data( $data , $table_name , $id ){
	global $arr_state, $arr_role;
	$errors = array();
	// $data['login'] = trim( $data['login'] );
	if ( check_login($data['login']) == false) $errors[] = 'Логин - строка из букв и цифр длиной не более '.LIMIT_LOGIN;
	if ( in_array($data['role'], $arr_role) == false ) $errors[] = 'Выберите роль из списка';
	if ( in_array($data['state'], $arr_state) == false ) $errors[] = 'Выберите состояние из списка';
	if ( empty($errors) ){
		$item = R::load( $table_name , $id );
		if ( $item->id ) {
			if ( ($item->login != $data['login']) || ($item->role != $data['role']) || ($item->state != $data['state']) ){
				$item->login = $data['login'];
				$item->role = $data['role'];
				$item->state = $data['state'];
				R::begin();
				try{
					R::store($item);
					R::commit();
				}catch (Exception $e){
					R::rollback();
					$errors[] = 'Нет связи';
				}
			} else $errors[] = 'Вы не изменили данные';
		} else $errors[] = 'Пользователь не найден';
	}
	if ( empty($errors) ) $_SESSION['messages'][] = 'Данные изменены успешно';
	$_SESSION['errors'] = array_merge( $_SESSION['errors'] , $errors );
}
function change_dev_data( $data , $table_name , $id , $test_double ){
	global $arr_state;
	$double_item_exists = false;			// найдены ли дубликаты с таким же номером
	$errors = array();
	$message = 'Данные изменены успешно';
	// $data['m_name'] = htmlspecialchars( $data['m_name'] );
	// $data['m_type'] = htmlspecialchars( $data['m_type'] );
	// $data['m_number'] = htmlspecialchars( $data['m_number'] );
	if ( $data['m_number'] == '' ) $errors[] = 'Номер - обязательное поле';
	if ( mb_strlen($data['m_name'], 'utf8') > LIMIT_DEV_TEXT ) $errors[] = 'Название должно быть меньше '.LIMIT_DEV_TEXT.' символов';
	if ( mb_strlen($data['m_type'], 'utf8') > LIMIT_DEV_TEXT ) $errors[] = 'Тип должен быть меньше '.LIMIT_DEV_TEXT.' символов';
	if ( mb_strlen($data['m_number'], 'utf8') > LIMIT_DEV_TEXT ) $errors[] = 'Номер должен быть меньше '.LIMIT_DEV_TEXT.' символов';
	foreach ($data as $key => $value) {
		if ( mb_strlen($value, 'utf8') > LIMIT_DEV_TEXT ){
			$errors[] = 'Текст в ячейках должен быть меньше '.LIMIT_DEV_TEXT.' символов';
			$data[$key] = substr($value, 0, LIMIT_DEV_TEXT);
		}
		if ( preg_match("/\A[a-zA-Z]_date_/", $key) ){
			if ( $value != '' ){
				if ( (strtotime($value) && check_date($value)) == false )
					$errors[] = 'Выберите дату из календаря';
			}else {
				$data[$key] = "1902-01-01"; //Корректным диапазоном временных меток обычно являются даты с 13 декабря 1901 20:45:54 UTC по 19 января 2038 03:14:07 UTC. (Эти даты соответствуют минимальному и максимальному значению 32-битового знакового целого). ( Функция strtotime() выдаст ошибку при выходе за диапазон )
			}
		}	
		if ( preg_match("/\A[a-zA-Z]_state\z/", $key) ){
			if ( in_array($value, $arr_state) == false ) $errors[] = 'Выберите состояние из списка';
		}
		if ( preg_match("/\Ar_text[0-9]*\z/", $key) ){
			if ( $value == '' ) $errors[] = 'Таблица ремонтов => описание - обязательное поле';
		}		
	}
	$count_double = R::count( $table_name , 'id <> ? AND name = ? AND type = ? AND number = ? AND date_release = ?' , 
		array( $id , $data['m_name'] , $data['m_type'] , $data['m_number'] , $data['m_date_release']) );
	$count_double_number = R::count( $table_name , 'id <> ? AND number = ?' , array($id , $data['m_number']) );
	if ( $count_double ){
		$errors[] = 'Этот прибор уже внесен';
	} elseif ( $count_double_number && ($data['do_ignore_double'] !== $data['m_number']) && $test_double ){
		$double_item_exists = true;
		$errors[] = 'Прибор с таким номером уже существует';
	}
	$item = dev_data_to_obj( $data, $id );
	if ( empty($errors) ){
		if ( $id == 0 ) {
			$message = 'Создана новая запись';
		}else {
			if ( $item->id == 0 ) $errors[] = 'Запись не найдена';
			if ( comp_obj($item, one_item($id, 'devs', array('Repairs'))) ) $errors[] = 'Вы не изменили данные';
		}
		if ( empty($errors) ) {
			if ( comp_obj($item, one_item($id, 'devs'), false) == false ){
				$item->last_date = date("Y-m-d");
				$item->last_author = $_SESSION['logged_user']->login;
			}
			R::begin();
			try{
				R::store($item);
				R::commit();
			}catch (Exception $e){
				R::rollback();
				$errors[] = 'Нет связи';
			}
		}
	}
	if ( empty($errors) ) $_SESSION['messages'][] = $message;
	$_SESSION['errors'] = array_merge( $_SESSION['errors'] , $errors );
	return array( 'changed' => ! empty($errors) , 'double_item_exists' => $double_item_exists , 'item' => $item);
}
function dev_data_to_obj( $data , $id ){
	global $arr_fields;		// допустимые поля таблиц, важен порядок
	$errors = array();
	$dev;
	$repair;
	$matches;
	$not_empty_count;		// количество непустых ячеек
	$equal_count;			// количество одинаковых ячеек
	if ( $id ) $dev = R::load( 'devs' , $id );
	if ( $id == 0 || $dev->id == 0 ) $dev = R::dispense('devs');
	foreach ($data as $key => $value){

		if ( preg_match("/\Ado_/", $key) ){													// управляющие элементы

		} elseif ( preg_match("/\Am_(\w+)\z/", $key, $matches) ){							// строка основной таблицы
			if ( in_array($matches[1], $arr_fields['devs']) ) {	
				$dev->$matches[1] = $value;
			}			
		} elseif ( preg_match("/\Ar_([a-zA-Z_]+)([0-9]*)\z/", $key, $matches) ){			// строка таблицы ремонтов
			if ( $matches[1] == 'id' ){
				if ( $value ) $repair = $dev->ownRepairsList[$value];
				if ( $value == 0 || $repair->id == 0 ) {
					$repair = R::dispense('repairs');
				}
				$not_empty_count = 0;
				$equal_count = 0;
			}
			if ( isset($repair) ){
				if ( in_array($matches[1], $arr_fields['repairs']) ) {	
					if ( $value != '' ) $not_empty_count++;
					if ( $value == $repair->$matches[1] ) $equal_count++;
					$repair->$matches[1] = $value;				
				}
				if ( $matches[1] == $arr_fields['repairs'][count($arr_fields['repairs']) - 1] ) {
					if ( $equal_count != count($arr_fields['repairs']) ) {
						$repair->last_date = date("Y-m-d");
						$repair->last_author = $_SESSION['logged_user']->login;
					}
					if ( $not_empty_count && $repair->id == 0 ) $dev->ownRepairsList[] = $repair;	// запись по последнему элементу
				}
			}
		} elseif ( preg_match("/\Ap_(\w+)\z/", $key, $matches) ){							// строка таблицы поверок

		}else{																				// другое

		}
	}
	return $dev;
}
function comp_obj( $obj1, $obj2 , $full=true ){
	$equal = false;
	$own_exists = false;
	$own_equal_i = 0;
	$matches;
	$regexp_last = "/\A(last_[\w]+)\z/";
	$regexp_own = "/\A(own[A-Z][\w]+)\z/";
	$arr1 = $obj1->export();
	$arr2 = $obj2->export();
	// v($arr1);
	// v($arr2);
	foreach ($arr1 as $key => $value) {		// ключи last_... не нужно сравнивать, поэтому удаляем
		if ( preg_match($regexp_last, $key, $matches) ) unset($arr1[$key], $arr2[$key]);
		if ( preg_match($regexp_own, $key, $matches) ){
			if ( $full == false ) unset($arr1[$key], $arr2[$key]);
			else foreach ($arr1[$key] as $own_n => $own_n_value) {
				foreach ($arr1[$key][$own_n] as $own_key => $own_key_value) {
					if ( preg_match($regexp_last, $own_key, $matches) ) 
						unset($arr1[$key][$own_n][$own_key], $arr2[$key][$own_n][$own_key]);
				}
			}
		}
	}
	// v($full);
	// v($arr1);
	// v($arr2);
	if ( count(array_diff($arr1, $arr2)) == 0 ){
		if ( $full ) {
			foreach ($arr1 as $key => $value) {
				if ( preg_match($regexp_own, $key, $matches) ){
					$own_exists = true;
					if ( is_array($arr1[$key]) && is_array($arr2[$key]) && count($arr1[$key]) == count($arr2[$key]) ){
						for ($i=0; $i < count($arr1[$key]); $i++) { 
							if ( count(array_diff($arr1[$key][$i], $arr2[$key][$i])) == 0 ) $own_equal_i++;
						}
						if ( $i == $own_equal_i ) $equal = true;
					}
				}
			}
		}
		if ( $own_exists == false) $equal = true;
	}
	// v($arr1);
	// v($arr2);
	// v($equal);
	return $equal;
}
?>