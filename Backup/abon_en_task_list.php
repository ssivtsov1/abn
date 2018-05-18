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
$nm1 = "АЕ-Завдання";

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
print('<script type="text/javascript" src="abon_en_task_list.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="abon_en_main_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="staff_list_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="js/date-uk-UA.js"></script> ');

print('<script type="text/javascript" src="check_session.js?version='.$app_version.'"></script> ');

$ltask=DbTableSelList($Link,'cli_tasks_tbl','id','name');
$ltasklist=DbTableSelect($Link,'cli_tasks_tbl','id','name');

$lreason=DbTableSelList($Link,'cli_tasks_reason_tbl','id','name'); 
$lreasonlist=DbTableSelect($Link,'cli_tasks_reason_tbl','id','name');

$labnstate=DbTableSelList($Link,'cli_tasks_abn_state_tbl','id','name');
$labnstatelist=DbTableSelect($Link,'cli_tasks_abn_state_tbl','id','name');

$lstate=DbTableSelList($Link,'cli_tasks_state_tbl','id','name');
$lstatelist=DbTableSelect($Link,'cli_tasks_state_tbl','id','name');

$lregion=      DbTableSelList($Link,'cli_region_tbl','id','name');

$staff_dep= DbTableSelList($Link,'prs_department','id','name');

$r_edit = CheckLevel($Link,'завдання',$session_user);

$town_hidden=CheckTownInAddrHidden($Link);
?>
<style type="text/css">  
#pmain_content {padding:3}

.ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default {  color: #111111; }
#dialog-indications {padding:1px;}
</style>   


<script type="text/javascript">

var ltask = <?php echo "$ltask" ?>; 
var lreason = <?php echo "$lreason" ?>; 
var lstate = <?php echo "$lstate" ?>; 
var labnstate = <?php echo "$labnstate" ?>; 
var lregion = <?php echo "$lregion" ?>; 

var mmgg = <?php echo "'$mmgg'" ?>;
var r_edit = <?php echo "$r_edit" ?>;  

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
            &nbsp;&nbsp;&nbsp;&nbsp;
            <button type="button" class ="btn btnSmall" id="bt_sel">Вибрати</button>		    
        </div>
        
        <div id="ptask_list" style="margin:1px;padding:1px;" > 
            <table id="task_table" style="margin:1px;padding:1px;"></table>
            <div id="task_tablePager"></div>
        </div>

    </DIV>

        <div id="dialog_editform" style="display:none; overflow:visible; padding:1px; ">        
        <form id="fTaskEdit" name="fTaskEdit" method="post" action="abon_en_task_list_edit.php" >
          <div class="pane"  >
            <input name="id" type="hidden" id="fid" value="" />
            <input name="oper" type="hidden" id="foper" value="" />    
            <div class="pane"  >

            <p>            
                <label>Дата формування
                    <input name="date_print" type="text" size="12" value="" id="fdate_print" class="dtpicker" data_old_value = "" />
                </label>
            </p>            
                
            <p>
                <input name="id_paccnt" type="hidden" id = "fid_paccnt" size="10" value= "" data_old_value = ""/>    
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
                
            <p>            
                <label>Дата виконання
                    <input name="date_work" type="text" size="12" value="" id="fdate_work" class="dtpicker" data_old_value = "" />
                </label>
                &nbsp;&nbsp

                <label>№ завдання
                    <input name="task_num" type="text" size="10"  id ="ftask_num" value= "" data_old_value = "" />
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
            <input name="note" type="text" id = "fnote" size="80" value= "" data_old_value = ""/>
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

<div id="pwarning_late_table"  title="" style="display:none; margin:1px; padding:1px;" >    
            <table id="warning_late_table" > </table>
            <div   id="warning_late_tablePager" ></div>
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


    <div id="dialog-changedate" title="Запланована дата роботи" style="display:none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text">Вкажіть дату виконання </p></p>
        <br>
        <input name="date_change" type="text" size="20" class="dtpicker" id ="fdate_change"/>
    </div>

<?php

end_mpage();
?>


