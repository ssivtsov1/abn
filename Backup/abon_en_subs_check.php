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
$nm1 = "АЕ-Звірка кодів субсидії";

start_mpage($nm1);
head_addrpage();
/*
$Query=" select to_char(fun_mmgg(), 'DD.MM.YYYY') as mmgg;";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$mmgg = $row['mmgg'];
*/

print('<link rel="stylesheet" type="text/css" href="css/ded_styles.css" /> ');
print('<link rel="stylesheet" type="text/css" href="css/layout-default-latest.css" /> ');
    
print('<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script> ');
print('<script type="text/javascript" src="js/jquery.form.js"></script>');
print('<script type="text/javascript" src="js/jquery.validate.min.js?version='.$app_version.'" ></script>');
print('<script type="text/javascript" src="js/jquery.ui.datepicker-uk.js"></script> ');
print('<script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script> ');
print('<script type="text/javascript" src="js/jquery.layout.min-latest.js"></SCRIPT>');
print('<script type="text/javascript" src="js/spin.min.js"></script> ');
print('<script type="text/javascript" src="abon_en_subs_check.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="abon_en_main_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="check_session.js?version='.$app_version.'"></script> ');

//$lzones=DbTableSelList($Link,'eqk_zone_tbl','id','nm');
//$lindicoper =DbTableSelList($Link,'cli_indic_type_tbl','id','name');

//$lactions=DbTableSelList($Link,'cli_badindic_action_tbl','id','name');
//$lactionslist=DbTableSelect($Link,'cli_badindic_action_tbl','id','name');

$r_edit = CheckLevel($Link,'субсидія',$session_user);

$town_hidden=CheckTownInAddrHidden($Link);
$lregionselect=DbTableSelect($Link,'cli_region_tbl','id','name');

?>
<style type="text/css"> 
#pmain_content {padding:3}

.ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default {  color: #111111; }
#pActionBar { position:relative; height:23px; border-width:1px; width:"100%"; padding:4px; margin: 1px;}    
</style>   


<script type="text/javascript">

var lstatus = 'null: ;1:Норма;2:По субсидии;3:!=адрес;4:!=фамилия;5:!=фамилия и адрес;10:До выяснения;11:Исправлено';
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

</DIV>  

    <DIV id="pmain_content" style="margin:1px;padding:1px;">
        <div class="ui-corner-all ui-state-default" id="pActionBar"> 
                <label>Регіон
                     <select name="id_region" size="1" id = "fid_region"  value= "" data_old_value = "">
                         <?php echo "$lregionselect" ?><option value="999">-Невідомий-</option>;
                     </select>                    
                </label> 
                <button type="button" class ="btn btnSmall" id="bt_sel">Вибрати</button>		                    
        </div>

        <div id="pbadsubs_list" style="margin:1px;padding:1px;" > 
            <table id="badsubs_table" style="margin:1px;padding:1px;"></table>
            <div id="badsubs_tablePager"></div>
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

    <div id="dialog_subsform" style="display:none; overflow:visible; padding:1px; ">        
        <form id="fSubsEdit" name="fSubsEdit"  target=""  method="post" action="abon_en_subs_check_edit.php" >
            <div class="pane" style="padding:1px">
                <input type="hidden" name="id" id = "fid" size="10" value= ""  />                 
                <input type="hidden" name="oper" id="foper" value="" />            
                    
                <div class="pane"  >
                        
                    <p>
                        <input name="id" type="hidden" id = "fid" size="10" value= "" data_old_value = ""/>    
                        <input name="id_paccnt" type="hidden" id = "fid_paccnt" size="10" value= "" data_old_value = ""/>    

                        <input name="bookcod" type="text" id = "fbookcod" size="10" value= "" data_old_value = "" readonly/>    
                        <input name="fio" type="text" id = "ffio" size="50" value= "" data_old_value = "" readonly/>    
                        <br/>
                        <input name="addr" type="text" id = "faddr" size="50" value= "" data_old_value = "" readonly/>    
                    </p>
                    
                    <p>                    
                        <label>Книга/рахунок &nbsp &nbsp
                            <input name="book" type="text" id = "fbook" size="5" value= "" data_old_value = "" readonly/>
                        </label> 
                        <label> /
                            <input name="code" type="text" id = "fcode" size="10" value= "" data_old_value = "" readonly/>            
                        </label>                 

                        <input  name="abon_name" type="text" id = "fabon_name" size="50" value= "" data_old_value = "" readonly />
                        <button type="button" class ="btnSel" id="btPaccntSel"></button>
                    </p>    

                    <p>                                        
                        <label>Статус
                              <select name="ident" size="1" id = "fident"  value= "" data_old_value = "">
                                <option value="null">  </option>
                                <option value="1">Норма</option>
                                <option value="2">По субсидии</option>
                                <option value="3">!=адрес</option>
                                <option value="4">!=фамилия</option>
                                <option value="5">!=фамилия и адрес</option>
                                <option value="5">!=фамилия и адрес</option>
                                <option value="10">До выяснения</option>;
                                <option value="11">Исправлено</option>;
                             </select>        
                        </label> 
                    </p>                            
                </div>   
                
            </div>
                 
            <div class ="pane" id="pPayParam_buttons"  >
                
                <button name="submitButton" type="submit" class ="btn" id="bt_edit" value="edit" >Записати</button>
                <button name="resetButton" type="button" class ="btn" id="bt_reset" value="reset" >Відмінити</button>         
                    
            </div>
        </form>
    </div>     

<div id="grid_selabon" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 100000;" >
             <table id="abon_en_sel_table" style="margin:1px;"></table>
             <div id="abon_en_sel_tablePager"></div>
</div>

<div id="calc_progress" style ="" > 
    <p> Йде обробка... </p>
    
    <div id ="progress_indicator" style="position:absolute; top:50px; left:50%"> </div>
    <div id ="progress_time" style="position:absolute; height:20px;  top:80px; left:40%"> </div>
</div>

<form id="freps_params" name="reps_params" method="post" action="rep_main_build_html.php" target="_blank">
        
 <input type="hidden" name="template_name" id="ftemplate_name" value="badsubs_list" />      
 <input type="hidden" name="oper" id="foper" value="badsubs_list" />      
 <input type="hidden" name="period_str" id = "fperiod_str" size="30" value= ""  />
 
 <input type="hidden" name="dt_b" size="10"  id ="fdt_b" value= "" />
 <input type="hidden" name="to_xls" id="fxls" value="0" />  
 
 <input type="hidden" name="grid_params" id="fgrid_params" value="" />  
 <input type="hidden" name="id_region"   id="fid_region"  value= "" />
</form> 
 
<?php

end_mpage();
?>


