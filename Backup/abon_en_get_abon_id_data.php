<?php
//header('Content-type: text/html; charset=utf-8');
header("Content-type: application/json;charset=utf-8");

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();

error_reporting(1);

$Link = get_database_link($_SESSION,1);
$session_user = $_SESSION['ses_usr_id'];
session_write_close();

$code = $_POST['code']; 
$book = $_POST['book'];  

if (isset($_POST['param']))
    $param = $_POST['param']; 
else
    $param =1;


if ($book=='' || $code == '')
{
    echo echo_result(2,'Абонент не найден!',0 );
    return;
}

$SQL = "select acc.id, 
(c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
 (adr.street||' '||
   (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')|| 
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash,''))::varchar
)::varchar as addr
from clm_paccnt_tbl as acc 
join clm_abon_tbl as c on (c.id = acc.id_abon) 
left join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
where acc.code = '$code' and acc.book = '$book' and acc.archive = 0; ";

//left join adt_addr_tbl as adr on (adr.id = (acc.addr).id_class)

$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link) );
if (!$result) { echo echo_result(2,'Абонент не найден!',0 );}
else{

    $rows = pg_num_rows($result);
    
    if ($rows>0)
    {
        $row = pg_fetch_array($result);
        if ($param==1)
           echo echo_result(1,$row['abon'],$row['id'], $row['addr']);
        if ($param==2)
           echo echo_result(1,$row['addr'],$row['id'] );
        
    }
    else
    {
        echo echo_result(2,'Абонент не найден!',0 );        
    }
}

?>
