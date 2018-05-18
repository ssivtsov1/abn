<?php
header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();

error_reporting(0);

$Link = get_database_link($_SESSION,2);
$session_user = $_SESSION['ses_usr_id'];
session_write_close();

try {

    
if (isset($_POST['submitButton'])) 
{    
  $oper=$_POST['submitButton'];
}
else
{    
    if (isset($_POST['oper'])) {
        $oper=$_POST['oper'];
    }
}
    
//throw new Exception(json_encode($_POST));

//------------------------------------------------------
if ($oper=="edit") {

$p_name = sql_field_val('name','string');
$p_short_name = sql_field_val('short_name','string');
$p_code = sql_field_val('code','string');

$p_addr=sql_field_val('addr','record');
$p_addr_str=sql_field_val('addr_str','string');

$p_id_boss = sql_field_val('id_boss','int');
$p_id_buh = sql_field_val('id_buh','int');

$p_id_sbutboss = sql_field_val('id_sbutboss','int');
$p_id_warningboss = sql_field_val('id_warningboss','int');
$p_id_spravboss = sql_field_val('id_spravboss','int');

$p_ae_mfo = sql_field_val('ae_mfo','int');
$p_ae_account = sql_field_val('ae_account','int');

$p_phone_bill = sql_field_val('phone_bill','string');
$p_addr_ikc = sql_field_val('addr_ikc','string');
$p_phone_ikc = sql_field_val('phone_ikc','string');

$p_licens_num = sql_field_val('licens_num','string');
$p_okpo_num = sql_field_val('okpo_num','string');
$p_tax_num = sql_field_val('tax_num','string');

$p_print_name = sql_field_val('print_name','string');
$p_small_name = sql_field_val('small_name','string');

$p_warning_addr = sql_field_val('warning_addr','string');
$p_phone_warning = sql_field_val('phone_warning','string');


$p_addr_district = sql_field_val('addr_district','int');
$p_barcode_print = sql_field_val('barcode_print','int');
$p_qr_print = sql_field_val('qr_print','int');

$p_id=sql_field_val('id','int');    


$QE="UPDATE syi_resinfo_tbl
   SET name=$p_name, short_name=$p_short_name, code=$p_code, addr=$p_addr, addr_str=$p_addr_str, 
       id_boss=$p_id_boss, id_buh=$p_id_buh, ae_mfo=$p_ae_mfo, ae_account=$p_ae_account, 
       phone_bill=$p_phone_bill, addr_ikc=$p_addr_ikc, phone_ikc=$p_phone_ikc,
       licens_num=$p_licens_num, okpo_num=$p_okpo_num, tax_num=$p_tax_num,
       addr_district =$p_addr_district, print_name = $p_print_name,
       warning_addr = $p_warning_addr, id_sbutboss = $p_id_sbutboss,
       id_warningboss = $p_id_warningboss, small_name = $p_small_name , phone_warning = $p_phone_warning,
       barcode_print = $p_barcode_print, qr_print = $p_qr_print, 
       id_spravboss = $p_id_spravboss 
 WHERE id_department = $p_id;";

 $res_e=pg_query($Link,$QE);
 if ($res_e) {
     echo_result(1,'Data updated');

     }
 else        {
     echo_result(2,pg_last_error($Link));
     }
}
//------------------------------------------------------


}
catch (Exception $e) {

 echo echo_result(1,'Error: '.$e->getMessage());
}

?>