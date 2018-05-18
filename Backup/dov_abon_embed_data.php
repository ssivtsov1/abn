<?php
header("Content-type: application/json;charset=utf-8");

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();

$Link = get_database_link($_SESSION,1);
$session_user = $_SESSION['ses_usr_id'];
session_write_close();

error_reporting(1);

$id = $_POST['id']; 

if ($id !=0) 
{
$SQL = "
select c.id, c.last_name, c.name, c.patron_name, c.s_doc, c.n_doc, 
       to_char(c.dt_doc, 'DD.MM.YYYY') as dt_doc, 
       c.who_doc, c.tax_number, c.home_phone, c.work_phone, 
       c.mob_phone, c.e_mail, 
       to_char(c.dt_b, 'DD.MM.YYYY') as dt_b, 
      c.work_period, 
    to_char(c.dt_input, 'DD.MM.YYYY') as dt_input,  
    c.addr_reg,  c.addr_live, c.note,
    adr1.adr||' '||address_print(c.addr_reg) as addr_reg_str, 
adr2.adr||' '||address_print(c.addr_live) as addr_live_str 
from clm_abon_tbl as c 
left join adt_addr_tbl as adr1 on (adr1.id = (c.addr_reg).id_class)
left join adt_addr_tbl as adr2 on (adr2.id = (c.addr_live).id_class)      
 where c.id = $id ;";
   
$result = pg_query($Link,$SQL);

 if ($result) {
    $row = pg_fetch_array($result);
    echo json_encode($row);
 }
 else
 {
     echo_result(2,pg_last_error($Link));
 }

}    
 else {
     echo_result(2,"Unknown id!");    
}




?>
