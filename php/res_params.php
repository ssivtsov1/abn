<?php

header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';
session_name("session_kaa");
session_start();

error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];

$nm1 = "АЕ-Параметри РЕМ";

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
print('<script type="text/javascript" src="js/jquery.layout.resizeTabLayout.min-1.2.js"></SCRIPT>');

print('<script type="text/javascript" src="res_params.js?version='.$app_version.'"></script> ');

$lresselect=DbTableSelect($Link,'syi_resinfo_tbl','id_department','small_name');
$r_edit = CheckLevel($Link,'довідник-налаштування',$session_user);

$Query=" select crt_ttbl();";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );


$Query=" select syi_resid_fun()::int as id_res;";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$id_res = $row['id_res'];  

?>
<style type="text/css"> 
#pmain_content {padding:3px;}
#pwork_header {padding:3px; font-size: 14px;}
#pwork_center {padding:3px;}
/* #pmeterzones {float:left; border-width:1;} */
#pMeterParam_left {padding:1px;  background-color: #CCCCCC; border-color: #444444; border-style: solid; border-width:1;}
#pMeterParam_right {padding:1px;  background-color: #CCCCCC; border-color: #444444; border-style: solid; border-width:1;}
/*#pMeterParam_buttons {margin:3px;  background-color: #CCCCCC; border-color: #444444; border-style: solid; border-width:1; padding: 5px; }*/

