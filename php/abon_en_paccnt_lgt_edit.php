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

    if (isset($_POST['oper'])) {
        $oper = $_POST['oper'];
    }

    $p_id_paccnt = sql_field_val('id_paccnt', 'int');

    $p_id_grp_lgt = sql_field_val('id_grp_lgt', 'int');
    $p_id_calc = sql_field_val('id_calc', 'int');
    $p_prior_lgt = sql_field_val('prior_lgt', 'int');
    $p_fio_lgt = sql_field_val('fio_lgt', 'string');
    $p_id_doc = sql_field_val('id_doc', 'int');
    $p_family_cnt = sql_field_val('family_cnt', 'int');
    $p_s_doc = sql_field_val('s_doc', 'string');
    $p_n_doc = sql_field_val('n_doc', 'string');
    $p_dt_doc = sql_field_val('dt_doc', 'date');
    $p_dt_doc_end = sql_field_val('dt_doc_end', 'date');
    $p_ident_cod_l = sql_field_val('ident_cod_l', 'string');
    $p_dt_reg = sql_field_val('dt_reg', 'date');
    $p_dt_start = sql_field_val('dt_start', 'date');
    $p_dt_end = sql_field_val('dt_end', 'date');
    $p_note = sql_field_val('note', 'string');
    $p_closed = sql_field_val('closed', 'int');

    $p_change_date    = sql_field_val('change_date','date');
    $p_id_usr = $session_user; // где взять текущего юзера?    

    $p_id = sql_field_val('id', 'int');

    
    $SQL = "select ''''||to_char( value_ident::date  , 'YYYY-MM-DD')||'''' as start_mmgg 
        from syi_sysvars_tbl where ident = 'dt_start';";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);    
    
    if ($result) {
        $row = pg_fetch_array($result); 
        $start_mmgg=$row['start_mmgg'];
    }    

//------------------------------------------------------
    if ($oper == "add") {

   
        $p_id_reason = sql_field_val('id_reason_new', 'int');
        
        $QE = "select c.id, (c.book||'/'||c.code)::varchar as bookcode from
         clm_paccnt_tbl as c
         join lgm_abon_tbl as lg on (lg.id_paccnt = c.id) 
         where ident_cod_l = $p_ident_cod_l and c.archive =0 
         and (
         ($p_dt_start < coalesce(lg.dt_end,'2030-01-01'::date))
         or 
         tintervalov(tinterval(GREATEST($start_mmgg::date,lg.dt_start)::timestamp::abstime,coalesce(lg.dt_end,'2030-01-01'::date)::timestamp::abstime),
                     tinterval(GREATEST($start_mmgg::date,($p_dt_start::date+'1 days'::interval)::date )::timestamp::abstime,coalesce($p_dt_end,'2030-01-01'::date)::timestamp::abstime)) )";
        
        
        $result = pg_query($Link, $QE);
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
            return;
        }
        $rows = pg_num_rows($result);
        if ($rows>0)
        {
            $row = pg_fetch_array($result);
            
            echo_result(3, pg_last_error($Link), $row['id'], $row['bookcode']);
            return;
        }

        $result = pg_query($Link, "begin");
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
            return;
        } 
        
        $QE = "select lgt_change_fun(1,$p_id_paccnt,null,$p_dt_start,$p_id_usr,$p_id_reason,1)";

        $result = pg_query($Link, $QE);
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
            pg_query($Link, "rollback");
            return;
        }


        $QE = "INSERT INTO lgm_abon_tbl(id, id_paccnt, id_calc, id_grp_lgt, prior_lgt, family_cnt, fio_lgt, id_doc, s_doc, 
            n_doc, dt_doc, dt_doc_end,ident_cod_l, dt_reg, dt_start, dt_end, id_person, note, closed)
            VALUES (DEFAULT, $p_id_paccnt, $p_id_calc, $p_id_grp_lgt, $p_prior_lgt, $p_family_cnt,$p_fio_lgt, $p_id_doc, $p_s_doc, 
            $p_n_doc, $p_dt_doc, $p_dt_doc_end, $p_ident_cod_l, $p_dt_reg, $p_dt_start, $p_dt_end,$p_id_usr,$p_note,$p_closed ) returning id;";

        $res_e = pg_query($Link, $QE);
        
        $row = pg_fetch_array($res_e);
        $new_id = $row["id"];
        
        if ($res_e) {
            pg_query($Link, "commit"); 
            echo_result(-1, 'Data ins',$new_id); 
        } else {
            echo_result(2, pg_last_error($Link));
            pg_query($Link, "rollback");            
        }
    }
