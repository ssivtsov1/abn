<?php

header('Content-type: text/html; charset=utf-8');

include_once 'abon_en_func.php';
include_once 'abon_ded_func.php';
//------------------------------------------------------------------------------------------
class XMLSerializer {

    
    public static function generateValidXmlFromObj(stdClass $obj, $node_block='nodes', $node_name='node') {
        $arr = get_object_vars($obj);
        return self::generateValidXmlFromArray($arr, $node_block, $node_name);
    }

    public static function generateValidXmlFromArray($array, $node_block='nodes', $node_name='node') {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>';

        $xml .= '<' . $node_block . '>';
        $xml .= self::generateXmlFromArray($array, $node_name);
        $xml .= '</' . $node_block . '>';

        return $xml;
    }

    private static function generateXmlFromArray($array, $node_name) {
        $xml = '';

        if (is_array($array) || is_object($array)) {
            foreach ($array as $key=>$value) {
                if (is_numeric($key)) {
                    $key = $node_name;
                }

                $xml .= '<' . $key . '>' . self::generateXmlFromArray($value, $node_name) . '</' . $key . '>';
            }
        } else {
            $xml = htmlspecialchars($array, ENT_QUOTES);
        }

        return $xml;
    }

}
//--------------------------------------------------------------------------------------

session_name("session_kaa");
session_start();

error_reporting(0); 

$Link = get_database_link($_SESSION,2);
$session_user = $_SESSION['ses_usr_id'];

session_write_close();

$fildsArray = array();
//$fildsArray['id'] = array('f_name' => 'id', 'f_type' => 'integer');
$fildsArray['book'] = array('f_name' => 'book', 'f_type' => 'char');
$fildsArray['code'] = array('f_name' => 'code', 'f_type' => 'char');
$fildsArray['abon'] = array('f_name' => 'abon', 'f_type' => 'character varying');
$fildsArray['addr'] = array('f_name' => 'addr', 'f_type' => 'character varying');
$fildsArray['id_paccnt'] = array('f_name' => 'id_paccnt', 'f_type' => 'integer');
$fildsArray['index_accnt'] = array('f_name' => 'index_accnt', 'f_type' => 'integer');

$fildsArray['dat_ind'] = array('f_name' => 'dat_ind', 'f_type' => 'date');
$fildsArray['num_eqp'] = array('f_name' => 'num_eqp', 'f_type' => 'character varying');

$fildsArray['id_zone'] = array('f_name' => 'id_zone', 'f_type' => 'integer');
$fildsArray['id_operation'] = array('f_name' => 'id_operation', 'f_type' => 'integer');
$fildsArray['value'] = array('f_name' => 'value', 'f_type' => 'integer');
$fildsArray['coef_comp'] = array('f_name' => 'coef_comp', 'f_type' => 'integer');
$fildsArray['carry'] = array('f_name' => 'carry', 'f_type' => 'integer');
$fildsArray['value_diff'] = array('f_name' => 'value_diff', 'f_type' => 'integer');
$fildsArray['mmgg'] = array('f_name' => 'mmgg', 'f_type' => 'date');


try {

  if (isset($_POST['oper'])) {

    $oper = $_POST['oper'];
    
    $p_mmgg = sql_field_val('mmgg', 'mmgg');
    //$mmgg=$_POST['mmgg']; 
    //list($day, $month, $year) = split('[/.-]', $mmgg);
    
    $p_id_usr = $session_user;
    
    
//------------------------------------------------------
    if ($oper == "xml") {

/*        
     if ($region=='slav')
       $result = pg_query($Link, "select seb_lgt_f2_slav_fun($p_mmgg,$id_region);");         
     else    
       $result = pg_query($Link, "select seb_lgt_f2_fun($p_mmgg,$id_region);");
     
     if (!($result)) {
        echo_result(2, pg_last_error($Link) );
        return;
     }
 
 */        
        
        
  $QE = "select  c.book, c.code, c.index_accnt, i.*,
    (ab.last_name||' '||coalesce(ab.name,'')||' '||coalesce(ab.patron_name,''))::varchar as abon,
    adr.town||' '||(adr.street||' '||
        (coalesce('буд.'||(c.addr).house||'','')||
		coalesce('/'||(c.addr).slash||' ','')|| 
			coalesce(' корп.'||(c.addr).korp||'','')||
				coalesce(', кв. '||(c.addr).flat,'')||
					coalesce('/'||(c.addr).f_slash,''))::varchar
    )::varchar as addr
        
  from 
  clm_paccnt_tbl as c
  join clm_paccnt_export_tbl as x on (x.id_paccnt = c.id)
  join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
  join acm_indication_tbl as i on (i.id_paccnt = c.id)
  join clm_abon_tbl as ab on (ab.id = c.id_abon) 
  where i.mmgg = $p_mmgg
  order by c.book, c.code, i.id; "; 
    
  $result = pg_query($Link, $QE);
      
   if ($result) 
   {
          
        $i = 0; 
        while($row = pg_fetch_array($result)) {

            foreach ($fildsArray as $fild) {
                $data[$i][$fild['f_name']] = $row[$fild['f_name']];
            }

        $i++; 
        }
        $count=$i;
        $data['records'] = $count;
   }  
   else
   {
     echo_result(1,'Ошибка SQL');
     return;
   }
     //echo  
     //$xml_str =  xml_encode( $data );
     //$xml_str = json_encode( $data );
     
   
    $xml_generater = new XMLSerializer; 
    $std_class = json_decode(json_encode($data));
    $xml_str = $xml_generater->generateValidXmlFromObj($std_class);   
   
     $now_time = date('Y-m-d-H-i-s');
    
     $dest_name ="../tmp/exchange{$now_time}.xml";
     $myfile = fopen($dest_name, "w") or die("Unable to create xml file!");
     fwrite($myfile, $xml_str);
     fclose($myfile);        
     
     echo_result(-1,$dest_name);
     return;

    }
//------------------------------------------------------
  
  }
} catch (Exception $e) {

  echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>