#pMeterParam {height:200}
.tabPanel {padding:1px; margin:1px; }
.ui-tabs .ui-tabs-panel {padding:1px;}
.ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default {  color: #111111; }

</style>   

<script type="text/javascript">
var r_edit = <?php echo "$r_edit" ?>;
var id_res = <?php echo "$id_res" ?>;

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

<DIV id="pmain_content">

   <div class="pane"  > 
    Параметри РЕМ
      <select name="id_res_sel" size="1" id="fid_res_sel" value= "" data_old_value = "" >
        <?php echo "$lresselect" ?>;                        
         </select>                    
    </div>    
    <form id="fResEdit" name="fResEdit" method="post" action="res_params_edit.php" >    
    <DIV id="pwork_header">
    
           <div class="pane"  > 
            
            <input name="id" type="hidden" id="fid" value="" />
            <input name="oper" type="hidden" id="foper" value="" />            
            <input name="change_date" type="hidden" id ="fchange_date" value="" />                 
            <div class="pane"  > 
            <p> 
                <label>Код РЕМ
                    <input name="code" type="text" id = "fcode" size="10" value= "" data_old_value = ""/>
                </label> 

                <label>Назва РЕМ
                    <input name="short_name" type="text" id = "fshort_name" size="70" value= "" data_old_value = ""/>
                </label> 
            </p>

            <p> 
                <label>Повна назва РЕМ
                    <input name="name" type="text" id = "fname" size="100" value= "" data_old_value = ""/>
                </label> 
            </p>

            <p> 
                <label>Назва для друкованих форм повна
                    <input name="print_name" type="text" id = "fprint_name" size="70" value= "" data_old_value = ""/>
                </label> 
                
                <label>Скорочена
                    <input name="small_name" type="text" id = "fsmall_name" size="10" value= "" data_old_value = ""/>
                </label> 
                
            </p>
            
            <p>
                <input name="addr" type="hidden" id = "faddr" size="10" value= "" data_old_value = ""/>    
                <label >Адреса РЕМ
                    <input  name="addr_full" type="text" id = "faddr_full" size="80" value= "" data_old_value = ""/>
                </label> 
                <button type="button" class ="btnSel" id="btAddrSel"></button>       
            </p>
 
            <p>
                <input name="addr_district" type="hidden" id = "faddr_district" size="10" value= "" data_old_value = ""/>    
                <label >Район (для адреси)
                    <input  name="addr_district_name" type="text" id = "faddr_district_name" size="80" value= "" data_old_value = ""/>
                </label> 
                <button type="button" class ="btnSel" id="btAddrDistrictSel"></button>       
            </p>

            <p> 
                <label>Адреса для попереджень
                    <input name="warning_addr" type="text" id = "fwarning_addr" size="100" value= "" data_old_value = ""/>
                </label> 
            </p>
            
            </div>
            <div class="pane"  > 
            <p>
                <input name="id_boss" type="hidden" id = "fid_boss" size="10" value= "" data_old_value = ""/>    
                <label> Директор
                    <input name="boss_name" type="text" id = "fboss_name" size="60" value= "" data_old_value = ""/>
                </label> 
                <button type="button" class ="btnSel" id="btBossSel"></button>      
            </p>
            
            <p>
                <input name="id_buh" type="hidden" id = "fid_buh" size="10" value= "" data_old_value = ""/>    
                <label> Головний бухгалтер
                    <input name="buh_name" type="text" id = "fbuh_name" size="60" value= "" data_old_value = ""/>
                </label> 
                <button type="button" class ="btnSel" id="btBuhSel"></button>      
            </p>

            <p>
                <input name="id_sbutboss" type="hidden" id = "fid_sbutboss" size="10" value= "" data_old_value = ""/>    
                <label> Заст.директора  з енергозбуту
                    <input name="sbutboss_name" type="text" id = "fsbutboss_name" size="60" value= "" data_old_value = ""/>
                </label> 
                <button type="button" class ="btnSel" id="btSbutSel"></button>      
            </p>

            <p>
                <input name="id_warningboss" type="hidden" id = "fid_warningboss" size="10" value= "" data_old_value = ""/>    
                <label> Підписує попередження
                    <input name="warningboss_name" type="text" id = "fwarningboss_name" size="60" value= "" data_old_value = ""/>
                </label> 
                <button type="button" class ="btnSel" id="btWarningSel"></button>      
            </p>

            <p>
                <input name="id_spravboss" type="hidden" id = "fid_spravboss" size="10" value= "" data_old_value = ""/>    
                <label> Підписує довідки
                    <input name="spravboss_name" type="text" id = "fspravboss_name" size="60" value= "" data_old_value = ""/>
                </label> 
                <button type="button" class ="btnSel" id="btSpravSel"></button>      
            </p>
            
            </div>
            
            <div class="pane"  > 
            <p> 
                <label>Код ЕДРПОУ
                    <input name="okpo_num" type="text" id = "fokpo_num" size="20" value= "" data_old_value = ""/>
                </label> 

                <label>Налоговый номер
                    <input name="tax_num" type="text" id = "ftax_num" size="20" value= "" data_old_value = ""/>
                </label> 

                <label> Номер свідоцтва
                    <input name="licens_num" type="text" id = "flicens_num" size="20" value= "" data_old_value = ""/>
                </label> 
                
            </p>
            </div>
            <div class="pane"  > 
            <p>
                Рахунок оплати за електроенергію
            </p>
                
                <label> МФО
                    <input name="ae_mfo" type="text" id = "fae_mfo" size="10" value= "" data_old_value = ""/>    
                </label>                     
                <label> Банк 
                    <input name="ae_bank_name" type="text" id = "fae_bank_name" size="80" value= "" data_old_value = ""/>
                </label> 
                <button type="button" class ="btnSel" id="btBankSel"></button>      
                <p>
                    <label> Рахунок
                        <input name="ae_account" type="text" id = "fae_account" size="20" value= "" data_old_value = ""/>
                    </label> 
                </p>
                
          
            </div>
            <div class="pane"  > 
            <p>
                <label> Телефони РЕМ (для рахунку)
                    <input name="phone_bill" type="text" id = "fphone_bill" size="80" value= "" data_old_value = ""/>    
                </label>                     
            </p>                
            <p>
                <label> Телефони РЕМ (для попередження)
                    <input name="phone_warning" type="text" id = "fphone_warning" size="80" value= "" data_old_value = ""/>    
                </label>                     
            </p>                

            <p>
                <label> Адреса ІКЦ
                    <input name="addr_ikc" type="text" id = "faddr_ikc" size="80" value= "" data_old_value = ""/>
                </label> 
            </p>
             <p>
                <label> Телефони ІКЦ
                    <input name="phone_ikc" type="text" id = "fphone_ikc" size="80" value= "" data_old_value = ""/>
                </label> 
            </p>

            <input type="hidden"   name="barcode_print"  value="No" />              
            <label>
              <input type="checkbox" name="barcode_print" id="fbarcode_print" value="Yes" data_old_checked = ""/>
              Друкувати штрих-код на рахунках
            </label>
            <input type="hidden"   name="qr_print"  value="No" />  
            <label>
              <input type="checkbox" name="qr_print" id="fqr_print" value="Yes" data_old_checked = ""/>
              Друкувати QR-код на рахунках
            </label>
            
            </div>
            
          </div>    
        <div class="pane" >
                <button name="submitButton" type="submit" class ="btn" id="bt_edit" value="edit" >Записати зміни</button>
                <button name="resetButton" type="button" class ="btn" id="bt_reset" value="bt_reset" >Відмінити</button>         
        </div>
            

    </DIV>

 </form>    
</DIV>

       
    <div id="dialog-changedate" title="Дата редагування" style="display:none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text">Вкажіть дату редагування </p></p>
        <br>
        <input name="date_change" type="text" size="20" class="dtpicker" id ="fdate_change"/>
    </div>
    
    <div id="dialog-confirm" title="Удалить учет?" style="display:none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text">Учет будет удален. Продолжить? </p></p>
    </div>
    
    
    <form id="fperson_sel_params" name="person_sel_params" target="person_win" method="post" action="staff_list.php">
      <input type="hidden" name="select_mode" value="1" />
      <input type="hidden" name="id_person" id="fperson_sel_params_id_person" value="0" />
    </form>
    
    <form id="fadr_sel_params" name="adr_sel_params" target="adr_win" method="post" action="adr_tree_selector.php">
        <input type="hidden" name="select_mode" id="fadr_sel_params_select_mode" value="1" />
        <input type="hidden" name="address" id="fadr_sel_params_address" value="" />
        <input type="hidden" name="full_addr_mode" id="ffull_addr_mode" value="1" />
    </form>    

    <form id="fbank_sel_params" name="bank_sel_params" target="bank_win" method="post" action="dov_banks.php">
        <input type="hidden" name="select_mode" value="1" />
        <input type="hidden" name="mfo" id="fbank_sel_params_mfo" value="" />
    </form>    
    

<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>


<?php

end_mpage();
?>