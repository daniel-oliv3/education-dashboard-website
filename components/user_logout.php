<?php

   include 'connect.php';

   setcookie('user_id', '', time() - 1, '/');

   header('location:../home.php');

?>

<!--
    Autor: Daniel Oliveira
    Email: danieloliveira.webmaster@gmail.com
    Manaus/Amazonas
    04/02/2023
-->