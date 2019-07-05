<?php 
	require 'db.php';

	$data = $_POST;
	$message;
	if ( isset($data['do_login']) ){
		// аутентификация

		$errors = array();
		$user = R::findOne( 'users' , 'login = ?' , array($data['login']));
		if ( $user ){
			if ( hash( 'SHA256' , $data['password'] ) == $user->password){
				// вход
				$_SESSION['logged_user'] = $user;
				$message =  '<div style="color: green;">Вы вошли как '.trim($data['login']).'</div><hr>';

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

		if ( empty($errors) ){
			// аутентифицируем


		} else {
			$message = '<div style="color: red;">'.array_shift($errors).'</div><hr>';
		}

	}
?>

<!DOCTYPE html>
<html lang="RU">
<head>
	<meta charset="UTF-8">
	<title>Аутентификация</title>
	<link href="css/style.css" type="text/css" rel="stylesheet"/>
</head>
<body>

    <div class="header">
        <div class="name">
            Аутентификация
        </div>
        <div class="links">
            <a href="index.php">Главная</a>
            <a href="login.php">Войти</a>
            <a href="signup.php">Зарегистрироваться</a>
        </div>
    </div>
    <hr>      

	<form action="/login.php" method="POST">
		<p>Имя пользователя</p>
		<input type="text" name="login">
		<p>Пароль</p>
		<input type="password" name="password">
		<p>
			<button type="submit" name="do_login">Войти</button>
		</p>
	</form>

	<?php echo $message ?>

</body>
</html>