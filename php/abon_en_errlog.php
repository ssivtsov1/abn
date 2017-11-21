<?php

header('Content-type: text/html; charset=utf-8');
//
error_reporting(0);

require_once 'abon_en_func.php';

startPage();
start_lform();
session_name("session_kaa");
session_start();
middle_errlform();
end_lform();
endPage();
?>