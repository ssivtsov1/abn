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

    if (isset($_POST['submitButton'])) {
        $oper = $_POST['submitButton'];
    } else {
        if (isset($_POST['oper'])) {
            $oper = $_POST['oper'];
        }
    }

//throw new Exception(json_encode($_POST));
    $p_id_session = sql_field_val('id_session', 'int');
    
    $p_id = sql_field_val('id', 'int');
    $p_id_paccnt = sql_field_val('id_paccnt', 'int');
    $p_id_meter = sql_field_val('id_meter', 'int');
    $p_idk_work = sql_field_val('idk_work', 'int');
    $p_dt_work = sql_field_val('dt_work', 'date');
    $p_work_period = sql_field_val('work_period', 'date');
    $p_id_position = sql_field_val('id_position', 'int');
    $p_note = sql_field_val('note', 'string');
    $p_act_num = sql_field_val('act_num', 'string');
    $p_id_usr = $session_user;

    //-----------------------------------------
    $p_newmeter_id = sql_field_val('newmeter_id', 'int');
    $p_code_eqp = sql_field_val('code_eqp', 'int');
    $p_id_station = sql_field_val('id_station', 'int');
    $p_power = sql_field_val('power', 'numeric');
    $p_calc_losts = sql_field_val('calc_losts', 'int');
    $p_id_extra = sql_field_val('id_extra', 'int');
    $p_smart = sql_field_val('smart', 'int');
    $p_magnet = sql_field_val('magnet', 'int');
    $p_id_type_meter = sql_field_val('id_type_meter', 'int');
    $p_num_meter = sql_field_val('num_meter', 'string');
    $p_carry = sql_field_val('carry', 'int');
    $p_dt_control = sql_field_val('dt_control', 'date');
    $p_id_typecompa = sql_field_val('id_typecompa', 'int');
    $p_dt_control_ca = sql_field_val('dt_control_ca', 'date');
    $p_id_typecompu = sql_field_val('id_typecompu', 'int');
    $p_dt_control_cu = sql_field_val('dt_control_cu', 'date');
    $p_coef_comp = sql_field_val('coef_comp', 'int');


    //-----------------------------------------
    $json_str = $_POST['indication_json'];
//echo $json_str;
    $json_str = stripslashes($json_str);
//echo $json_str; 
    $indic_lines = json_decode($json_str, true);

    
//------------------------------------------------------    
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
    

