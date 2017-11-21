<?php

header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();
error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];
/*
if (isset($_SESSION['id_sess'])) {
  $ids = $_SESSION['id_sess'];

  $res_par = sel_par($ids);
  $row_par = pg_fetch_array($res_par);
  $cons = $row_par['con_str1'];
  $Q = $row_par['qu'];
  $Link = pg_connect($cons);
}
*/
//$lnk_sys = log_s_pgsql("login");
//$lnk1 = $lnk_sys['lnks'];

//$rr = pg_query($Link, $Q);
/*
$idgrt = 0;
$idtar = 0;
$idgrpl = 0;
$idbperc = 0;

$QS = "update dbusr_var set idgrt=" . $idgrt . ",idtar=" . $idtar . ",idgrpl=" . $idgrpl .
        ",idbperc=" . $idbperc . " where id_sess=" . $ids;
$res_qs = pg_query($lnk1, $QS);
*/

$nmp1 = "АЕ-Довідники - типи проводів для повітряних ліній";
$cp1 = "Провода";
$gn1 = "dov_corde";
$gn1_table = $gn1."_table";
$gn1_pager = $gn1."_tablePager";

start_mpage($nmp1); //заголовок
head_addrpage();  // хедер, подключение библиотек

print('<link rel="stylesheet" type="text/css" href="css/ded_styles.css" /> ');
print('<link rel="stylesheet" type="text/css" href="css/layout-default-latest.css" /> ');
print('<script type="text/javascript" src="js/jquery.layout.min-latest.js"></SCRIPT>');
    
print('<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script> ');
print('<script type="text/javascript" src="dov_corde.js"></script> ');

$lmateral=DbTableSelList($Link,'eqk_materals_tbl','id','name');

echo <<<SCRYPT
<script type="text/javascript">
var lmateral = $lmateral;
    
</script>
SCRYPT;

?>

</head>
<body >

<DIV id="pmain_header"> 
    <?php main_menu(); ?>
</DIV> 
    
<DIV id="pmain_footer">
   <a href="javascript:void(0)" id="debug_ls1">show debug window</a> 
   <a href="javascript:void(0)" id="debug_ls2">hide debug window</a> 
   <a href="javascript:void(0)" id="debug_ls3">clear debug window</a>
</DIV>

<div id="grid_dform">    
<table id="<?php echo $gn1_table ?>" > </table>
<div id="<?php echo $gn1_pager ?>" ></div>
</div>    
    
<?php

print('<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>');


end_mpage();
?>