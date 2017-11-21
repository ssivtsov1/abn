<?php

header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();
error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];

if ((isset($_POST['select_mode']))&&($_POST['select_mode']=='1'))
{
   $selmode = 1; 
   $nmp1 = "АЕ-Довідники - типи лічильників вибір";
}
else
{
   $selmode = 0; 
   $nmp1 = "АЕ-Довідники - типи лічильників";
    
}    

$cp1 = "Лічильники";
$gn1 = "dov_meters";
$fn1 = "dovmeters";

$gn1_table = $gn1."_table";
$gn1_pager = $gn1."_tablePager";


start_mpage($nmp1); //заголовок
head_addrpage();  // хедер, подключение библиотек

print('<link rel="stylesheet" type="text/css" href="css/ded_styles.css" /> ');
print('<link rel="stylesheet" type="text/css" href="css/layout-default-latest.css" /> ');
print('<script type="text/javascript" src="js/jquery.layout.min-latest.js"></SCRIPT>');
    
print('<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script> ');
print('<script type="text/javascript" src="dov_meters.js"></script> ');

$lmeters=DbTableSelList($Link,'eqk_meter_tbl','id','name');
$lphase=DbTableSelList($Link,'eqk_phase_tbl','id','name');

$r_edit = CheckLevel($Link,'довідник-лічильники',$session_user);
?>

<script type="text/javascript">
    
var lkind_meter = <?php echo $lmeters ?>;
var lphase = <?php echo $lphase ?>;
var selmode = <?php echo $selmode ?>;
var r_edit = <?php echo $r_edit ?>;
 
</script>


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

print('<div id="message_zone" style ="padding: 5px;color: blue;" > ------------------- </div>');

print('<a href="javascript:void(0)" id="ls1">show debug window</a> <br> ');
print('<a href="javascript:void(0)" id="ls2">hide debug window</a>');

print("<br><a href=\"abon_en_dov.php\">Довідники</a>\n");

end_mpage();
?>