//------------------------------------------------------
    if ($oper == "edit") {

        $p_id_reason = sql_field_val('id_reason', 'int');

        //чтобы можно было убрать лишнюю льготу
        //разрешаем, если уменьшается конечная дата
        $QE = "select lg.id from lgm_abon_tbl as lg where lg.id = $p_id and 
         lg.ident_cod_l = $p_ident_cod_l and 
         (lg.dt_end is null and $p_dt_end is not null or lg.dt_end>$p_dt_end );   ";

        $result = pg_query($Link, $QE);
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
            return;
        }
        $rows = pg_num_rows($result);
        if ($rows==0)
        {

            $QE = "select c.id, (c.book||'/'||c.code)::varchar as bookcode from
            clm_paccnt_tbl as c
            join lgm_abon_tbl as lg on (lg.id_paccnt = c.id and lg.id <> $p_id) 
            where ident_cod_l = $p_ident_cod_l and c.archive =0 
            and (
            --($p_dt_start < coalesce(lg.dt_end,'2030-01-01'::date))
            -- or 
            tintervalov(tinterval(GREATEST($start_mmgg::date,lg.dt_start)::timestamp::abstime,coalesce(lg.dt_end,'2030-01-01'::date)::timestamp::abstime),
                     tinterval(GREATEST($start_mmgg::date,($p_dt_start::date+'1 days'::interval)::date )::timestamp::abstime,coalesce($p_dt_end,'2030-01-01'::date)::timestamp::abstime)) )";
        
            $result = pg_query($Link, $QE);
            if (!($result)) {
                echo_result(2, pg_last_error($Link));
                return;
            }
            $rows = pg_num_rows($result);
            if ($rows>0)
            {
                $row = pg_fetch_array($result);
                
                echo_result(3, pg_last_error($Link), $row['id'], $row['bookcode']);
                return;
            }               
        }        
        
        
        $result = pg_query($Link, "begin");
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
            return;
        }

        $QE = "select lgt_change_fun(2,$p_id_paccnt,$p_id,$p_change_date,$p_id_usr,$p_id_reason,1)";

        $result = pg_query($Link, $QE);
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
            pg_query($Link, "rollback");
            return;
        }


        $QE = "update lgm_abon_tbl set 
        id_grp_lgt = $p_id_grp_lgt, id_calc = $p_id_calc, prior_lgt=$p_prior_lgt, fio_lgt = $p_fio_lgt, id_doc = $p_id_doc,
        s_doc=$p_s_doc, n_doc=$p_n_doc, dt_doc=$p_dt_doc, dt_doc_end = $p_dt_doc_end,
        ident_cod_l=$p_ident_cod_l, dt_reg = $p_dt_reg,
        dt_start=$p_dt_start, dt_end=$p_dt_end, family_cnt = $p_family_cnt, note = $p_note, closed = $p_closed
        where id = $p_id ;";

        $res_e = pg_query($Link, $QE);
        
        if ($res_e) {
            pg_query($Link, "commit");
            echo_result(1, 'Data updated');
        } else {
            echo_result(2, pg_last_error($Link));
            pg_query($Link, "rollback");            
        }
    }
    
//------------------------------------------------------
    if ($oper == "del") {

        $result = pg_query($Link, "begin");
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
            return;
        }
        $p_id_reason = sql_field_val('id_reason', 'int');
        $QE = "select lgt_change_fun(3,$p_id_paccnt,$p_id,$p_change_date,$p_id_usr,$p_id_reason,1)";

        $result = pg_query($Link, $QE);
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
            pg_query($Link, "rollback");
            return;
        }        
        
        $QE = "Delete from lgm_abon_tbl where id= $p_id;";        
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