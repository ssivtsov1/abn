<?php
header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();

error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];
session_write_close();

try {

if (isset($_POST['submitButton'])) 
{    
  $oper=$_POST['submitButton'];
}
else
{    
    if (isset($_POST['operation'])) 
    {    
    $oper=$_POST['operation'];
    }
}
//------------------------------------------------------
if ($oper=="bt_edit") {

$p_id_paccnt    = sql_field_val('id_paccnt','int');
$p_id_mp        = sql_field_val('id_mp','int');  // код учета, заполняется только для счетчика 
$p_type_eqp     = sql_field_val('type_eqp','int');
$p_num_eqp      = sql_field_val('num_eqp','string');
$p_name_eqp     = sql_field_val('name_eqp','string');
$p_addr         = sql_field_val('addr','record');
$p_dt_install   = sql_field_val('dt_install','date');
$p_dt_change    = sql_field_val('dt_change','date');
$p_loss_power   = sql_field_val('loss_power','int');
$p_is_owner     = sql_field_val('is_owner','int');
//$p_id_tree     = sql_field_val('id_tree','int');
$p_id_tree = 0;
$p_lvl     = sql_field_val('lvl','int');

$p_change_date    = sql_field_val('change_date','date');

$p_id=sql_field_val('id','int');    
$p_id_usr = $session_user; // где взять текущего юзера?    

$result=pg_query($Link,"begin");
 if (!($result)) 
 {
   echo_result(2,pg_last_error($Link));
   return;
 }    

 $QE="select eqt_change_fun(2,$p_id_paccnt,$p_id_tree,$p_change_date,$p_id_usr,$p_id,$p_id_mp,1)";
 
 $result=pg_query($Link,$QE);
 if (!($result)) 
 {
   echo_result(2,pg_last_error($Link));
   pg_query($Link,"rollback");   
   return;
 }    


$QE="UPDATE eqm_equipment_tbl set
  num_eqp=$p_num_eqp, name_eqp=$p_name_eqp, addr=$p_addr, id_paccnt=$p_id_paccnt, 
       dt_install=$p_dt_install, dt_change=$p_dt_change, loss_power=$p_loss_power, is_owner=$p_is_owner, 
 lvl = $p_lvl 
 WHERE id = $p_id;";

 //echo json_encode(array('errMess'=>'Error: '));

 $result=pg_query($Link,$QE);
 
 if (!($result)) 
 {
   echo_result(2,pg_last_error($Link));
   pg_query($Link,"rollback");   
   return;
 }    
 
      
 switch ($p_type_eqp) {
    case 2: // compensator
        
        $p_id_type_compens      = sql_field_val('id_type_compens','int');
        
        $QE3 = "update eqm_compensator_tbl set id_type_eqp = $p_id_type_compens 
        where code_eqp = $p_id ";

        break;
    case 3: //switch
        $p_id_type_switch      = sql_field_val('id_type_switch','int');
        
        $QE3 = "update eqm_switch_tbl set id_type_eqp = $p_id_type_switch 
        where code_eqp = $p_id ";
        break;
    case 5: //fuse
        $p_id_type_fuse      = sql_field_val('id_type_fuse','int');
        
        $QE3 = "update eqm_fuse_tbl set id_type_eqp = $p_id_type_fuse 
        where code_eqp = $p_id ";
        break;

    case 6: //cable

        $p_id_type_cable      = sql_field_val('id_type_cable','int');
        $p_length_cable      = sql_field_val('length_cable','int');
        $p_id_voltage_cable      = sql_field_val('id_voltage_cable','int');
        
        $QE3 = "update eqm_line_c_tbl set id_type_eqp = $p_id_type_cable,
        length = $p_length_cable, id_voltage= $p_id_voltage_cable 
        where code_eqp = $p_id ";
        
        break;
    case 7: //air

        $p_id_type_corde      = sql_field_val('id_type_corde','int');
        $p_length_corde      = sql_field_val('length_corde','int');
        $p_id_voltage_corde      = sql_field_val('id_voltage_corde','int');
        $p_id_pillar      = sql_field_val('id_pillar','int');
        
        $QE3 = "update eqm_line_a_tbl set id_type_eqp =$p_id_type_corde,
        length = $p_length_corde,id_voltage = $p_id_voltage_corde,id_pillar = $p_id_pillar 
        where code_eqp = $p_id ";
        
        break;
    /*
    case 12: //point
        
        $p_id_station   = sql_field_val('id_station','int');
        $p_power        = sql_field_val('power','numeric');
        $p_calc_losts   = sql_field_val('calc_losts','int');
        $p_id_extra     = sql_field_val('id_extra','int');
        $p_smart         = sql_field_val('smart','int');
        $p_magnet       = sql_field_val('magnet','int');
        $p_id_type_meter= sql_field_val('id_type_meter','int');
        $p_num_meter    = sql_field_val('num_meter','string');
        $p_carry        = sql_field_val('carry','int');
        $p_dt_control   = sql_field_val('dt_control','date');
        $p_id_typecompa = sql_field_val('id_typecompa','int');
        $p_dt_control_ca= sql_field_val('dt_control_ca','date');
        $p_id_typecompu = sql_field_val('id_typecompu','int');
        $p_dt_control_cu= sql_field_val('dt_control_cu','date');
        $p_coef_comp    = sql_field_val('coef_comp','int');
        
        $QE3="UPDATE clm_meterpoint_tbl
        SET id_station=$p_id_station, power=$p_power, calc_losts=$p_calc_losts, 
        id_extra=$p_id_extra, smart=$p_smart, magnet=$p_magnet, id_type_meter=$p_id_type_meter, num_meter=$p_num_meter, 
        carry=$p_carry, dt_control=$p_dt_control, id_typecompa=$p_id_typecompa, dt_control_ca=$p_dt_control_ca, 
        id_typecompu=$p_id_typecompu, dt_control_cu=$p_dt_control_cu, coef_comp=$p_coef_comp
        where code_eqp = $p_id ";
        
        break;
  */
 }
 $res_e=pg_query($Link,$QE3);
 
 if ($res_e) {
     echo_result(1,'Data updated');
     pg_query($Link,"commit");
     return;
     }
 else {
     echo_result(2,pg_last_error($Link));
     pg_query($Link,"rollback");   
     return;
     }
     
}
//------------------------------------------------------
if ($oper=="bt_add") {

$p_id_paccnt    = sql_field_val('id_paccnt','int');
//$p_id_client    = sql_field_val('id_client','int');
$p_type_eqp     = sql_field_val('type_eqp','int');
$p_num_eqp      = sql_field_val('num_eqp','string');
$p_name_eqp     = sql_field_val('name_eqp','string');
$p_addr         = sql_field_val('addr','record');
$p_dt_install   = sql_field_val('dt_install','date');
$p_dt_change    = sql_field_val('dt_change','date');
$p_loss_power   = sql_field_val('loss_power','int');
$p_is_owner     = sql_field_val('is_owner','int');
$p_lvl          = sql_field_val('lvl','int');
$p_id_tree = 0;
/*
$p_code_eqp_e     = sql_field_val('code_eqp_e','int');

if ($p_code_eqp_e < 0)
{
    $p_code_eqp_e = 'null';
}
$p_id_tree     = sql_field_val('id_tree','int');
*/
$p_id_usr = $session_user; // где взять текущего юзера?    

//$nm_e=addcslashes($nm_e,"'");

 $result=pg_query($Link,"begin");
 if (!($result)) 
 {
   echo_result(2,pg_last_error($Link));
   return;
 }    

 $QE="select eqt_change_fun(1,$p_id_paccnt,$p_id_tree,$p_dt_install,$p_id_usr,null,null,1)";
 
 $result=pg_query($Link,$QE);
 if (!($result)) 
 {
   echo_result(2,pg_last_error($Link));
   pg_query($Link,"rollback");   
   return;
 }    
 

 $QE="INSERT INTO eqm_equipment_tbl(
            id, type_eqp, num_eqp, name_eqp, addr, id_paccnt, dt_install, 
            dt_change, loss_power, is_owner, lvl)
    VALUES (DEFAULT, $p_type_eqp, $p_num_eqp, $p_name_eqp, $p_addr, $p_id_paccnt, $p_dt_install, 
            $p_dt_install, $p_loss_power, $p_is_owner, $p_lvl) returning id as newid;";


 $result=pg_query($Link,$QE);
 if (!($result)) 
 {
   echo_result(2,pg_last_error($Link));
   pg_query($Link,"rollback");
   return;
 }    
 $row = pg_fetch_array($result);

 $new_id = $row[newid];
 /*  
 $QE2=" INSERT INTO eqm_eqp_tree_tbl(
         id_tree, code_eqp, code_eqp_e, name )
    VALUES ($p_id_tree, $new_id, $p_code_eqp_e, $p_name_eqp);";  
   
 $res_e=pg_query($Link,$QE2);

 if (!($res_e)) 
 {
   echo_result(2,pg_last_error($Link));
   pg_query($Link,"rollback");   
   return;
 }  
 */
 switch ($p_type_eqp) {
    case 2: // compensator
        
        $p_id_type_compens      = sql_field_val('id_type_compens','int');
        
        $QE3 = "insert into eqm_compensator_tbl(code_eqp,id_type_eqp) 
        values ($new_id,$p_id_type_compens) ";

        break;
    case 3: //switch
        $p_id_type_switch      = sql_field_val('id_type_switch','int');
        
        $QE3 = "insert into eqm_switch_tbl(code_eqp,id_type_eqp) 
        values ($new_id,$p_id_type_switch) ";
        break;
    case 5: //fuse
        $p_id_type_fuse      = sql_field_val('id_type_fuse','int');
        
        $QE3 = "insert into eqm_fuse_tbl(code_eqp,id_type_eqp) 
        values ($new_id,$p_id_type_fuse) ";
        break;

    case 6: //cable

        $p_id_type_cable      = sql_field_val('id_type_cable','int');
        $p_length_cable      = sql_field_val('length_cable','int');
        $p_id_voltage_cable      = sql_field_val('id_voltage_cable','int');
        
        $QE3 = "insert into eqm_line_c_tbl(code_eqp,id_type_eqp,length,id_voltage) 
        values ($new_id,$p_id_type_cable,$p_length_cable,$p_id_voltage_cable) ";
        
        break;
    case 7: //air

        $p_id_type_corde      = sql_field_val('id_type_corde','int');
        $p_length_corde      = sql_field_val('length_corde','int');
        $p_id_voltage_corde      = sql_field_val('id_voltage_corde','int');
        $p_id_pillar      = sql_field_val('id_pillar','int');
        
        $QE3 = "insert into eqm_line_a_tbl(code_eqp,id_type_eqp,length,id_voltage,id_pillar) 
        values ($new_id,$p_id_type_corde,$p_length_corde,$p_id_voltage_corde,$p_id_pillar) ";
        
        break;
    /*
    case 12: //point
        $p_id_mp   = sql_field_val('id_mp','int');
        
        if ($p_id_mp==0)
        {
            $p_id_station   = sql_field_val('id_station','int');
            $p_power        = sql_field_val('power','numeric');
            $p_calc_losts   = sql_field_val('calc_losts','int');
            $p_id_extra     = sql_field_val('id_extra','int');
            $p_smart         = sql_field_val('smart','int');
            $p_magnet       = sql_field_val('magnet','int');
            $p_id_type_meter= sql_field_val('id_type_meter','int');
            $p_num_meter    = sql_field_val('num_meter','string');
            $p_carry        = sql_field_val('carry','int');
            $p_dt_control   = sql_field_val('dt_control','date');
            $p_id_typecompa = sql_field_val('id_typecompa','int');
            $p_dt_control_ca= sql_field_val('dt_control_ca','date');
            $p_id_typecompu = sql_field_val('id_typecompu','int');
            $p_dt_control_cu= sql_field_val('dt_control_cu','date');
            $p_coef_comp    = sql_field_val('coef_comp','int');
        
            $QE3="INSERT INTO clm_meterpoint_tbl(
                id,id_paccnt, id_station, code_eqp, power, calc_losts, id_extra, 
                smart, magnet, id_type_meter, num_meter, carry, dt_control, id_typecompa, 
                dt_control_ca, id_typecompu, dt_control_cu, coef_comp, dt_b,id_person)
            VALUES (DEFAULT,$p_id_paccnt, $p_id_station, $new_id, $p_power, $p_calc_losts, $p_id_extra, 
                $p_smart, $p_magnet, $p_id_type_meter, $p_num_meter, $p_carry, $p_dt_control, $p_id_typecompa, 
                $p_dt_control_ca, $p_id_typecompu, $p_dt_control_cu, $p_coef_comp, $p_dt_install,$p_id_usr) returning id;";
        
            $res_e = pg_query($Link, $QE3);

            if (!($res_e)) {
                echo_result(2, pg_last_error($Link));
                pg_query($Link,"rollback");   
                return;
            }

            $row = pg_fetch_array($res_e);
            $new_id_meter = $row["id"];

            $QE3 = " INSERT INTO clm_meter_zone_tbl(id_meter, id_zone, kind_energy,dt_b,id_person)
                     VALUES ($new_id_meter, 0, 10,$p_dt_install,$p_id_usr);";
        }
        else
        {
            $QE3="update clm_meterpoint_tbl set code_eqp = $new_id
                where id = $p_id_mp;";
            
        }
        
        break;
  */
 }
 $res_e=pg_query($Link,$QE3);

 if ($res_e) {
     echo_result(-1,'Data ins');
     pg_query($Link,"commit");
     return;
     }
 else        {
     echo_result(2,pg_last_error($Link));
     pg_query($Link,"rollback");
     return;
     }
       
     

 }
//------------------------------------------------------

}
catch (Exception $e) {

 echo echo_result(1,'Error: '.$e->getMessage());
}

?>