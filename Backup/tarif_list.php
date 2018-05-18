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
   $nmp1 = "АЕ-Тарифи вибір";
}
else
{
   $selmode = 0; 
   $nmp1 = "АЕ-Тарифи";
    
}    

$cp1 = "тарифи";
$gn1 = "_list";
$gn1_table = $gn1."_table";
$gn1_pager = $gn1."_tablePager";

start_mpage($nmp1); //заголовок
head_addrpage();  // хедер, подключение библиотек

print('<link rel="stylesheet" type="text/css" href="css/ded_styles.css" /> ');
print('<link rel="stylesheet" type="text/css" href="css/layout-default-latest.css" /> ');
print('<script type="text/javascript" src="js/jquery.layout.min-latest.js"></SCRIPT>');
print('<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script> ');
print('<script type="text/javascript" src="js/jquery.ui.datepicker-uk.js"></script> ');
print('<script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script> ');


print('<script type="text/javascript" src="tarif_list.js?version='.$app_version.'"></script> ');

$lgt_grp= DbTableSelList($Link,'lgi_tar_grp_tbl','id','name');
//$lpersons=DbTableSelList($Link,'persons','id_person','soname');
$r_edit = CheckLevel($Link,'довідник-тарифи',$session_user);

echo <<<SCRYPT
<script type="text/javascript">
//var lpersons = $lpersons;
var selmode = $selmode;    
var lgt_grp = $lgt_grp;        
var r_edit = $r_edit;             
var tar_hidden;    
if (selmode==1)
{
   tar_hidden = true;
}
else
{
   tar_hidden = false;
}
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

    <DIV id="pmain_center" style="padding:2px; margin:2px;">    
        <div id="pTarif_grp_table">    
            <table id="tarif_grp_table" > </table>
            <div   id="tarif_grp_tablePager" ></div>
        </div>    

        <div id="pTarif_table">    
            <table id="tarif_table" > </table>
            <div   id="tarif_tablePager" ></div>
        </div>    

        <div id="pTarif_val_table">    
            <table id="tarif_val_table" > </table>
            <div   id="tarif_val_tablePager" ></div>
        </div>    
    </div>        
<?php

print('<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>');


end_mpage();
?>