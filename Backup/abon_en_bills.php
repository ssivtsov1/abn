<?php

header('Content-type: text/html; charset=utf-8');

require_once 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();
//error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];

$nm1 = "АЕ-Рахунки абонентів";

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

print('<script type="text/javascript" src="abon_en_bills.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="abon_en_bill_det.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="abon_en_main_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="js/spin.min.js"></script> ');
print('<script type="text/javascript" src="check_session.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="runner_sectors_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="bill_cache_sel.js?version='.$app_version.'"></script> ');

$lid_pref=DbTableSelList($Link,'aci_pref_tbl','id','name');
$lid_prefselect=DbTableSelect($Link,'aci_pref_tbl','id','name');
$lidk_doc=DbTableSelList($Link,'dci_doc_tbl','id','name');

$lidk_docselectbill=DbTableSelect($Link,'(select * from dci_doc_tbl where id_grp = 200) as t','id','name');

$r_edit = CheckLevel($Link,'сальдо-рахунки',$session_user);

$town_hidden=CheckTownInAddrHidden($Link);

?>
<style type="text/css"> 
#pmain_content {padding:3}

.tabPanel {padding:1px; margin:1px; }
.ui-tabs .ui-tabs-panel {padding:1px;}

.ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default {  color: #111111; }
#pActionBar { position:relative; height:23px; border-width:1px; width:"100%"; padding:4px; margin: 1px;}    

#bt_print_all   {color: #FFF !important; background: url("images/ui-bg_glass_45_0078ae_1x400.png") repeat-x scroll 50% 50% #0078AE !important;}
#bt_print_allall   {color: #FFF !important; background: url("images/ui-bg_glass_45_0078ae_1x400.png") repeat-x scroll 50% 50% #0078AE !important;}
</style>   


<script type="text/javascript">

var lid_pref = <?php echo "$lid_pref" ?>; 
var lidk_doc = <?php echo "$lidk_doc" ?>; 
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

    <DIV id="pmain_content">

        <div class="ui-corner-all ui-state-default" id="pActionBar">
            <label>Період
                <input name="mmgg" type="text" size="12" class="dtpicker" id ="fmmgg" value= "" />
            </label>
            &nbsp;&nbsp;&nbsp;
            
            <label>Тип 
                     <select name="id_pref" size="1" id = "fid_pref"  value= "" data_old_value = "">
                         <?php echo "$lid_prefselect" ?>;
                     </select>                    
            </label> 
            &nbsp;&nbsp;
            <button type="button" class ="btn btnSmall" id="bt_sel">Вибрати</button>		    
            &nbsp;&nbsp;
            <!--<button type="button" class ="btn btnSmall" id="bt_calc">Формувати рахунки</button> -->
            
            <input name="id_sector" type="hidden" id = "fid_sector" size="10" value= "" data_old_value = ""/>    
            <label >Дільниця
              <input style="width: 130px;" name="sector" type="text" id = "fsector" value= "" data_old_value = "" tabindex="-1" readonly />
            </label> 
            <button type="button" class ="btnSel" id="btSectorSel"></button>      
            
            <button type="button" class ="btn btnSmall" id="bt_print_all">Друк по дільниці</button>
            &nbsp;
            <button type="button" class ="btn btnSmall" id="bt_print_allall">Друк всі!</button>
        </div>
        
        
       <div id="tab_bill" > 
           <table id="bill_table" style="margin:1px;"></table>
           <div id="bill_tablePager"></div>
       </div>

    </DIV>

    
    <div id="bill_info" style="padding:1px"> 
           <table id="bill_info1_table" style="margin:1px;"></table>
           <div id="bill_info1_tablePager"></div>
           <table id="bill_info2_table" style="margin:1px;"></table>
           <div id="bill_info2_tablePager"></div>
           <table id="bill_info3_table" style="margin:1px;"></table>
           <div id="bill_info3_tablePager"></div>
           <table id="bill_info4_table" style="margin:1px;"></table>
           <div id="bill_info4_tablePager"></div>

    </div> 
    
    
    <DIV style="display:none;">
       <form id="fprint_params" name="print_params" method="post" action="bill_print.php">
         <input type="text" name="book" id="pbook" value="" />         
         <input type="text" name="mmgg" id="pmmgg" value="" />
         <input type="text" name="bill_list" id="pbill_list" value="" />
         <input type="text" name="id_sector_filter" id="pid_sector_filter" value="" />
         <input type="text" name="id_bill" id="pid_bill" value="" />                  
         <input type="text" name="caption" id="pcaption" value="" />
         
       </form>
    </DIV>
    
    
        <div id="dialog_billedit" style="display:none; overflow:visible; padding:1px; "> 
        <form id="fBillEdit" name="fBillEdit" method="post" action="abon_en_bills_edit.php" >
          <div class="pane"  >
            <input name="id_doc" type="hidden" id="fid" value="" />              
            <input name="oper" type="hidden" id="foper" value="" />            
   
            <p>
                <input name="id_paccnt" type="hidden" id = "fid_paccnt" size="10" value= "" data_old_value = ""/>
                <label>Абонент &nbsp &nbsp
                    <input name="book" type="text" id = "fbook" size="5" value= "" data_old_value = "" readonly />
                </label> 
                <label> /
                    <input name="code" type="text" id = "fcode" size="7" value= "" data_old_value = "" readonly />
                </label>                 
                <input  name="abon" type="text" id = "fabon" size="50" value= "" data_old_value = "" readonly />
                <button type="button" class ="btnSel" id="btPaccntSel"></button>                       
            </p>            

            <p>            
                <label>Номер
                    <input name="reg_num" type="text" id = "freg_num" size="10" value= "" data_old_value = ""/>
                </label> 
                <label>Дата 
                    <input name="reg_date" type="text" size="12" value="" id="freg_date" class="dtpicker" data_old_value = "" />
                </label>

            </p>

            <p>            
                <label >Сума 
                    <input name="value" type="text" id = "fvalue" size="10" value= "" data_old_value = ""/>
                </label> 
                <label >в.т.ч ПДВ 
                    <input name="value_tax" type="text" id = "fvalue_tax" size="10" value= "" data_old_value = ""/>
                </label> 

                <label >кВтг 
                    <input name="demand" type="text" id = "fbilldemand" size="10" value= "" data_old_value = ""/>
                </label> 
                
            </p>
            <p> 
                <label>Тип документа
                     <select name="idk_doc" size="1" id = "fidk_doc"  value= "" data_old_value = "">
                         <?php echo "$lidk_docselectbill" ?>;
                     </select>                    
                </label> 
            </p>

            <p>            
                <label>Період
                    <input name="mmgg" type="text" size="12" value="" id="fmmgg_b" class="dtpicker" data_old_value = "" />
                </label>

                <label>Період спож.
                    <input name="mmgg_bill" type="text" size="12" value="" id="fmmgg_bill" class="dtpicker" data_old_value = "" />
                </label>

            </p>

        </div>    
            
        <div class="pane" >
                <button name="submitButton" type="submit" class ="btn" id="bt_edit" value="edit" >Записати</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_add" value="add" style="display:none;">Записати новий</button>                
                <button name="resetButton" type="button" class ="btn" id="bt_reset" value="bt_reset" >Відмінити</button>         
        </div>

      </FORM>
      </div>      
    
    
<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>

<div id="grid_selabon" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 100000;" >
             <table id="abon_en_sel_table" style="margin:1px;"></table>
             <div id="abon_en_sel_tablePager"></div>
</div>

<div id="grid_selsector" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 100000;" >   
     <table id="sectors_sel_table" style="margin:1px;"></table>
     <div id="sectors_sel_tablePager"></div>
</div>       

<div id="grid_billcache" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 100000;" >   
     <table id="bill_cache_table" style="margin:1px;"></table>
     <div id="bill_cache_tablePager"></div>
</div>       


<div id="calc_progress" style ="" > 
    <p> Йде формування рахунків... </p>
    
    <div id ="progress_indicator" style="position:absolute; top:50px; left:50%"> </div>
    <div id ="progress_time" style="position:absolute; height:20px;  top:80px; left:40%"> </div>
</div>

<div id="dialog-confirm" title="Удалить учет?" style="display:none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text">Учет будет удален. Продолжить? </p></p>
</div>

    <form id="fpaccnt_sel_params" name="paccnt_sel_params" target="paccnt_win" method="post" action="abon_en_main.php">
        <input type="hidden" name="select_mode" value="1" />
    </form>

<?php

end_mpage();
?>


