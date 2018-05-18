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
  $paccnt_book = $_POST['paccnt_book']; 
  $paccnt_code = $_POST['paccnt_code']; 
  $paccnt_name = htmlspecialchars($_POST['paccnt_name'],ENT_QUOTES); 
      
}
$nm1 = "АЕ-Сальдо та споживання абонента $paccnt_info";

start_mpage($nm1);
head_addrpage(); 

$flag_cek = is_cek($Link);  //принадлежность РЭСа к ЦЭК

$Query=" select to_char(fun_mmgg(), 'DD.MM.YYYY') as mmgg;";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$mmgg = $row['mmgg'];

$Query=" select rs.name from  prs_runner_sectors as rs 
		join prs_runner_paccnt as rp on (rp.id_sector = rs.id)
                where rp.id_paccnt = $id_paccnt ;";

$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$sector = htmlspecialchars($row['name'],ENT_QUOTES);


$Query="select coalesce(' - '||ilg.name,'') as name 
    from lgm_abon_tbl  as la
    join lgi_group_tbl as ilg on (ilg.id = la.id_grp_lgt)
    where ((la.dt_end is null) or (la.dt_end > '$mmgg'::date)) 
    and  la.id_paccnt = $id_paccnt ;";

$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$lgt = htmlspecialchars($row['name'],ENT_QUOTES);


$Query="select a.name||' '||to_char(coalesce(csw.dt_action,csw.dt_create), 'DD.MM.YYYY') as sw_info 
       from clm_switching_tbl as csw 
        join cli_switch_action_tbl as a on (a.id = csw.action )
        join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl where action in (1,2) and id_paccnt = $id_paccnt group by id_paccnt) as csdt 
       on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
       left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and cswc.dt_action >= csw.dt_action and ((cswc.action =3 and csw.action =1) or (cswc.action =4  and csw.action =2) ) ) 
       where csw.action not in (3,4) and cswc.id_paccnt is null and csw.id_paccnt = $id_paccnt;";

$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$sw = htmlspecialchars($row['sw_info'],ENT_QUOTES);

// Добавление строки адреса регистрации, в случае
// если он не совпадает с адресом проживания. (только для ЦЕК)
// изменено 27.07.2017
// начало
if($flag_cek==1) {
$Query="select address_print_full(a.addr_reg,2) as reg 
    from clm_abon_tbl  as a
    where  a.id = $id_paccnt and a.addr_reg<>a.addr_live";

$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );

$row = pg_fetch_array($result);
//$reg = htmlspecialchars($row['reg'],ENT_QUOTES);
$reg = $row['reg'];
$reg = str_replace('"','',$reg);
if(!empty($reg)) $reg = ' Адреса реєстрації: '.$reg;
// конец
}

if($flag_cek==0)
   $paccnt_info = $paccnt_info.' ('.$sector.')'.$lgt.' '.$sw;
else
   $paccnt_info = $paccnt_info.' ('.$sector.')'.$lgt.$reg.' '.$sw;

// Добавление адреса абонента - для журнала/показники з помилками (только для ЦЕК)
// изменено 31.07.2017
if($flag_cek==1) {

$pos_q = strpos($paccnt_info,'буд.');
if(!$pos_q) {
    $Query = "select address_print_full(a.addr_live,5) as addr_live 
    from clm_abon_tbl  as a
    where  a.id = $id_paccnt ";
    
    $result = pg_query($Link, $Query) or die("SQL Error: " . pg_last_error($Link));
    $row = pg_fetch_array($result);
    $addr_live = $row['addr_live'];
    $addr_live = str_replace('"', '', $addr_live);
//    $paccnt_info = mb_substr($paccnt_info,0,$pos_q-1) . ' ' . $addr_live . ' - '.mb_substr($paccnt_info,$pos_q+2);
     $paccnt_info = $paccnt_info.' ('.$addr_live.')';
}
}
// конец


