<?php
header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

//session_name("session_kaa");
//session_start();

error_reporting(1);

//$Link = get_database_link($_SESSION,1);

$base = sql_field_val('base', 'varchar');

$cstr="host=$app_host dbname=$base user=$app_user password=$app_pass"; 
$link = pg_connect($cstr) or die("Connection Error: " . pg_last_error($link));

$Query= " (select id,name from syi_user 
        where flag_type =0 order by id) as ss ";

$lselect = DbTableSelect($link,$Query,'id','name');

echo $lselect;


?>