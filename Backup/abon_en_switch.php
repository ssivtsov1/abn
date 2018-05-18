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
$nm1 = "АЕ-Попередження та відключення абонента $paccnt_info";

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
print('<script type="text/javascript" src="abon_en_switch.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="staff_list_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="ind_direct_edit.js?version='.$app_version.'"></script> ');

$lactions=DbTableSelList($Link,'cli_switch_action_tbl','id','name');
$lactionslist=DbTableSelect($Link,'cli_switch_action_tbl','id','name');

$lplace=DbTableSelList($Link,'cli_switch_place_tbl','id','name');
$lplacelist=DbTableSelect($Link,'cli_switch_place_tbl','id','name');

$staff_dep= DbTableSelList($Link,'prs_department','id','name');
$lzones=DbTableSelList($Link,'eqk_zone_tbl','id','nm');
$lindicoper =DbTableSelList($Link,'cli_indic_type_tbl','id','name');

$ltasklist=DbTableSelect($Link,'cli_tasks_tbl','id','name');
$lreasonlist=DbTableSelect($Link,'cli_tasks_reason_tbl','id','name');
$labnstatelist=DbTableSelect($Link,'cli_tasks_abn_state_tbl','id','name');
$lstatelist=DbTableSelect($Link,'cli_tasks_state_tbl','id','name');

$r_edit = CheckLevel($Link,'відключення',$session_user);
$r_indic = CheckLevel($Link,'сальдо-показники',$session_user);

?>
<style type="text/css"> 
#pmain_content {padding:3}

.ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default {  color: #111111; }
#dialog-indications {padding:1px;}
</style>   


<script type="text/javascript">

var id_paccnt = <?php echo "$id_paccnt" ?>;
var lactions = <?php echo "$lactions" ?>; 
var lplace = <?php echo "$lplace" ?>; 

var lzones = <?php echo "$lzones" ?>; 
var lindicoper = <?php echo "$lindicoper" ?>; 

var staff_dep = <?php echo "$staff_dep" ?>; 

var paccnt_info = <?php echo "'$paccnt_info'" ?>; 
var r_edit = <?php echo "$r_edit" ?>;    
var r_indic = <?php echo "$r_indic" ?>;    
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

        <div id="pswitch_list" style="margin:1px;padding:1px;" > 
            <table id="switch_table" style="margin:1px;padding:1px;"></table>
            <div id="switch_tablePager"></div>
        </div>

    </DIV>

        <div id="dialog_editform" style="display:none; overflow:visible; padding:1px;">        
        <form id="fSwitchEdit" name="fSwitchEdit" method="post" action="abon_en_switch_edit.php" >
          <div class="pane"  >
            <input name="id" type="hidden" id="fid" value="" />
            <input name="oper" type="hidden" id="foper" value="" />    
            <input name="id_paccnt" type="hidden" id="fid_paccnt" value="" /> 
            <div class="pane"  >
                
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
                </p>
                -->
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
    
        <div id="dialog_editform_task" style="display:none; overflow:visible; padding:1px; ">        
        <form id="fTaskEdit" name="fTaskEdit" method="post" action="abon_en_switch_task_edit.php" >
          <div class="pane"  >
            <input name="id" type="hidden" id="fid" value="" />
            <input name="oper" type="hidden" id="foper" value="" />    
            <div class="pane"  >

            <p>            
                <label>Дата формування
                    <input name="dt_create" type="text" size="12" value="" id="fdate_print" class="dtpicker" data_old_value = "" />
                </label>
            </p>            
                
            <p>
                <input name="id_paccnt" type="hidden" id = "fid_paccnt" size="10" value= "" data_old_value = ""/>    
<!--
                <label>Книга/особовий рахунок &nbsp &nbsp
                    <input name="book" type="text" id = "fbook" size="5" value= "" data_old_value = ""/>
                </label> 
                <label> /
                    <input name="code" type="text" id = "fcode" size="10" value= "" data_old_value = ""/>            
                </label> &nbsp &nbsp
                
                <br/>
                <label>
                    <input  name="abon" type="text" id = "fpaccnt_name" size="50" value= "" data_old_value = "" readonly />
                    <input  name="addr" type="text" id = "fpaccnt_addr" size="50" value= "" data_old_value = "" readonly />
                </label>                 
                <button type="button" class ="btnSel" id="btPaccntSel"></button>                       
            </p>            
-->                
            <p>            
                <label>Дата виконання
                    <input name="dt_action" type="text" size="12" value="" id="fdate_work" class="dtpicker" data_old_value = "" />
                </label>
                &nbsp;&nbsp

                <label>№ завдання
                    <input name="act_num" type="text" size="10"  id ="ftask_num" value= "" data_old_value = "" />
                </label>
                
            </p>
            <p>                            
            <label> Подія
                <select name="idk_work" size="1" id="fidk_work">
                    <?php echo "$ltasklist" ?>;                        
                </select>                    
            </label>
            </p>         
            <p>                            
            <label> Підстава
                <select name="idk_reason" size="1" id="fidk_reason">
                    <?php echo "$lreasonlist" ?>;                        
                </select>                    
            </label>
                
            <label>Борг, грн
                    <input name="sum_warning" type="text" id = "fsum_warning" size="10" value= "" data_old_value = ""/>
            </label> 
               
            </p>         
            <p>                            
            <label> Стан абонента
                <select name="idk_abn_state" size="1" id="fidk_abn_state">
                    <?php echo "$labnstatelist" ?>;                        
                </select>                    
            </label>
            </p>         
            <p>                            
            <label> Статус завдання
                <select name="task_state" size="1" id="ftask_state">
                    <?php echo "$lstatelist" ?>;                        
                </select>                    
            </label>
            </p>         

            
            <p>
            <label>Примітка 
            <input name="comment" type="text" id = "fnote" size="80" value= "" data_old_value = ""/>
            </label> 
            </p>

                        
            </div>    
        </div>    
        <div class="pane" >
                <button name="submitButton" type="submit" class ="btn" id="bt_edit" value="edit" >Записати</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_add" value="add" style="display:none;">Записати новий</button>
                <button name="resetButton" type="button" class ="btn" id="bt_reset" value="bt_reset" >Відмінити</button>
        </div>
            
      </FORM>
            
      </div>        
    
<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>

<div id="grid_selperson" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 100000;" > 
      <table id="person_sel_table" style="margin:1px;"></table>
      <div id="person_sel_tablePager"></div>
</div>    

<div id="dialog-confirm" title="Удалить?" style="display:none;">
    <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
    <p id="dialog-text"> Продолжить? </p></p>
</div>

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


