<?php

header('Content-type: text/html; charset=utf-8');

require_once 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();
//error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];

$nm1 = "АЕ-Додаткові пільги";

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
print('<script type="text/javascript" src="abon_en_lgt_dop.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="js/spin.min.js"></script> ');
print('<script type="text/javascript" src="abon_en_main_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="check_session.js?version='.$app_version.'"></script> ');

$lgtlist =DbTableSelList($Link,"(select gr.id, coalesce(gr.ident,'')||'  '||replace(gr.name,'''','') as name from lgi_group_tbl as gr 
    join lgi_calc_header_tbl as h  on (h.id = gr.id_calc)
    order by ('0'||gr.ident)::int) as sss",'id','name');

$r_edit = CheckLevel($Link,'додаткові пільги',$session_user);
$r_bill = CheckLevel($Link,'сальдо-рахунки',$session_user);
$town_hidden=CheckTownInAddrHidden($Link);

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
    var lgtlist = <?php echo "$lgtlist" ?>;
    var r_edit = <?php echo "$r_edit" ?>;
    var r_bill = <?php echo "$r_bill" ?>;
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
   <a href="javascript:void(0)" id="show_peoples">Исполнители</a>                
</DIV>  

    <DIV id="pmain_content">

        <div class="ui-corner-all ui-state-default" id="pActionBar">
            <label>Період
                <input name="mmgg" type="text" size="15" class="dtpicker" id ="fmmgg" value= "" />
            </label>
            <button type="button" class ="btn btnSmall" id="bt_sel">Вибрати</button>		    
            
           <!-- <button type="button" class ="btn btnSmall" id="bt_calc">Заповнити</button> -->
        </div>
        
        
       <div id="tab_lgt" > 
           <table id="lgt_table" style="margin:1px;"></table>
           <div id="lgt_tablePager"></div>
       </div>

        <div id="pBottom" style="padding:2px; margin:3px;">    
                <button name="OkButton" type="button" class ="btn" id="bt_save" value="add" >Записати</button>                     
                <button name="CancelButton" type="button" class ="btn" id="bt_cancel" value="reset" >Відмінити</button>         
        </div>    
        
    </DIV>


<div id="grid_selabon" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 100000;" >
             <table id="abon_en_sel_table" style="margin:1px;"></table>
             <div id="abon_en_sel_tablePager"></div>
</div>
    
    
<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>


<div id="dialog-confirm" title="Видалити запис?" style="display:none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text">Видалити запис про додаткову пільгу? </p></p>
</div>

    <form id="fpaccnt_sel_params" name="paccnt_sel_params" target="paccnt_win" method="post" action="abon_en_main.php">
        <input type="hidden" name="select_mode" value="1" />
    </form>

<form id="freps_params" name="reps_params" method="post" action="rep_main_build_html.php" target="_blank">
        
 <input type="hidden" name="template_name" id="ftemplate_name" value="doplgt_list" />      
 <input type="hidden" name="oper" id="foper" value="doplgt_list" />      
 <input type="hidden" name="period_str" id = "fperiod_str" size="30" value= ""  />
 
 <input type="hidden" name="dt_b" size="10"  id ="fdt_b" value= "" />
 <input type="hidden" name="to_xls" id="fxls" value="0" />  
 
 <input type="hidden" name="grid_params" id="fgrid_params" value="" />  
</form> 

<?php 

end_mpage();
?>


