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

$id_paccnt = $_POST['id_paccnt']; 


if ($id_paccnt=='')
{
    echo echo_result(2,'',0 );
    return;
}

$SQL = "select  t.name as type,
' №'||s.num_sp||' від '||to_char(s.dt_sp, 'DD.MM.YYYY') as sprav,
' на запит №'||s.num_input||' від '||to_char(s.dt_input, 'DD.MM.YYYY') as input,
 to_char(s.dt, 'DD.MM.YYYY') as dt, 
  p.represent_name as person 
  from rep_spravka_tbl as s 
  left join rep_sprav_types_tbl as t on (t.id = s.doc_type)
  left join prs_persons as p on (p.id = s.id_person)
  where s.id_paccnt = $id_paccnt order by s.dt desc limit 1; ";

//left join adt_addr_tbl as adr on (adr.id = (acc.addr).id_class)

$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link) );
if (!$result) { echo echo_result(2,'Абонент не найден!',0 );}
else{

    $rows = pg_num_rows($result);
    
    if ($rows>0)
    {
        $row = pg_fetch_array($result);

        echo echo_result(1,"остання довідка {$row['type']} {$row['sprav']} {$row['input']} зформована  {$row['dt']}");

        
    }
    else
    {
        echo echo_result(2,'',0 );        
    }
}

?>