//------------------------------------------------------
    if ($oper == "edit") {

        // перед стартом транзакции записать контрольную запись, 
        // чтобы не допускать повторного старта операции
        $QE = "INSERT INTO clm_works_lock_tbl( id_paccnt, idk_work,dt_work,id_person )
           values( $p_id_paccnt, $p_idk_work,$p_dt_work,$p_id_usr ) ; ";
        
        $result = pg_query($Link, $QE);
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
            return;
        }
        //-----------------------------------------------------------------        

        $result = pg_query($Link, "begin");
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
            return;
        }

        $QE = "update clm_works_tbl set idk_work = $p_idk_work ,dt_work = $p_dt_work ,
         id_position = $p_id_position, note = $p_note , work_period = $p_work_period, act_num = $p_act_num
         where id = $p_id ; ";

        $res_e = pg_query($Link, $QE);

        if ($res_e) {

            $row = pg_fetch_array($res_e);
            $new_id = $row['id'];

            $cnt = 0;
            foreach ($indic_lines as $record) {
                $cnt++;
                $id = $record['id'];

		$num_m = $record['num_meter']; 
		
                $id_meter = $record['id'];
                $id_type_meter = $record['id_type_meter'];
                $id_zone = $record['id_zone'];
                $id_metzone = $record['id_metzone'];
                
                $num_meter = "'$num_m'";
                $k_tr = $record['k_tr'];
                $carry = $record['carry'];
                $id_p_indic = $record['id_p_indic'];
                if ($id_p_indic=='') $id_p_indic='null';
                
                $indic_real = $record['indic_real']; 
                if ($indic_real=='') $indic_real='null';                
                
                $indic = $record['indic'];
                $idk_oper = $record['idk_oper'];

                if ($indic == '')
                    $indic = 'null';

                if ($id < 0) {
                    $QE = " INSERT INTO clm_work_indications_tbl(
                    id_work, id_paccnt, id_meter, idk_oper, id_history, id_metzone, id_zone, 
                    num_meter, id_type_meter, k_tr, carry, id_p_indic, indic, id_person , indic_real) 
                    values ($new_id, $p_id_paccnt, $id_meter, $idk_oper, null, $id_metzone, $id_zone, 
                    $num_meter, $id_type_meter, $k_tr, $carry, $id_p_indic, $indic, $p_id_usr, $indic_real); ";

                    $res_e = pg_query($Link, $QE);

                    if (!($res_e)) {
                        echo_result(2, pg_last_error($Link) . $QE);
                        pg_query($Link, "rollback");
                        return;
                    }
                }
                if ($id > 0) {
                    $QE = " update clm_work_indications_tbl set indic = $indic , id_person = $p_id_usr,
                    indic_real = $indic_real
                    where id =  $id;";

                    $res_e = pg_query($Link, $QE);

                    if (!($res_e)) {
                        echo_result(2, pg_last_error($Link) . $QE);
                        pg_query($Link, "rollback");
                        return;
                    }
                }
                
            }
            
            $QE = " update clm_work_indications_tbl 
                    set indic = tmp.indic , id_person = $p_id_usr
            from  clm_meter_zone_tmp as tmp where tmp.id_work = $p_id 
                    and tmp.id_session = $p_id_session
                    and clm_work_indications_tbl.id_work = $p_id 
                    and clm_work_indications_tbl.id = tmp.id_work_indic
                    and (coalesce(clm_work_indications_tbl.indic,0)<> coalesce(tmp.indic,0)
                      or coalesce(clm_work_indications_tbl.id_zone,0)<> coalesce(tmp.id_zone,0)); ";
            
            $res_e = pg_query($Link, $QE);

            if (!($res_e)) {
                echo_result(2, pg_last_error($Link) . $QE);
                pg_query($Link, "rollback");
                return;
            }
            //   ----------- удаленные зоны ----
            $QE = " select count(*) as cnt_del 
                   from clm_work_indications_tbl as i
                   left join clm_meter_zone_tmp as tmp on 
                    (tmp.id_meter = i.id_meter and tmp.id_zone = i.id_zone 
                      and tmp.id_work_indic  = i.id 
                      and tmp.id_session = $p_id_session
                      and tmp.id_work = $p_id 
                    )
                   where i.id_work = $p_id      
                   and i.idk_oper = 1
                   and tmp.id is null; ";

            $result = pg_query($Link, $QE);          
            if (!($result)) {
                  echo_result(2, pg_last_error($Link) . $QE);
                  pg_query($Link, "rollback");
                  return;
            }
            
            $row = pg_fetch_array($result);
            
            if ($row['cnt_del']!=0)
            {
            
             $QE = "select eqt_change_fun(12,$p_id_paccnt,null,$p_dt_work,$p_id_usr,null,$p_newmeter_id,0)";

             $result = pg_query($Link, $QE);
             if (!($result)) {
                echo_result(2, pg_last_error($Link));
                pg_query($Link, "rollback");
                return;
             }
                
             
             $QE = "delete from clm_work_indications_tbl 
                    where 
                    clm_work_indications_tbl.id_work = $p_id             
                    and clm_work_indications_tbl.idk_oper = 1
                    and not exists  (
                        select id_work_indic from clm_meter_zone_tmp as tmp where tmp.id_work = $p_id 
                        and tmp.id_session = $p_id_session
                        and tmp.id_zone = clm_work_indications_tbl.id_zone
                        and clm_work_indications_tbl.id = tmp.id_work_indic
                    );";
            
             $res_e = pg_query($Link, $QE);

             if (!($res_e)) {
                echo_result(2, pg_last_error($Link) . $QE);
                pg_query($Link, "rollback");
                return;
             }
              
            }            
            //----------проверить есть ли новые строки -------------//
            $QE = " select count(*) as cnt_new from clm_meter_zone_tmp where id_work = $p_id 
                    and id_session = $p_id_session and id_work_indic = -1; ";

            $result = pg_query($Link, $QE);          
            if (!($result)) {
                  echo_result(2, pg_last_error($Link) . $QE);
                  pg_query($Link, "rollback");
                  return;
            }
            
            $row = pg_fetch_array($result);
            
            if ($row['cnt_new']!=0)
            {
             $QE = "select eqt_change_fun(11,$p_id_paccnt,null,$p_dt_work,$p_id_usr,null,$p_newmeter_id,1)";

             $result = pg_query($Link, $QE);
             if (!($result)) {
                echo_result(2, pg_last_error($Link));
                pg_query($Link, "rollback");
                return;
             }
                
             $QE = " INSERT INTO clm_meter_zone_tbl(id_meter, id_zone, kind_energy,dt_b,id_person)
             select id_meter, id_zone, kind_energy,$p_dt_work, $p_id_usr 
             from clm_meter_zone_tmp 
             where id_session = $p_id_session and id_work = $p_id  and id_work_indic = -1 ;";

             $res_e = pg_query($Link, $QE);
             if (!($result)) {
                echo_result(2, pg_last_error($Link));
                pg_query($Link, "rollback");
                return;
             }

             $QE = " INSERT INTO clm_work_indications_tbl(
             id_work, id_paccnt, id_meter, idk_oper, id_history, id_metzone, id_zone, 
             num_meter, id_type_meter, k_tr,carry, id_p_indic, indic , id_person) 
             select $p_id,$p_id_paccnt,zt.id_meter,1,m.id_key,z.id,z.id_zone,m.num_meter,
                m.id_type_meter,m.coef_comp,m.carry, null, zt.indic , $p_id_usr
                from clm_meterpoint_h as m 
                join clm_meter_zone_h as z on (z.id_meter = m.id)
                join clm_meter_zone_tmp as zt on (zt.id_zone = z.id_zone and zt.kind_energy = z.kind_energy)
                where m.dt_b = $p_dt_work and z.dt_b = $p_dt_work
                and m.id = $p_newmeter_id
                and zt.id_session = $p_id_session and zt.id_work = $p_id  and zt.id_work_indic = -1 ; ";
             
             $res_e = pg_query($Link, $QE);
             if (!($result)) {
                echo_result(2, pg_last_error($Link));
                pg_query($Link, "rollback");
                return;
             }
             
             
             
            }
            
            /*
            
            
            
            
            $QE = " select * from clm_meter_zone_tmp where id_work = $p_id 
                    and id_session = $p_id_session; ";

            $result = pg_query($Link, $QE);          
            if (!($result)) {
                  pg_query($Link, "rollback");
                  echo_result(2, pg_last_error($Link) . $QE);
                  return;
            }
            
            
            while($row = pg_fetch_array($result)) {

                    if ($row['id_work_indic']!=-1)
                    {
                        $id_ind = $row['id_work_indic'];
                        $QE2 = " select * from clm_work_indications_tbl where id_work = $p_id 
                        and id = $id_ind; ";
                        
                        $result2 = pg_query($Link, $QE2);          
                        if (!($result2)) {
                           pg_query($Link, "rollback");
                           echo_result(2, pg_last_error($Link) . $QE2);
                           return;
                        }
                        
                        $row2 = pg_fetch_array($result2);
                        
                        if (($row['indic']!=$row2['indic'])||($row['id_zone']!=$row2['id_zone']))
                        {
                            
                            $QE3 = " update clm_work_indications_tbl where id_work = $p_id 
                            and id = $id_ind; ";
                        
                            $result2 = pg_query($Link, $QE2);          
                            if (!($result2)) {
                               pg_query($Link, "rollback");
                               echo_result(2, pg_last_error($Link) . $QE2);
                               return;
                            }
                            
                            
                            
                        }
                        
                    }
                

                
               foreach ($fildsArray as $fild) {
                $data['rows'][$i][$fild['f_name']] = $row[$fild['f_name']];
                }

                $i++;
            }             
            */
            

            pg_query($Link, "commit");
            echo_result(1, 'Data updated', $new_id);
        } else {
            echo_result(2, pg_last_error($Link));
            pg_query($Link, "rollback");
        }
        
        $QE = "delete from clm_works_lock_tbl where id_paccnt = $p_id_paccnt 
        and idk_work = $p_idk_work and dt_work = $p_dt_work and id_person = $p_id_usr; ";
        
        $result = pg_query($Link, $QE);
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
        }        
    }
