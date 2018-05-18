<?php

header('Content-type: text/html; charset=utf-8');

require_once 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();
//error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];

if ((isset($_POST['id_paccnt']))&&($_POST['id_paccnt']!=''))    
{
  $id_paccnt = $_POST['id_paccnt']; 
  $paccnt_info = htmlspecialchars($_POST['paccnt_info'],ENT_QUOTES); 
      
}
$nm1 = "АЕ-Планове споживання абонента $paccnt_info";

$Query=" select to_char(fun_mmgg(), 'DD.MM.YYYY') as mmgg;";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$mmgg = $row['mmgg'];

start_mpage($nm1);
head_addrpage();

print('<link rel="stylesheet" type="text/css" href="css/ded_styles.css" /> ');
print('<link rel="stylesheet" type="text/css" href="css/layout-default-latest.css" /> ');
    
print('<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script> ');
print('<script type="text/javascript" src="js/jquery.form.js"></script>');
print('<script type="text/javascript" src="js/jquery.validate.min.js?version='.$app_version.'" ></script>');
print('<script type="text/javascript" src="js/jquery.ui.datepicker-uk.js"></script> ');
print('<script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script> ');
print('<script type="text/javascript" src="js/jquery.layout.min-latest.js"></SCRIPT>');

print('<script type="text/javascript" src="abon_en_plandem.js?version='.$app_version.'"></script> ');

$lzones=DbTableSelList($Link,'eqk_zone_tbl','id','nm');
$lid_pref=DbTableSelList($Link,'aci_pref_tbl','id','name');

$r_edit = CheckLevel($Link,'планове споживання',$session_user);
?>
<style type="text/css"> 
#pmain_content {padding:3}

.ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default {  color: #111111; }

</style>   


<script type="text/javascript">

var id_paccnt = <?php echo "$id_paccnt" ?>;
var lzones = <?php echo "$lzones" ?>; 
var lid_pref = <?php echo "$lid_pref" ?>; 
var paccnt_info = <?php echo "'$paccnt_info'" ?>;  
var mmgg = <?php echo "'$mmgg'" ?>; 
var r_edit = <?php echo "$r_edit" ?>;
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

    <DIV id="pmain_content" style="margin:1px;padding:1px;">
        
        <div class="ui-corner-all ui-state-default" id="pActionBar"> 
            <label>Період(рік)
                <input name="mmgg" type="text" size="15" class="dtpicker" id ="fmmgg" value= "" />
            </label>
            &nbsp;&nbsp;
            <button type="button" class ="btn btnSmall" id="bt_sel">Вибрати</button>
        </div>
        

        <div id="pplandem_list" style="margin:1px;padding:1px;" > 
            <table id="plandem_table" style="margin:1px;padding:1px;"></table>
            <div id="plandem_tablePager"></div>
        </div>

    </DIV>

<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>


<?php

end_mpage();
?>


