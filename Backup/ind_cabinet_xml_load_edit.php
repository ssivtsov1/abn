<?php

header('Content-type: text/html; charset=utf-8');

include_once 'abon_en_func.php';
include_once 'abon_ded_func.php';

session_name("session_kaa");
session_start();

error_reporting(0);

$Link = get_database_link($_SESSION,2);
$session_user = $_SESSION['ses_usr_id'];
session_write_close();


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

$result = pg_query($Link, "select crt_ttbl();");
if (!($result)) {
    echo_result(2, pg_last_error($Link) );
    return;
}

$result = pg_query($Link, "insert into syi_sysvars_tmp (id, ident,type_ident, value_ident) 
     values(120,'id_person','int', $session_user);");

if (!($result)) {
    echo_result(2, pg_last_error($Link) );
    return;
}



try {

    if ($oper == "load") {

        if ($_FILES) {
            if ($_FILES['xml_file']['error'] <= 0) {
                $name = $_FILES['xml_file']['name'];
                $size = $_FILES['xml_file']['size'];
                $tmp_name = $_FILES['xml_file']['tmp_name'];

                $xml = simplexml_load_file($tmp_name); 
                
                 foreach ($xml->node as $node) {
                     $book = "'".$node->book."'" ;
                     $code = "'".$node->code."'" ;
                     $index_accnt = $node->index_accnt;
                     $dat_ind = "'".$node->dat_ind."'";
                     $num_eqp = "'".$node->num_eqp."'";
                     $id_zone = $node->id_zone;
                     $value = $node->value;
                     $coef_comp = $node->coef_comp;
                     $carry = $node->carry;
                     $value_diff = $node->value_diff;
                     $mmgg = "'".$node->mmgg."'";

                     $QE = "INSERT INTO acd_cabindication_tbl(
                       id_paccnt, id_zone, kind_energy, mmgg, num_eqp, koef,  carry, dat_ind,  value_ind,  
                       id_status, id_operation )
                       select id, $id_zone, 10, $mmgg, $num_eqp, $coef_comp, $carry, $dat_ind, $value, 0, 10
                       from clm_paccnt_tbl where index_accnt = $index_accnt;";
                     
                     //echo $QE;
                     $res = pg_query($Link, $QE);
                     if (!($res)) {
                       echo_result(2, pg_last_error($Link));
                       return;
                     }
                     
                 }
                
                echo_result(1, 'xml ok');                
        
            }
        }
    }
} catch (Exception $e) {

    echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>


