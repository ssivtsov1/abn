<?php
header("Content-type: application/json;charset=utf-8");

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();

error_reporting(1);

$Link = get_database_link($_SESSION,1);
$session_user = $_SESSION['ses_usr_id'];
session_write_close();
echo echo_result(-1, "ok");
//die("Истек срок ожидания (Время жизни сессии)! Обновите страницу!");
?>