<?php

header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();
error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];

$nmp1 = "АЕ-Фідери";
$cp1 = "Фідери";
$gn1 = "dov_fider";
$gn1_table = $gn1."_table";
$gn1_pager = $gn1."_tablePager";


start_mpage($nmp1); //заголовок
head_addrpage();  // хедер, подключение библиотек

print('<link rel="stylesheet" type="text/css" href="css/ded_styles.css" /> ');
print('<link rel="stylesheet" type="text/css" href="css/layout-default-latest.css" /> ');
print('<script type="text/javascript" src="js/jquery.layout.min-latest.js"></SCRIPT>');
    
print('<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script> ');
print('<script type="text/javascript" src="dov_fider.js"></script> ');

$lvolt=DbTableSelList($Link,'eqk_voltage_tbl','id','voltage');
$r_edit = CheckLevel($Link,'довідник-фідери',$session_user);

echo <<<SCRYPT
<script type="text/javascript">
var lvolt = $lvolt;
var r_edit = $r_edit;             
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