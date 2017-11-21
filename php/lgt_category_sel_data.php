<?php
header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();

error_reporting(1);

$Link = get_database_link($_SESSION,1);
$session_user = $_SESSION['ses_usr_id'];
session_write_close();

$node = sql_field_val('cur_node', 'int');

$Query= " (select id,name from lgi_kategor_tbl 
        where ((lgt_isparent_fun(id, $node) = false) and (id<>$node)) or ($node is null)
        order by sort) as ss ";

$lcategoryselect = DbTableSelect($Link,$Query,'id','name');

echo $lcategoryselect;


?>