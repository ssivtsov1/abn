<?php

header('Content-type: text/html; charset=utf-8');

require_once 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();
//error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];

$nm1 = "АЕ-Загрузжені оплати";

start_mpage($nm1);
head_addrpage();


if ((isset($_POST['mmgg']))&&($_POST['mmgg']!=''))    
{
  $mmgg = $_POST['mmgg']; 
}
else
{
  $Query=" select to_char(fun_mmgg(), 'DD.MM.YYYY') as mmgg;";
  $result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
  $row = pg_fetch_array($result);
  $mmgg = $row['mmgg'];
}

print('<link rel="stylesheet" type="text/css" href="css/ded_styles.css" /> ');
print('<link rel="stylesheet" type="text/css" href="css/layout-default-latest.css" /> ');
    
print('<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script> ');
print('<script type="text/javascript" src="js/jquery.form.js"></script>');
print('<script type="text/javascript" src="js/jquery.validate.min.js?version='.$app_version.'" ></script>');
print('<script type="text/javascript" src="js/jquery.ui.datepicker-uk.js"></script> ');
print('<script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script> ');
print('<script type="text/javascript" src="js/jquery.layout.min-latest.js"></SCRIPT>');
print('<script type="text/javascript" src="js/date-uk-UA.js"></script> ');
print('<script type="text/javascript" src="abon_en_bankpay.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="abon_en_main_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="js/spin.min.js"></script> ');
print('<script type="text/javascript" src="check_session.js?version='.$app_version.'"></script> ');

$lorigin=DbTableSelList($Link,'aci_pay_origin_tbl','id','name');
$loriginselect=DbTableSelect($Link,'aci_pay_origin_tbl','id','name');
$r_edit = CheckLevel($Link,'оплата',$session_user);
$town_hidden=CheckTownInAddrHidden($Link);

?>
<style type="text/css"> 
#pmain_content {padding:3}

.tabPanel {padding:1px; margin:1px; }
.ui-tabs .ui-tabs-panel {padding:1px;}

.ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default {  color: #111111; }
#pFooterBar {font-size:12px; }
#pActionBar { position:relative; height:23px; border-width:1px; width:"100%"; padding:4px; margin: 1px;}    
</style>   


<script type="text/javascript">

var mmgg = <?php echo "'$mmgg'" ?>;
var lorigin = <?php echo "$lorigin" ?>; 
var r_edit = <?php echo "$r_edit" ?>;    
var town_hidden = <?php echo "$town_hidden" ?>;  
var lstatus = 'null: ;1:Норма;2:!=адрес;3:!=фамилия;4:!=фамилия и адрес;10:По коду';

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
 
    <DIV id="pmain_center">    
        
        <div id="pHeaders_table" style="padding:2px; margin:3px;">    
            
          <div class="ui-corner-all ui-state-default" id="pActionBar">
            <label>Період
                <input name="mmgg" type="text" size="15" class="dtpicker" id ="fmmgg" value= "" />
            </label>
            &nbsp;&nbsp;
            <button type="button" class ="btn btnSmall" id="bt_sel">Вибрати</button>
           </div>
            
            <table id="headers_table" > </table>
            <div   id="headers_tablePager" ></div>
        </div>    

        <div id="ppay_table" style="padding:2px; margin:3px;">    
            <table id="pay_table" > </table>
            <div   id="pay_tablePager" ></div>

        </div>    
    </DIV>    
    

    <div id="dialog_payform" style="display:none; overflow:visible; padding:1px; ">        
        <form id="fPayEdit" name="fPayParam"  target=""  method="post" action="abon_en_bankpay_edit.php" >
            <div class="pane" style="padding:1px">
                <input type="hidden" name="id" id = "fid" size="10" value= ""  />                 
                <input type="hidden" name="oper" id="foper" value="" />            
                    
                <div class="pane"  >
                        
                    <p>
                        <input name="id_paccnt" type="hidden" id = "fid_paccnt" size="10" value= "" data_old_value = ""/>    
                        <input name="old_paccnt" type="hidden" id = "fold_paccnt" size="10" value= "" data_old_value = ""/>    
                        <input name="id_headpay" type="hidden" id = "fid_headpay" size="10" value= "" data_old_value = ""/>    

                        <input name="abcount" type="text" id = "fabcount" size="10" value= "" data_old_value = "" readonly/>    
                        <input name="fio" type="text" id = "ffio" size="50" value= "" data_old_value = "" readonly/>    
                    </p>
                    
                    <p>
                        <label> Сума
                            <input name="summ" type="text" id = "fsumm" size="15" value= "" data_old_value = "" readonly/>            
                        </label>                 
                        
                        <label> Дата
                            <input name="pdate" type="text" id = "fpdate" size="15" value= "" data_old_value = "" readonly/>
                            <input name="date_ob" type="hidden" id = "fdate_ob" size="15" value= "" data_old_value = "" readonly/>
                        </label>                 
                        
                    </p>    
                    <p>                    
                        <label>Книга/рахунок &nbsp &nbsp
                            <input name="book" type="text" id = "fbook" size="5" value= "" data_old_value = "" readonly/>
                        </label> 
                        <label> /
                            <input name="code" type="text" id = "fcode" size="10" value= "" data_old_value = "" readonly/>            
                        </label>                 

                        <input  name="abon" type="text" id = "fabon" size="50" value= "" data_old_value = "" readonly />
                        <button type="button" class ="btnSel" id="btPaccntSel"></button>
                    </p>    

                </div>   
                
            </div>
                
            <div class ="pane" id="pPayParam_buttons"  >
                
                <button name="submitButton" type="submit" class ="btn" id="bt_edit" value="edit" >Записати</button>
                <button name="resetButton" type="button" class ="btn" id="bt_reset" value="reset" >Відмінити</button>         
                    
            </div>
        </form>
    </div>     
    
<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>
 

<form id="freps_params" name="reps_params" method="post" action="rep_main_build_html.php" target="_blank">
        
 <input type="hidden" name="template_name" id="ftemplate_name" value="bank_load_list" />      
 <input type="hidden" name="oper" id="foper" value="bank_load_list" />      
 <input type="hidden" name="period_str" id = "fperiod_str" size="30" value= ""  />
 
 <input type="hidden" name="dt_b" size="10"  id ="fdt_b" value= "" />
 <input type="hidden" name="to_xls" id="fxls" value="0" />  
 
 <input type="hidden" name="grid_params" id="fgrid_params" value="" />  
 <input type="hidden" name="report_caption" id="freport_caption" value="" />  
</form> 
 

<div id="calc_progress" style ="" > 
    <p> Йде завантаження і обробка... </p>
    
    <div id ="progress_indicator" style="position:absolute; top:50px; left:50%"> </div>
    <div id ="progress_time" style="position:absolute; height:20px;  top:80px; left:40%"> </div>
</div>

<div id="dialog-confirm" title="Друк журналу?" style="display:none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text"> Виберіть варіант друку </p></p>
</div>

<div id="grid_selabon" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 100000;" >
             <table id="abon_en_sel_table" style="margin:1px;"></table>
             <div id="abon_en_sel_tablePager"></div>
</div>

<?php

end_mpage();
?>


