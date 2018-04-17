<?php
header('Content-type: text/html; charset=utf-8');

require_once 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();
//error_reporting(0);
$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];

//$cp1 = iconv("windows-1251", "utf-8", "Абоненти");
$cp1 = "Відомість по кількості лічильників";
$gn1 = "m_grid";
$fn1 = "mgrid";

if ((isset($_POST['select_mode']))&&($_POST['select_mode']=='1'))
{
   $selmode = 1; 
   $nmp1 = "АЕ-Особові рахунки вибір";
}
else
{
   $selmode = 0; 
   $nmp1 = "АЕ-Особові рахунки";
    
}    

//start_mpage_cache($nmp1);
start_mpage($nmp1);
head_addrpage();
$flag_cek = is_cek($Link);  //принадлежность РЭСа к ЦЭК

print('<link rel="stylesheet" type="text/css" href="css/ded_styles.css" /> ');
print('<link rel="stylesheet" type="text/css" href="css/layout-default-latest.css" /> ');
    
print('<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script> ');
print('<script type="text/javascript" src="js/jquery.form.js"></script>');
print('<script type="text/javascript" src="js/jquery.validate.min.js?version='.$app_version.'" ></script>');
print('<script type="text/javascript" src="js/jquery.ui.datepicker-uk.js"></script> ');
print('<script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script> ');
print('<script type="text/javascript" src="js/jquery.layout.min-latest.js"></SCRIPT>');
print('<script type="text/javascript" src="js/jquery.hotkeys.js"></SCRIPT>');

print('<script type="text/javascript" src="controlers_counters.js?version='.$app_version.'"></script> ');

print('<script type="text/javascript" src="check_session.js?version='.$app_version.'"></script> ');

$lactions=DbTableSelList($Link,'cli_switch_action_tbl','id','name');

$r_newabon = CheckLevel($Link,'картка-загальне',$session_user);
$town_hidden=CheckTownInAddrHidden($Link);

?>
<style type="text/css"> 
#pActionBar { position:relative; height:28px; border-width:1px; width:"100%"; padding:4px; margin: 1px;}    
#pmain_content {padding:3px}
#pwork_grid {padding:3px}

.ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default {  color: #111111; }

</style>   

<script type="text/javascript">
    var selmode = <?php echo "$selmode" ?>;        
    var lactions = <?php echo "$lactions" ?>;        
    var r_newabon = <?php echo "$r_newabon" ?>; 
    var town_hidden = <?php echo "$town_hidden" ?>;  
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


<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>

<DIV style="display:none;">
    <form id="fpaccnt_params" name="paccnt_params" method="post" action="abon_en_paccnt.php">
        <input type="text" name="mode" id="pmode" value="1" />
        <input type="text" name="id_paccnt" id="pid_paccnt" value="0" />         
        <input type="text" name="paccnt_info" id="ppaccnt_info" value="" />    
        <input type="text" name="paccnt_book" id="ppaccnt_book" value="" />                  
        <input type="text" name="paccnt_code" id="ppaccnt_code" value="" />                  
        <input type="text" name="paccnt_name" id="ppaccnt_name" value="" />                  
      
    </form>
</DIV>


<?php


//// print("SQL - ".$S1);

end_mpage();
?>