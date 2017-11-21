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

$p_id=sql_field_val('id_res','int');    

$SQL = "select r.*, address_print_full(r.addr,4) as addr_full,
 b.name as ae_bank_name, 
 boss.represent_name as boss_name,    
 buh.represent_name as buh_name,
 sbut.represent_name as sbutboss_name,    
 warn.represent_name as warningboss_name,   
 sprav.represent_name as spravboss_name,   
 av.fulladr as addr_district_name ,
 r.print_name, r.warning_addr 
    from syi_resinfo_tbl as r
    left join bank as b on (b.mfo = r.ae_mfo)
    left join prs_persons as boss on (boss.id = r.id_boss)
    left join prs_persons as buh on (buh.id = r.id_buh)
    
    left join prs_persons as sbut on (sbut.id = r.id_sbutboss)
    left join prs_persons as warn on (warn.id = r.id_warningboss)
    left join prs_persons as sprav on (sprav.id = r.id_spravboss)
    
    left join adv_fulladdr_tbl as av on (av.id = r.addr_district)
    where r.id_department = $p_id ;";

$result = pg_query($Link,$SQL);

 if ($result) {
    $row = pg_fetch_array($result);
    echo json_encode($row);
 }
 else
 {
     echo_result(2,pg_last_error($Link));
 }


?>
