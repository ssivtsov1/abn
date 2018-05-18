<?php

header('Content-type: text/html; charset=utf-8');

require_once 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();
//error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];

/*
if ((isset($_POST['id_paccnt']))&&($_POST['id_paccnt']!=''))    
{
  $id_paccnt = $_POST['id_paccnt']; 
  $paccnt_info = $_POST['paccnt_info']; 
}
  */
$nm1 = "АЕ-Помилкові показники";

start_mpage($nm1);
head_addrpage();

$Query=" select to_char(fun_mmgg(), 'DD.MM.YYYY') as mmgg;";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$mmgg = $row['mmgg'];


print('<link rel="stylesheet" type="text/css" href="css/ded_styles.css" /> ');
print('<link rel="stylesheet" type="text/css" href="css/layout-default-latest.css" /> ');
    
print('<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script> ');
print('<script type="text/javascript" src="js/jquery.form.js"></script>');
print('<script type="text/javascript" src="js/jquery.validate.min.js?version='.$app_version.'" ></script>');
print('<script type="text/javascript" src="js/jquery.ui.datepicker-uk.js"></script> ');
print('<script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script> ');
print('<script type="text/javascript" src="js/jquery.layout.min-latest.js"></SCRIPT>');

print('<script type="text/javascript" src="abon_en_badindic_list.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="check_session.js?version='.$app_version.'"></script> ');

$lzones=DbTableSelList($Link,'eqk_zone_tbl','id','nm');
$lindicoper =DbTableSelList($Link,'cli_indic_type_tbl','id','name');

//$lactions=DbTableSelList($Link,'cli_badindic_action_tbl','id','name');
//$lactionslist=DbTableSelect($Link,'cli_badindic_action_tbl','id','name');

?>
<style type="text/css"> 
#pmain_content {padding:3}

.ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default {  color: #111111; }

</style>   


<script type="text/javascript">

var mmgg = <?php echo "'$mmgg'" ?>;

var lzones = <?php echo "$lzones" ?>;
var lindicoper = <?php echo "$lindicoper" ?>;

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
            <label>Період
                <input name="mmgg" type="text" size="15" class="dtpicker" id ="fmmgg" value= "" />
            </label>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <button type="button" class ="btn btnSmall" id="bt_sel">Вибрати</button>		    
        </div>
        
        <div id="pbadindic_list" style="margin:1px;padding:1px;" > 
            <table id="badindic_table" style="margin:1px;padding:1px;"></table>
            <div id="badindic_tablePager"></div>
        </div>

    </DIV>

    
<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>

<div id="dialog-confirm" title="Удалить?" style="display:none;">
    <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
    <p id="dialog-text"> Продолжить? </p></p>
</div>

<form id="fpaccnt_sel_params" name="paccnt_sel_params" target="paccnt_win" method="post" action="abon_en_main.php">
   <input type="hidden" name="select_mode" value="1" />
</form>

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

end_mpage();
?>


