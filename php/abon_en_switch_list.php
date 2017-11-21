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
$nm1 = "АЕ-Журнал попередження та відключення";

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
print('<script type="text/javascript" src="abon_en_switch_list.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="abon_en_main_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="staff_list_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="ind_direct_edit.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="check_session.js?version='.$app_version.'"></script> ');

$lactions=DbTableSelList($Link,'cli_switch_action_tbl','id','name');
$lactionslist=DbTableSelect($Link,'cli_switch_action_tbl','id','name');

$lplace=DbTableSelList($Link,'cli_switch_place_tbl','id','name');
$lplacelist=DbTableSelect($Link,'cli_switch_place_tbl','id','name');

$staff_dep= DbTableSelList($Link,'prs_department','id','name');

$lzones=DbTableSelList($Link,'eqk_zone_tbl','id','nm');
$lindicoper =DbTableSelList($Link,'cli_indic_type_tbl','id','name');

$r_indic = CheckLevel($Link,'сальдо-показники',$session_user);
$r_edit = CheckLevel($Link,'відключення',$session_user);

$town_hidden=CheckTownInAddrHidden($Link);
?>
<style type="text/css">  
#pmain_content {padding:3}

.ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default {  color: #111111; }
#dialog-indications {padding:1px;}
</style>   


<script type="text/javascript">

var lactions = <?php echo "$lactions" ?>; 
var lplace = <?php echo "$lplace" ?>; 
var staff_dep = <?php echo "$staff_dep" ?>; 

var lzones = <?php echo "$lzones" ?>; 
var lindicoper = <?php echo "$lindicoper" ?>; 

var mmgg = <?php echo "'$mmgg'" ?>;
var r_edit = <?php echo "$r_edit" ?>;  
var r_indic = <?php echo "$r_indic" ?>;    

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

    <DIV id="pmain_content" style="margin:1px;padding:1px;">

        <div class="ui-corner-all ui-state-default" id="pActionBar">
            <label>Період
                <input name="mmgg" type="text" size="15" class="dtpicker" id ="fmmgg" value= "" />
            </label>
            &nbsp;&nbsp;
            <label>
                <input type="checkbox" name="mode_period" id="fperiod" value="1" /> Події за період
            </label>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <label>
                <input type="checkbox" name="mode_state" id="fstate" value="1" /> Останні події
            </label>                
            &nbsp;&nbsp;&nbsp;&nbsp;
            <button type="button" class ="btn btnSmall" id="bt_sel">Вибрати</button>		    
        </div>
        
        <div id="pswitch_list" style="margin:1px;padding:1px;" > 
            <table id="switch_table" style="margin:1px;padding:1px;"></table>
            <div id="switch_tablePager"></div>
        </div>

    </DIV>

        <div id="dialog_editform" style="display:none; overflow:visible; padding:1px; ">        
        <form id="fSwitchEdit" name="fSwitchEdit" method="post" action="abon_en_switch_edit.php" >
          <div class="pane"  >
            <input name="id" type="hidden" id="fid" value="" />
            <input name="oper" type="hidden" id="foper" value="" />    
            <div class="pane"  >

            <p>
                <input name="id_paccnt" type="hidden" id = "fid_paccnt" size="10" value= "" data_old_value = ""/>    
                <label>Абонент &nbsp &nbsp
                    <input  name="abon" type="text" id = "fpaccnt_name" size="70" value= "" data_old_value = "" readonly />
                </label>                 
                <button type="button" class ="btnSel" id="btPaccntSel"></button>                       
            </p>            
                
            <p>            
                <label>Дата події (відключення / вручення попередження)
                    <input name="dt_action" type="text" size="12" value="" id="fdt_action" class="dtpicker" data_old_value = "" />
                </label>
                &nbsp;&nbsp
                <label>№ акта
                    <input name="act_num" type="text" size="10"  id ="fact_num" value= "" data_old_value = "" />
                </label>
                
            </p>
            <p>                            
            <label> Подія
                <select name="action" size="1" id="faction">
                    <?php echo "$lactionslist" ?>;                        
                </select>                    
            </label>
            </p>         
            
            <p>  
                <input name="id_position" type="hidden" id = "fid_position" size="10" value= "" data_old_value = ""/>    
                <label> Роботу виконав
                    <input name="position" type="text" id = "fposition" size="50" value= "" data_old_value = "" readonly />
                </label> 
                <button type="button" class ="btnSel" id="btCntrlSel"></button>      
            </p>
            
            <div class="pane"  >
                <p>
                <b>Попередження:</b>
                
                    <label>Дата друку
                        <input name="dt_create" type="text" size="15" value="" id="fdt_create" class="dtpicker" data_old_value = "" />
                    </label>
                </p>
                <p>
                 <label>Борг, грн
                    <input name="sum_warning" type="text" id = "fsum_warning" size="10" value= "" data_old_value = ""/>
                 </label> 
                 <!--   
                 <label>за обсяг споживання, кВтг
                    <input name="demand_varning" type="text" id = "fdemand_varning" size="10" value= "" data_old_value = ""/>
                 </label> 
                 -->
                </p>                
                <p>
                <label>Дата боргу
                    <input name="dt_sum" type="text" size="13" value="" id="fdt_sum" class="dtpicker" data_old_value = "" />
                </label>

                <label>Період виникнення боргу
                    <input name="mmgg_debet" type="text" size="13" value="" id="fmmgg_debet" class="dtpicker" data_old_value = "" />
                </label>
                
                <label>Сплатити до
                    <input name="dt_warning" type="text" size="13" value="" id="fdt_warning" class="dtpicker" data_old_value = "" />
                </label>
                </p>                

            </div>
            <p>                
              <label> Місце відключення
                <select name="id_switch_place" size="1" id="fid_switch_place">
                    <?php echo "$lplacelist" ?>;                        
                </select>                    
              </label>
            </p>                
            <p>
            <label>Примітка 
            <input name="comment" type="text" id = "fcomment" size="80" value= "" data_old_value = ""/>
            </label> 
            </p>

                        
            </div>    
        </div>    
        <div class="pane" >
                <button name="submitButton" type="submit" class ="btn" id="bt_edit" value="edit" >Записати</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_add" value="add" style="display:none;">Записати новий</button>                
                <button name="resetButton" type="button" class ="btn" id="bt_reset" value="bt_reset" >Відмінити</button>         
                <button name="resetButton" type="button" class ="btn" id="bt_indic" value="bt_indic" >Показники</button>                                         
        </div>
            
      </FORM>
            
      </div>           
    
<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>

<div id="dialog-confirm" title="Удалить?" style="display:none;">
    <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
    <p id="dialog-text"> Продолжить? </p></p>
</div>

<form id="fpaccnt_sel_params" name="paccnt_sel_params" target="paccnt_win" method="post" action="abon_en_main.php">
   <input type="hidden" name="select_mode" value="1" />
</form>

<div id="grid_selabon" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 100000;" >
             <table id="abon_en_sel_table" style="margin:1px;"></table>
             <div id="abon_en_sel_tablePager"></div>
</div>

<div id="grid_selperson" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 100000;" > 
      <table id="person_sel_table" style="margin:1px;"></table>
      <div id="person_sel_tablePager"></div>
</div>    


<form id="freps_params" name="reps_params" method="post" action="rep_main_build_html.php" target="_blank">
        
 <input type="hidden" name="template_name" id="ftemplate_name" value="warning_print" />      
 <input type="hidden" name="oper" id="foper" value="warning_print" />      
 <input type="hidden" name="period_str" id = "fperiod_str" size="30" value= ""  />
 <input type="hidden" name="dt_b" size="10"  id ="fdt_b" value= "" />
 <input type="hidden" name="to_xls" id="fxls" value="0" />  
 <input type="hidden" name="id_warning" id="fid_warning" value="" />  
 <input type="hidden" name="list_mode" id="flist_mode" value="" />  
 <input type="hidden" name="grid_params" id="fgrid_params" value="" />  
</form> 

    <div id="dialog-indications" title="Показники" style="display:none;">
        <label>Вибрати лічильники на дату 
            <input name="dt_ind" type="text" size="15" class="dtpicker" id ="fdt_ind" value= "" data_old_value = "" />
        </label>
        <button type="button" class ="btnRefresh" id="btIndRefresh"></button>  &nbsp;&nbsp;

        <div id="grid_indications" >   
            <table id="new_indications_table" style="margin:1px;"></table>
            <div id="new_indications_tablePager"></div>
        </div>    
    </div>


<?php

end_mpage();
?>


