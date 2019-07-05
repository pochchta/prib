<?php 

require 'db.php';
//include 'libs/phpqrcode.php';
include 'libs/phpqrcode/qrlib.php';

?>

<!DOCTYPE html>
<html lang="RU">
<head>
    <meta charset="UTF-8">
    <title>Главная</title>
    <link href="css/style.css" type="text/css" rel="stylesheet"/>
</head>
<body>

    <div class="header">
        <div class="name">
            Главная страница
        </div>
        <div class="links">

            <?php 
            if ( isset( $_SESSION['logged_user'] ) ) {
                echo '<a href="profile.php">'.$_SESSION['logged_user']->login.'</a>';
                echo '<a href="logout.php">Выйти</a>';
            } else {
                echo '<a href="login.php">Войти</a>';
                echo '<a href="signup.php">Зарегистрироваться</a>';
            }
            ?>

        </div>
    </div>
    <hr>    

<?php 
    QRcode::png( 'http://localhost/index.php?n=1' , 'img/1.png' , 'H');
 ?>
 <img src="img/1.png">

</body>
</html>