//------------------------------------------------------
    if ($oper == "add") {
       
        // перед стартом транзакции записать контрольную запись, 
        // чтобы не допускать повторного старта операции
        $QE = "INSERT INTO clm_works_lock_tbl( id_paccnt, idk_work,dt_work,id_person )
           values( $p_id_paccnt, $p_idk_work,$p_dt_work,$p_id_usr ) ; ";
        
        $result = pg_query($Link, $QE);
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
            return;
        }
        //-----------------------------------------------------------------

        $result = pg_query($Link, "begin");
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
            return;
        }


        $QE = "INSERT INTO clm_works_tbl( id,id_paccnt, idk_work,dt_work,id_position,note,id_person, work_period, act_num )
           values( DEFAULT,$p_id_paccnt, $p_idk_work,$p_dt_work,$p_id_position,$p_note,$p_id_usr , $p_work_period ,$p_act_num ) returning id; ";

        $res_e = pg_query($Link, $QE);

        if ($res_e) {

            $row = pg_fetch_array($res_e);
            $new_id = $row['id'];

            ////-------таблица показаний ---
            $cnt = 0;
            foreach ($indic_lines as $record) {
                $cnt++;
                $id = $record['id'];

                $id_meter = $record['id_meter'];
                $id_type_meter = $record['id_type_meter'];
                $id_zone = $record['id_zone'];
                $id_metzone = $record['id_metzone'];
                

		$num_m = $record['num_meter'];
                $num_meter = "'$num_m'";
                
                $k_tr = $record['k_tr'];
                $carry = $record['carry'];
                $id_p_indic = $record['id_p_indic'];
                if ($id_p_indic=='') $id_p_indic='null';
                $indic = $record['indic'];
                $idk_oper = $record['idk_oper'];

                if ($indic == '')
                    $indic = 'null';

                $indic_real = $record['indic_real']; 
                if ($indic_real=='') $indic_real='null';                
                

                $QE = " INSERT INTO clm_work_indications_tbl(
                 id_work, id_paccnt, id_meter, idk_oper, id_history, id_metzone, id_zone, 
                 num_meter, id_type_meter, k_tr, carry, id_p_indic, indic ,indic_real,  id_person) 
                  values ($new_id, $p_id_paccnt, $id_meter, $idk_oper, null, $id_metzone, $id_zone, 
                 $num_meter, $id_type_meter, $k_tr,$carry, $id_p_indic, $indic ,$indic_real , $p_id_usr); ";

                $res_e = pg_query($Link, $QE);

                if (!($res_e)) {
                    echo_result(2, pg_last_error($Link) . $QE);
                    pg_query($Link, "rollback");
                    return;
                }
            }
            //----------------------------------------------
            if (($p_idk_work==1)||($p_idk_work==2)) // новый счетчик
            {
                
                $QE = "select eqt_change_fun(1,$p_id_paccnt,null,$p_dt_work,$p_id_usr,null,null,1) as hist_id; ";

                $result = pg_query($Link, $QE);
                if (!($result)) {
                    echo_result(2, pg_last_error($Link) . $QE);
                    pg_query($Link, "rollback");
                    return;
                }
                $row = pg_fetch_array($result);
                $id_history = $row["hist_id"];
                
                
                $QE = "INSERT INTO clm_meterpoint_tbl(
                id,id_paccnt, id_station, code_eqp, power, calc_losts, id_extra, 
                smart, magnet, id_type_meter, num_meter, carry, dt_control, id_typecompa, 
                dt_control_ca, id_typecompu, dt_control_cu, coef_comp,dt_b,id_person)
                VALUES (DEFAULT,$p_id_paccnt, $p_id_station, $p_code_eqp, $p_power, $p_calc_losts, $p_id_extra, 
                $p_smart, $p_magnet, $p_id_type_meter, $p_num_meter, $p_carry, $p_dt_control, $p_id_typecompa, 
                $p_dt_control_ca, $p_id_typecompu, $p_dt_control_cu, $p_coef_comp,$p_dt_work,$p_id_usr) returning id;";


                $res_e = pg_query($Link, $QE);

                if (!($res_e)) {
                    echo_result(2, pg_last_error($Link) . $QE);
                    pg_query($Link, "rollback");
                    return;
                }

                $row = pg_fetch_array($res_e);
                $new_id_meter = $row["id"];

                $QE = " INSERT INTO clm_meter_zone_tbl(id_meter, id_zone, kind_energy,dt_b,id_person)
                select $new_id_meter, id_zone, kind_energy,$p_dt_work, $p_id_usr 
                from clm_meter_zone_tmp 
                where id_session = $p_id_session and id_work = 0 ;";

                $res_e = pg_query($Link, $QE);

                if (!($res_e)) {
                    echo_result(2, pg_last_error($Link) . $QE);
                    pg_query($Link, "rollback");
                    return;
                }
                

                $QE = " INSERT INTO clm_work_indications_tbl(
                 id_work, id_paccnt, id_meter, idk_oper, id_history, id_metzone, id_zone, 
                 num_meter, id_type_meter, k_tr, carry, id_p_indic, indic, id_person ) 
                select $new_id,$p_id_paccnt,$new_id_meter,1,m.id_key,z.id,z.id_zone,m.num_meter,
                m.id_type_meter,m.coef_comp,m.carry, null, zt.indic,  $p_id_usr
                from clm_meterpoint_h as m 
                join clm_meter_zone_h as z on (z.id_meter = m.id)
                join clm_meter_zone_tmp as zt on (zt.id_zone = z.id_zone and zt.kind_energy = z.kind_energy)
                where m.dt_b = $p_dt_work and z.dt_b = $p_dt_work
                and m.id = $new_id_meter
                and zt.id_session = $p_id_session and zt.id_work = 0 ; ";

                
                $res_e = pg_query($Link, $QE);

                if (!($res_e)) {
                    echo_result(2, pg_last_error($Link) . $QE);
                    pg_query($Link, "rollback");
                    return;
                }

                $QE = " select prs_runner_paccnt_meter_add_fun($p_id_paccnt,$new_id_meter,$p_dt_work,$p_id_usr ) ; ";
                
                $res_e = pg_query($Link, $QE);

                if (!($res_e)) {
                    echo_result(2, pg_last_error($Link) . $QE);
                    pg_query($Link, "rollback");
                    return;
                }
                
            }

            if (($p_idk_work==2)||($p_idk_work==3)) // демонтаж
            {

                $QE="select eqt_change_fun(3,$p_id_paccnt,null,$p_dt_work,$p_id_usr,null,$p_id_meter,1)";

                $result = pg_query($Link, $QE);
                if (!($result)) {
                    echo_result(2, pg_last_error($Link) . $QE);
                    pg_query($Link, "rollback");
                    return;
                }
                $row = pg_fetch_array($result);
                $id_history = $row["hist_id"];
                
                
                $QE = "Delete from clm_meterpoint_tbl where id= $p_id_meter;";

                $res_e = pg_query($Link, $QE);
                
                if (!($res_e)) {
                    echo_result(2, pg_last_error($Link) . $QE);
                    pg_query($Link, "rollback");
                    return;
                }
                
                $QE = " update clm_work_indications_tbl set id_history = h.id_key
                from clm_meterpoint_h as h where h.id = clm_work_indications_tbl.id_meter
                and h.dt_e = $p_dt_work  and clm_work_indications_tbl.id_work = $new_id ;";
                
                $res_e = pg_query($Link, $QE);
                
                if (!($res_e)) {
                    echo_result(2, pg_last_error($Link) . $QE);
                    pg_query($Link, "rollback");
                    return;
                }
                
            }
            
            pg_query($Link, "commit");
            echo_result(1, 'Data ins', $new_id);
        } else {
            echo_result(2, pg_last_error($Link) . $QE);
            pg_query($Link, "rollback");
        }
        
        
        $QE = "delete from clm_works_lock_tbl where id_paccnt = $p_id_paccnt 
        and idk_work = $p_idk_work and dt_work = $p_dt_work and id_person = $p_id_usr; ";
        
        $result = pg_query($Link, $QE);
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
        }
        
    }
