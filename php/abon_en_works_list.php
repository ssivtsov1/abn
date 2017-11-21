<?php

header('Content-type: text/html; charset=utf-8');

require_once 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();
//error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];

$nm1 = "АЕ- Роботи";

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
print('<script type="text/javascript" src="js/date-uk-UA.js"></script> ');
print('<script type="text/javascript" src="abon_en_works_list.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="check_session.js?version='.$app_version.'"></script> ');


$lworktypes =DbTableSelList($Link,'cli_works_tbl','id','name');
$lworkmetstatus =DbTableSelList($Link,'cli_metoper_tbl','id','name');
$lzones=DbTableSelList($Link,'eqk_zone_tbl','id','nm');

?>
<style type="text/css"> 
#pmain_content {padding:3}

.tabPanel {padding:1px; margin:1px; }
.ui-tabs .ui-tabs-panel {padding:1px;}

.ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default {  color: #111111; }
#pActionBar { position:relative; height:23px; border-width:1px; width:"100%"; padding:4px; margin: 1px;}    
</style>   


<script type="text/javascript">

    var mmgg = <?php echo "'$mmgg'" ?>;
    var lworktypes = <?php echo "$lworktypes" ?>; 
    var lworkmetstatus = <?php echo "$lworkmetstatus" ?>; 
    var lzones = <?php echo "$lzones" ?>;  
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
   <a href="javascript:void(0)" id="show_peoples">Исполнители</a>
</DIV>  
 
    <DIV id="pmain_content">
        
        <div class="ui-corner-all ui-state-default" id="pActionBar">
            <label>Період
                <input name="mmgg" type="text" size="15" class="dtpicker" id ="fmmgg" value= "" />
            </label>
            <button type="button" class ="btn btnSmall" id="bt_sel">Вибрати</button>		    
        </div>
           
      
        <div id="paccnt_works_list" style="padding:2px; margin:3px;"> 
            <table id="paccnt_works_table" style="margin:1px; "></table> 
            <div id="paccnt_works_tablePager"></div>
        </div>
        <div id="paccnt_works_indic_list" style="padding:2px; margin:3px;"> 
            <table id="paccnt_works_indic_table" style="margin:1px;"></table>
            <div id="paccnt_works_indic_tablePager"></div>
        </div>

        
                
    </DIV>

    <div id="dialog-confirm" title="Видалити запис?" style="display:none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text">Видалити запис про додаткову пільгу? </p></p>
    </div>

    
    <DIV style="display:none;">
      <form id="fpaccnt_params" name="paccnt_params" method="post" action="">
         <input type="text" name="mode" id="pmode" value="0" />
         <input type="text" name="id_meter" id="pid_meter" value="0" />         
         <input type="text" name="id_paccnt" id="pid_paccnt" value="0" />                  
         <input type="text" name="id_work" id="pid_work" value="0" />                  
         <input type="text" name="idk_work" id="pidk_work" value="0" />  
         <input type="text" name="paccnt_info" id="ppaccnt_info" value="" />                  
      </form>
    </DIV>
    
<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>

<form id="freps_params" name="reps_params" method="post" action="rep_main_build_html.php" target="_blank">
        
 <input type="hidden" name="template_name" id="ftemplate_name" value="works_list" />      
 <input type="hidden" name="oper" id="foper" value="works_list" />      
 <input type="hidden" name="period_str" id = "fperiod_str" size="30" value= ""  />
 
 <input type="hidden" name="dt_b" size="10"  id ="fdt_b" value= "" />
 <input type="hidden" name="to_xls" id="fxls" value="0" />  
 
 <input type="hidden" name="grid_params" id="fgrid_params" value="" />  
</form> 

<?php

end_mpage();
?>


