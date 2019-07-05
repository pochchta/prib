<?php 

require 'libs/rb.php';
R::setup( 'mysql:host=localhost;dbname=devices',
        'root', '' ); //for both mysql or mariaDB

session_start();
?>