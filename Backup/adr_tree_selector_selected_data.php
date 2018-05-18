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

$id_class = $_POST['id_class']; 
$full_addr_mode = $_POST['full_addr_mode']; 

$SQL = "select * from adi_class_tbl where id = $id_class ";
$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$idk_class = $row['idk_class'];


if ($full_addr_mode==1)
    $SQL = "select fulladr as adr from adv_fulladdr_tbl where id = $id_class ";
else
    if ($full_addr_mode==2)
        
        if ($idk_class<=3)
            $SQL = "select  coalesce((coalesce(k.short_prefix||' ','')||coalesce(c.name,''))||coalesce(' '||k.short_postfix,''),'') as adr
                from adi_class_tbl as c 
                join adk_class_tbl as k on (k.id =c.idk_class)            
                where c.id = $id_class ";
        else    
            $SQL = "select adr from adt_addr_tbl where id = $id_class ";
        
    else
        $SQL = "select adr from adt_addr_tbl where id = $id_class ";

$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link) );

$row = pg_fetch_array($result);

$data = $row['adr'];

//header("Content-type: application/json;charset=utf-8");
echo $data;

?>