//------------------------------------------------------
    if ($oper == "del") {

        $result = pg_query($Link, "begin");
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
            return;
        }

        $result = pg_query($Link, "insert into syi_sysvars_tmp (id, ident,type_ident, value_ident) 
        values(170,'del_ctrl_ignore','int', '0');");

        if (!($result)) {
            echo_result(2, pg_last_error($Link) );
            pg_query($Link, "rollback");
            return;
        }        
        
        
        $QE = "Delete from clm_works_tbl where id= $p_id;";

        $res_e = pg_query($Link, $QE);

        if ($res_e) {
            echo_result(-1, 'Data delated');
            pg_query($Link, "commit");
        } else {
            echo_result(2, pg_last_error($Link));
            pg_query($Link, "rollback");
        }
    }
//------------------------------------------------------
    if ($oper == "del_extra") {

        $result = pg_query($Link, "begin");
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
            return;
        }

        $result = pg_query($Link, "insert into syi_sysvars_tmp (id, ident,type_ident, value_ident) 
        values(170,'del_ctrl_ignore','int', '1');");

        if (!($result)) {
            echo_result(2, pg_last_error($Link) );
            pg_query($Link, "rollback");
            return;
        }        
        
        
        $QE = "Delete from clm_works_tbl where id= $p_id;";

        $res_e = pg_query($Link, $QE);

        if ($res_e) {
            echo_result(-1, 'Data delated');
            pg_query($Link, "commit");
        } else {
            echo_result(2, pg_last_error($Link));
            pg_query($Link, "rollback");
        }
    }    
    
} catch (Exception $e) {

    echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>