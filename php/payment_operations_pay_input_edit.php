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
    

    $p_id_headpay = sql_field_val('id_headpay', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_code = sql_field_val('code', 'string');
    $p_summ = sql_field_val('summ', 'numeric');
    $p_mmgg_pay = sql_field_val('mmgg_pay', 'date');
    $p_mmgg_pay_txt = $_POST['mmgg_pay'];
    $p_barcode = sql_field_val('barcode_data', 'str');
    
    $p_total_summ = sql_field_val('total_summ', 'numeric');
    $p_total_cnt = sql_field_val('total_cnt', 'int');
    
    $pid_paccnt = 'null';
//------------------------------------------------------
if ($oper == "add") {
        
    if ($p_barcode!='null') 
    {
       list($pid_paccnt, $month, $year, $summ , $flag) = split('[;]', $p_barcode); 
       
       //if ($flag=='+')
       //{
           $p_mmgg_pay = "'$year-$month-01'";
           $p_mmgg_pay_txt = "01.$month.$year";
           
           $QE = "Select * from acm_pay_tbl      
             where id_headpay = $p_id_headpay and id_paccnt = $pid_paccnt and 
             value =  $summ and mmgg_pay = $p_mmgg_pay ;";

           $res_e = pg_query($Link, $QE);
           if ($res_e) {
               $rows_count = pg_num_rows($res_e);
               if ($rows_count>0)
               {
                   echo_result(-3, 'Dublicate!');
                   return;
               }
           }
           
       //}
       //else
       //{
       //  echo_result(2, 'Помилка зчитування штрих-коду!' );
       //  return;           
       //}
        
    }
    else
    {
        $summ = $p_summ;
    }
        

    $result = pg_query($Link, "select crt_ttbl();");
    if (!($result)) {
        echo_result(2, pg_last_error($Link) );
    //    pg_query($Link, "rollback");
        return;
    }
        
    $result = pg_query($Link, "update syi_sysvars_tmp set value_ident=1 where id=50;");
    if (!($result)) {
        echo_result(2, pg_last_error($Link) );
    //    pg_query($Link, "rollback");
        return;
    }
        
     $QE = "SELECT coalesce(MAX(reg_num),0)+1 as next_num FROM acm_pay_tbl WHERE id_headpay=$p_id_headpay";
     $result=pg_query($Link,$QE);
     if (!($result)) 
     {
        echo_result(2,pg_last_error($Link));
       // pg_query($Link,"rollback");   
        return;
     }    

     $row = pg_fetch_array($result);     
     $reg_num = $row['next_num'];
     

     $QE = "SELECT * FROM acm_headpay_tbl WHERE id=$p_id_headpay";
     $result=pg_query($Link,$QE);
     if (!($result)) 
     {
        echo_result(2,pg_last_error($Link));
        return;
     }    
     $row_head = pg_fetch_array($result);  
     
     $reg_date = "'".$row_head['reg_date']."'";
     
     
     $QE = "SELECT acc.id, acc.book, acc.code, 
      (adr.adr||' '||
        (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')||
			coalesce((acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash||' ',''))::varchar
        )::varchar as addr,
        (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon
     FROM clm_paccnt_tbl as acc
        join clm_abon_tbl as c on (c.id = acc.id_abon) 
        left join adt_addr_tbl as adr on (adr.id = (acc.addr).id_class)
     WHERE (acc.book=$p_book AND acc.code=$p_code) or (acc.id = $pid_paccnt ) LIMIT 1;";
     
     $result=pg_query($Link,$QE);
     if (!($result)) 
     {
        echo_result(2,pg_last_error($Link));
        return;
     }    

     if( pg_num_rows($result)==0) 
     {
        echo_result(3,'Абонент з вказаною книгою та рахунком не знайдений!');
        return;
     }    
     
     $row_paccnt = pg_fetch_array($result);     
     $id_paccnt = $row_paccnt['id'];
     $abon = $row_paccnt['abon'];
     $addr = $row_paccnt['addr']; 
     $book = $row_paccnt['book']; 
     $code = $row_paccnt['code']; 
     
     $sum_tax = round($summ/6,2);
      
      $QE = "INSERT INTO acm_pay_tbl(id_doc,id_headpay,id_person, idk_doc, id_pref, reg_num, 
            reg_date, id_paccnt, value, value_tax, pay_date, mmgg_pay )
        VALUES (DEFAULT, $p_id_headpay, $session_user, 100, 10, $reg_num, 
            $reg_date, $id_paccnt, $summ, $sum_tax, $reg_date, $p_mmgg_pay ) returning id_doc;";

      $res_e = pg_query($Link, $QE);
      if ($res_e) {

            $row = pg_fetch_array($res_e);   
            $id = $row['id_doc'];
            
            $book_txt = trim($book);
            $code_txt = trim($code);
            
            $code = $book_txt.'/'.$code_txt;
                    
            $arr = array('errcode' => -1, 'errstr' => '', 'id' => $id,'abon' =>$abon,
                'addr'=>$addr, 'code' => $code, 
                'summ' => str_replace(',','',number_format($summ,2)), 
                'sum_tax'=> str_replace(',','',number_format($sum_tax,2)), 
                'nn'=> $reg_num,
                'mmgg_pay'=> $p_mmgg_pay_txt
                );
            echo json_encode($arr);
          
      } else {
        echo_result(2, pg_last_error($Link).$QE);
      }
    }
//------------------------------------------------------
if ($oper == "save") {
  
    
    $result = pg_query($Link, "select crt_ttbl();");
    if (!($result)) {
        echo_result(2, pg_last_error($Link) );
    //    pg_query($Link, "rollback");
        return;
    }
  
    $result = pg_query($Link, "update acm_headpay_tbl set count_pay = $p_total_cnt , sum_pay = $p_total_summ
            where coalesce(count_pay,0)=0 and coalesce(sum_pay, 0) = 0 
            and id = $p_id_headpay; ");
    if (!($result)) {
        echo_result(2, pg_last_error($Link) );
        return;
    }

    
    $result = pg_query($Link, "select calc_saldo();");
    if (!($result)) {
        echo_result(2, pg_last_error($Link) );
        return;
    }
 
        
    $result = pg_query($Link, "select all_repayment();"); 
    if (!($result)) {
        echo_result(2, pg_last_error($Link) );
        return;
    }

    $result = pg_query($Link, "select warning_off_fun();");
    if (!($result)) {
        echo_result(2, pg_last_error($Link) );
        return;
    }
    
    echo_result(-2, 'pay accept' );    
        
    }
    
    
} catch (Exception $e) {

  echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>