print('<link rel="stylesheet" type="text/css" href="css/ded_styles.css" /> ');
print('<link rel="stylesheet" type="text/css" href="css/layout-default-latest.css" /> ');
    
print('<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script> ');
print('<script type="text/javascript" src="js/jquery.form.js"></script>');
print('<script type="text/javascript" src="js/jquery.validate.min.js?version='.$app_version.'" ></script>');
print('<script type="text/javascript" src="js/jquery.ui.datepicker-uk.js"></script> ');
print('<script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script> ');
print('<script type="text/javascript" src="js/jquery.layout.min-latest.js"></SCRIPT>');
print('<script type="text/javascript" src="js/date-uk-UA.js"></script> ');
print('<script type="text/javascript" src="js/str_pad.js"></script>');

print('<script type="text/javascript" src="abon_en_saldo.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="abon_en_bill_det.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="abon_en_main_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="ind_direct_edit.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="check_session.js?version='.$app_version.'"></script> ');

$lzones=DbTableSelList($Link,'eqk_zone_tbl','id','nm');
$lzoneselect=DbTableSelect($Link,'eqk_zone_tbl','id','nm');

$lid_pref=DbTableSelList($Link,'aci_pref_tbl','id','name');
$lidk_doc=DbTableSelList($Link,'dci_doc_tbl','id','name');

$lidk_docselectbill=DbTableSelect($Link,'(select * from dci_doc_tbl where id_grp = 200) as t','id','name');
$lidk_docselectpay=DbTableSelect($Link,'(select * from dci_doc_tbl where id_grp = 100) as t','id','name');

$lindicoper =DbTableSelList($Link,'cli_indic_type_tbl','id','name');
$llgtreasonselect =DbTableSelect($Link,'lgi_change_reason_tbl','id','name');

$r_indic = CheckLevel($Link,'сальдо-показники',$session_user);
$r_bill = CheckLevel($Link,'сальдо-рахунки',$session_user);
$r_pay = CheckLevel($Link,'сальдо-оплата',$session_user);
$r_distrib= CheckLevel($Link,'сальдо-розп.ПС',$session_user);
$r_allmeter_edit = CheckLevel($Link,'картка-лічильники-екстра',$session_user);

$town_hidden=CheckTownInAddrHidden($Link);

?>
<style type="text/css"> 
#pmain_content {padding:3}

.tabPanel {padding:1px; margin:1px; }
.ui-tabs .ui-tabs-panel {padding:1px;}

.ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default {  color: #111111; }
#dialog-indications {padding:1px;}
 
 .RedTitleClass .ui-dialog-titlebar {
          background:red;
    }
 .StandartTitleClass .ui-dialog-titlebar {
    }  
    
</style>   


<script type="text/javascript">

var id_paccnt = <?php echo "$id_paccnt" ?>;
var lzones = <?php echo "$lzones" ?>; 
var lid_pref = <?php echo "$lid_pref" ?>; 
var lidk_doc = <?php echo "$lidk_doc" ?>; 
var lindicoper = <?php echo "$lindicoper" ?>; 
var paccnt_info = <?php echo "'$paccnt_info'" ?>; 
var paccnt_book = <?php echo "'$paccnt_book'" ?>;  
var paccnt_code = <?php echo "'$paccnt_code'" ?>; 
var paccnt_name = <?php echo "'$paccnt_name'" ?>; 
var mmgg = <?php echo "'$mmgg'" ?>;  
var town_hidden = <?php echo "$town_hidden" ?>;  
var r_indic = <?php echo "$r_indic" ?>; 
var r_bill = <?php echo "$r_bill" ?>; 
var r_pay = <?php echo "$r_pay" ?>; 
var r_distrib = <?php echo "$r_distrib" ?>; 
var r_allmeter_edit = <?php echo "$r_allmeter_edit" ?>; 

</script>

</head>
<body >


<DIV id="pmain_header"> 
    
    <div style="position: absolute; width: 100px;">
        <button type="button" class ="btn" id="bt_abon">Картка</button>		                                                
    </div>

    <?php main_menu(); ?>

</DIV>
    
<DIV id="pmain_footer">
    
   <a href="javascript:void(0)" id="debug_ls1">show debug window</a> 
   <a href="javascript:void(0)" id="debug_ls2">hide debug window</a> 
   <a href="javascript:void(0)" id="debug_ls3">clear debug window</a>
   <a href="javascript:void(0)" id="show_peoples">Исполнители</a> 
</DIV>  

    <DIV id="pmain_content" style="margin:1px;padding:1px;">

        <div id="psaldo_list" style="margin:1px;padding:1px;" > 
            <table id="saldo_table" style="margin:1px;padding:1px;"></table>
            <div id="saldo_tablePager"></div>
        </div>
        
        <div id="pTabs" > 
            <ul id="tabButtons">
                <li><a href="#tab_indication">Показники</a></li>
                <li><a href="#tab_bill">Рахунки</a></li>
                <li><a href="#tab_pay">Оплата</a></li>
                <li><a href="#tab_corr">Перерахунки</a></li>
                <li><a href="#tab_inpdemand">Зел.тариф</a></li>
            </ul>   
        
        
            <div id="tab_indication" > 
                <table id="indic_table" style="margin:1px;"></table>
                <div id="indic_tablePager"></div>
            </div>
           
            <div id="tab_bill" > 
                <table id="bill_table" style="margin:1px;"></table>
                <div id="bill_tablePager"></div>
            </div>
           
            <div id="tab_pay" > 
                <table id="pay_table" style="margin:1px;"></table>
                <div id="pay_tablePager"></div>
            </div>

            <div id="tab_corr" > 
                <table id="corr_table" style="margin:1px;"></table>
                <div id="corr_tablePager"></div>
            </div>

            <div id="tab_inpdemand" > 
                <table id="inpdemand_table" style="margin:1px;"></table>
                <div id="inpdemand_tablePager"></div>
            </div>
            
        </div>

    </DIV>

    <DIV style="display:none;">
       <form id="fprint_params" name="print_params" method="post" action="bill_print.php">
         <input type="text" name="book" id="pbook" value="" />
         <input type="text" name="id_paccnt" id="pid_paccnt" value="" />
         <input type="text" name="mmgg" id="pmmgg" value="" />         
         <input type="text" name="bill_list" id="pbill_list" value="" />                  
         <input type="text" name="id_bill" id="pid_bill" value="" />                  
         <input type="text" name="caption" id="pcaption" value="" />                  
       </form>
    </DIV>
    
    <div id="dialog-indications" title="Коригування показників по абоненту" style="display:none;">
        <label>Вибрати лічильники на дату 
            <input name="dt_ind" type="text" size="15" class="dtpicker" id ="fdt_ind" value= "" data_old_value = "" />
        </label>
        <button type="button" class ="btnRefresh" id="btIndRefresh"></button>  &nbsp;&nbsp;

        <label id ="lid_reason" >Підстава коригування
            <select name="id_reason" size="1" id="fid_reason" value= "" data_old_value = "" >
                <?php echo "$llgtreasonselect" ?>;
            </select>                    
        </label>      

        <div id="grid_indications" >   
            <table id="new_indications_table" style="margin:1px;"></table>
            <div id="new_indications_tablePager"></div>
        </div>    
    </div>
    
    <div id="dialog-confirm" title="Видалити запис?" style="display:none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text">Видалити запис? </p></p>
    </div>

    <div id="dialog-confirm-reason" title="Видалити запис?" style="display:none;">
	<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text">Видалити запис? </p>
        
        <label>Підстава
            <select name="id_reason" size="1" id="fid_reason" value= "" data_old_value = "" >
                <?php echo "$llgtreasonselect" ?>;
            </select>                    
        </label>      

    </div>
    
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
    
    <div id="bill_info_lost" style="padding:1px"> 
           <table id="bill_info5_table" style="margin:1px;"></table>
           <div id="bill_info5_tablePager"></div>

    </div>
    
        <div id="dialog_billedit" style="display:none; overflow:visible; padding:1px; "> 
        <form id="fBillEdit" name="fBillEdit" method="post" action="abon_en_bills_edit.php" >
          <div class="pane"  >
            <input name="id_doc" type="hidden" id="fid" value="" />              
            <input name="oper" type="hidden" id="foper" value="" />            
            <input name="id_paccnt" type="hidden" id = "fid_paccnt" size="10" value= "" data_old_value = ""/>

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
                    <input name="mmgg" type="text" size="12" value="" id="fmmgg_b" data_old_value = "" readonly/>
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
    
    
        <div id="dialog_payedit" style="display:none; overflow:visible; padding:1px; "> 
        <form id="fPayEdit" name="fPayEdit" method="post" action="payment_operations_pay_edit.php" >
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
                <input name="id_headpay" type="hidden" id="fid_headpay" value="" data_old_value = ""/>                
                <label>Пачка &nbsp &nbsp
                <input  name="payheader" type="text" id = "fpayheader" size="50" value= "" data_old_value = "" readonly />
                </label>                 
                <button type="button" class ="btnSel" id="btPayHeaderSel"></button>                       
            </p>            

            <p>            
                <label>Номер

                    <input name="reg_num" type="text" id = "freg_num" size="10" value= "" data_old_value = ""/>
                </label> 
                <label>Дата 
                    <input name="reg_date" type="text" size="12" value="" id="freg_date_pay" class="dtpicker paydtpicker" data_old_value = "" />
                </label>
                <label>Дата надх.
                    <input name="pay_date" type="text" size="12" value="" id="fpay_date" class="dtpicker paydtpicker" data_old_value = "" />
                </label>
 
            </p>

            <p>            
                <label ><b>Сума </b>
                    <input name="value" type="text" id = "fvalue" size="10" value= "" data_old_value = ""/>
                </label> 
                <label ><b>в.т.ч ПДВ </b>
                    <input name="value_tax" type="text" id = "fvalue_tax" size="10" value= "" data_old_value = ""/>
                </label> 

            </p>
            <p> 
                <label>Тип документа
                     <select name="idk_doc" size="1" id = "fidk_doc"  value= "" data_old_value = "">
                         <?php echo "$lidk_docselectpay" ?>;
                     </select>                    
                </label> 
            </p>

            <p>            
                <label>Період
                    <input name="mmgg" type="text" size="12" value="" id="fmmgg_p" data_old_value = "" readonly />
                </label>

                <label>Період опл.
                    <input name="mmgg_pay" type="text" size="12" value="" id="fmmgg_pay" class="dtpicker paydtpicker" data_old_value = "" />
                </label>

                <label>Період пачки
                    <input name="mmgg_hpay" type="text" size="12" value="" id="fmmgg_hpay" class="dtpicker paydtpicker" data_old_value = "" />
                </label>

            </p>

            <p> Примітка <br/>
                <textarea  style="height: 20px" cols="80" name="note" id="fnote" data_old_value = "" ></textarea>
            </p>

        </div>    
        <div class="pane" >
                <button name="submitButton" type="submit" class ="btn" id="bt_edit" value="edit" >Записати</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_add" value="add" style="display:none;">Записати новий</button>                
                <button name="resetButton" type="button" class ="btn" id="bt_reset" value="bt_reset" >Відмінити</button>         
        </div>

      </FORM>
      </div>      
    
    
        <div id="dialog_inpdemandedit" style="display:none; overflow:visible; padding:1px; "> 
        <form id="fInpdemandEdit" name="fInpdemandEdit" method="post" action="abon_en_saldo_inpdemand_edit.php" >
          <div class="pane"  >
            <input name="id" type="hidden" id="fid" value="" />              
            <input name="oper" type="hidden" id="foper" value="" />            
            <input name="id_paccnt" type="hidden" id = "fid_paccnt" size="10" value= "" data_old_value = ""/>
   
            <p>    
                <label > Зона
                    <select name="id_zone" size="1" id="fid_zone" value= "" data_old_value = "" >
                        <?php echo "$lzoneselect" ?>;                        
                    </select>                    
                </label>
                
                <label ><b>Споживання, кВтг </b>
                    <input name="demand" type="text" id = "fdemand" size="10" value= "" data_old_value = ""/>
                </label> 
                
            </p>              
            
            <p>            
                <label>Дата початкова
                    <input name="dat_b" type="text" size="12" value="" id="fdat_b" class="dtpicker" data_old_value = "" />
                </label>
                <label>Дата кінцева
                    <input name="dat_e" type="text" size="12" value="" id="fdat_e" class="dtpicker" data_old_value = "" />
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
    
        <div id="dialog_recalcedit" style="display:none; overflow:visible; padding:1px; "> 
        <form id="fRecalcEdit" name="fRecalcEdit" method="post" action="abon_en_saldo_recalc_edit.php" >
          <div class="pane"  >
            <input name="id" type="hidden" id="fid" value="" />              
            <input name="oper" type="hidden" id="foper" value="" />            
            <input name="id_paccnt" type="hidden" id = "fid_paccnt" size="10" value= "" data_old_value = ""/>
            <p>            
                <label>Перерахувати за період з 
                    <input name="mmgg_begin" type="text" size="12" value="" id="fmmgg_begin" class="dtpicker" data_old_value = "" />
                </label>
                <label>по
                    <input name="mmgg_end" type="text" size="12" value="" id="fmmgg_end" class="dtpicker" data_old_value = "" />
                </label>
 
            </p>

        </div>    
        <div class="pane" >
                <button name="submitButton" type="submit" class ="btn" id="bt_add" value="add" >Записати</button>
                <button name="resetButton" type="button" class ="btn" id="bt_reset" value="bt_reset" >Відмінити</button>         
        </div>
      </FORM>
      </div> 

    
<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>

<form id="fpayheader_sel_params" name="payheader_sel_params" target="paccnt_win" method="post" action="payment_operations.php">
        <input type="hidden" name="select_mode" value="1" />
</form>

<form id="fpaccnt_sel_params" name="paccnt_sel_params" target="paccnt_win" method="post" action="abon_en_main.php">
        <input type="hidden" name="select_mode" value="1" />
</form>

<div id="grid_selabon" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 100000;" >
             <table id="abon_en_sel_table" style="margin:1px;"></table>
             <div id="abon_en_sel_tablePager"></div>
</div>


<div id="dialog-indichistory" title="Історія змін показників" style="display:none;">
     <div id="grid_indichistory" >   
             <table id="indic_history_table" style="margin:1px;"></table>
             <div id="indic_history_tablePager"></div>
     </div>    
</div>

    <DIV style="display:none;">
      <form id="fpaccnt_params" name="paccnt_params" method="post" action="">
         <input type="text" name="mode" id="pmode" value="0" />
         <input type="text" name="id_meter" id="pid_meter" value="0" />
         <input type="text" name="id_paccnt" id="pid_paccnt" value="0" />
         <input type="text" name="id_work" id="pid_work" value="0" />
         <input type="text" name="idk_work" id="pidk_work" value="0" />
         <input type="text" name="paccnt_info" id="ppaccnt_info" value="" />
         <input type="text" name="paccnt_book" id="ppaccnt_book" value="" />
         <input type="text" name="paccnt_code" id="ppaccnt_code" value="" />
         <input type="text" name="paccnt_name" id="ppaccnt_name" value="" />
         <input type="text" name="mmgg" id="ppaccnt_mmgg" value="" />
      </form>
    </DIV>

<?php


//// print("SQL - ".$S1);

end_mpage();
?>


