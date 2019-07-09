<?php 
	//require 'db.php';
	include 'model.php';	

	$data = $_POST;
	$message;
	if ( isset($data['do_signup']) ){
		// регистрация

		$errors = array();
		if( trim($data['login']) == '' ){
			$errors[] = 'Введите логин';
		}

		if( R::count( 'users' , "login = ?" , array(trim($data['login'])) ) ){
			$errors[] = 'Введите другой логин';
		}

		if( trim($data['password']) == '' ){
			$errors[] = 'Введите пароль';
		}		

		if ( empty($errors) ){
			// регистрируем
			$message =  '<div style="color: green;">Регистрация прошла успешно, '.trim($data['login']).'</div><hr>';
			$user = R::dispense('users');
			$user->login = $data['login'];
			$user->password = hash( 'SHA256' , $data['password'] );
			R::store($user);

		} else {
			$message = '<div style="color: red;">'.array_shift($errors).'</div><hr>';
		}

	}
?>

<!DOCTYPE html>
<html lang="RU">
<head>
	<meta charset="UTF-8">
	<title>Регистрация</title>
	<link href="css/style.css" type="text/css" rel="stylesheet"/>
</head>
<body>

    <div class="header">
        <div class="name">
            Регистрация
        </div>
        <div class="links">
            <a href="index.php">Главная</a>
            <a href="login.php">Войти</a>
            <a href="signup.php">Зарегистрироваться</a>
        </div>
    </div>
    <hr>      

	<form action="/signup.php" method="POST">
		<p>Имя пользователя</p>
		<input type="text" name="login">
		<p>Пароль</p>
		<input type="password" name="password">
		<p>
			<button type="submit" name="do_signup">Зарегистрироваться</button>
		</p>
	</form>

	<?php echo $message ?>

</body>
</html>