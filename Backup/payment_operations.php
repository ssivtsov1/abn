<?php

header('Content-type: text/html; charset=utf-8'); 

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();
error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];


if ((isset($_POST['select_mode']))&&($_POST['select_mode']=='1'))
{
   $selmode = 1; 
   $nmp1 = "Абон-енерго Вибір пачки";
}
else
{
   $selmode = 0; 
   $nmp1 = "АЕ-Рознесення оплат";
    
}    
  
start_mpage($nmp1); //заголовок
head_addrpage();  // хедер, подключение библиотек

$Query=" select to_char(fun_mmgg(), 'DD.MM.YYYY') as mmgg;";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$mmgg = $row['mmgg'];

$lorigin=DbTableSelList($Link,'aci_pay_origin_tbl','id','name');
$loriginselect=DbTableSelect($Link,'aci_pay_origin_tbl','id','name'); 
$lzones=DbTableSelList($Link,'eqk_zone_tbl','id','nm');
$lindicoper =DbTableSelList($Link,'cli_indic_type_tbl','id','name');
$lidk_doc=DbTableSelList($Link,'dci_doc_tbl','id','name');

$lidk_docselectpay=DbTableSelect($Link,'(select * from dci_doc_tbl where id_grp = 100) as t','id','name');
$llgtreasonselect =DbTableSelect($Link,'lgi_change_reason_tbl','id','name');

$r_edit = CheckLevel($Link,'оплата',$session_user);
$r_lock = CheckLevel($Link,'оплата-блок пачки',$session_user);
$r_unlock = CheckLevel($Link,'оплата-разблок пачки',$session_user);

$town_hidden=CheckTownInAddrHidden($Link);

print('<link rel="stylesheet" type="text/css" href="css/ded_styles.css" /> ');
print('<link rel="stylesheet" type="text/css" href="css/layout-default-latest.css" /> ');
print('<script type="text/javascript" src="js/jquery.layout.min-latest.js"></SCRIPT>');
print('<script type="text/javascript" src="js/jquery.form.js"></script>');
print('<script type="text/javascript" src="js/jquery.validate.min.js?version='.$app_version.'" ></script>');
print('<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script> ');
print('<script type="text/javascript" src="js/jquery.ui.datepicker-uk.js"></script> ');
print('<script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script> ');
print('<script type="text/javascript" src="js/date-uk-UA.js"></script> ');
print('<script type="text/javascript" src="js/spin.min.js"></script> ');
print('<script type="text/javascript" src="abon_en_main_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="payment_operations.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="check_session.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="ind_direct_edit.js?version='.$app_version.'"></script> ');

echo <<<SCRYPT
<script type="text/javascript">
    var lorigin = $lorigin;
    var lidk_doc = $lidk_doc;    
    var mmgg = '{$mmgg}';        
    var selmode = $selmode;    
    var r_edit = $r_edit;
    var r_lock = $r_lock;
    var r_unlock = $r_unlock;        
    var town_hidden = $town_hidden;    
    var lzones = $lzones;    
    var lindicoper = $lindicoper;
</script>
SCRYPT;

?>

<style type="text/css"> 
    table#dialog_payinput_log { border-collapse:collapse; font-family: "Arial"; font-size: 12px; font-weight:normal; background-color: white;}
    table#dialog_payinput_log td { border:1px solid black;padding-top:1px; padding-bottom: 1px; padding-left:2px;padding-right:2px; }
    table#dialog_payinput_log th { border:1px solid black;padding-top:1px; padding-bottom: 1px; padding-left:2px;padding-right:2px;font-family: "Times New Roman", Times, serif;font-size: 12px; }
    #dialog-indications {padding:1px;}
</style>   

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


          <div class="ui-corner-all ui-state-default" id="pFooterBar">
           <label>Всього платежів
                <input name="fcnt_all" type="text" size="10"  id ="fcnt_all" value= "" readonly/>
           </label>
            &nbsp;&nbsp;
           <label> на суму 
                <input name="fsum_all" type="text" size="12"  id ="fsum_all" value= "" readonly/>
           </label>
          </div>
        </div>    
    </DIV>
    
        <div id="dialog_editform" style="display:none; overflow:visible; padding:1px; ">        
        <form id="fHeaderEdit" name="fHeaderEdit" method="post" action="payment_operations_header_edit.php" >
          <div class="pane"  >
            <input name="id" type="hidden" id="fid" value="" />
            <input name="oper" type="hidden" id="foper" value="" />            
            <input name="change_date" type="hidden" id ="fchange_date" value="" />                            
            <div class="pane"  >    


                <p>            
                    <label>Номер пачки.
                        <input name="reg_num" type="text" id = "freg_num" size="20" value= "" data_old_value = ""/>
                    </label> 
                    <label>Дата 
                        <input name="reg_date" type="text" size="12" value="" id="freg_date" class="dtpicker" data_old_value = "" />
                    </label>

                </p>

                <p> 
                    <label>Походження 
                        <select name="id_origin" size="1" id = "fid_origin"  value= "" data_old_value = "">                             
                            <?php echo "$loriginselect" ?>;
                        </select>                    
                    </label> 
                </p>
                
                <p>            
                    <label >Кількість квитанцій
                        <input name="count_pay" type="text" id = "fcount_pay" size="10" value= "" data_old_value = ""/>
                    </label> 
                </p>
                
                <p>            
                    <label >Сума по пачці
                        <input name="sum_pay" type="text" id = "fsum_pay" size="15" value= "" data_old_value = ""/>
                    </label> 
                </p>

                <p>            
                    <label >Файл даних
                        <input name="name_file" type="text" id = "fname_file" size="23" value= "" data_old_value = ""/>
                    </label> 

                    <label> Завантажити
                       <input type="file" size="60" name="pay_file"/>
                    </label>    

                </p>
            </div>    

        </div>    
        <div class="pane" >
                <button name="submitButton" type="submit" class ="btn" id="bt_edit" value="edit" >Записати</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_add" value="add" style="display:none;">Записати новий</button>                
                <button name="resetButton" type="button" class ="btn" id="bt_reset" value="bt_reset" >Відмінити</button>         
                &nbsp;&nbsp;
                <button name="submitButton" type="submit" class ="btn" id="bt_lock" value="lock" style="display:none;">Блокувати</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_unlock" value="unlock" style="display:none;">Разблокувати</button>
                &nbsp;
                <span id="lwait" style="color:navy; display:none; "> Зачекайте ... </span>
        </div>
            
      </FORM>
            
      </div>  
    
    
        <div id="dialog_payinput" style="display:none; padding:1px; "> 
        <form id="fPayInput" name="fPayInput" method="post" action="payment_operations_pay_input_edit.php" >
          
            <input name="id_headpay" type="hidden" id="fid_headpay" value="" />
            <input name="id_paccnt" type="hidden" id="fpay_paccnt" value="" />
            <input name="oper" type="hidden" id="foper" value="" />            
            <div class="pane" id="payinput_outer" >    
                <p>
                    <label>Період оплати
                        <input name="mmgg_pay" type="text" size="12" value="" id="fmmgg_pay" class="dtpicker" data_old_value = "" />
                    </label>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <label>
                      <input type="checkbox" name="barcode_input" id="fbarcode_input" value="Yes" data_old_checked = ""/>
                       Зі штрих-коду
                    </label>
                </p>
                <div class="pane" id="pbarcode_data" >
                    <label>Дані зі штрих-коду
                        <input name="barcode_data" tabindex="10" type="text" size="40" value="" id="fbarcode_data"  />
                    </label>
                </div>    
                <table>
                    <tr><td>Книга</td><td>Особовий рахунок</td><td>Сума</td></tr>
                    <tr>
                        <td><input type="text" tabindex="11" name="book" value="" id="fpay_book" maxlength="12"/></td>
                        <td><input type="text" tabindex="12" name="code" value="" id="fpay_code" maxlength="12"/></td>
                        <td><input type="text" tabindex="13" name="summ" value="" id="fpay_summ" maxlength="12"/></td>
                        <td>
                            <button name="submitButton" type="submit" class ="btn" id="bt_add" value="add" >Записати</button>
                        </td>
                    </tr>
                </table>
                <a href="javascript:void(0)" id="abon_label" style="color:blue;text-decoration:underline" tabindex="-1" ></a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <a href="javascript:void(0)" id="abon_indic" style="color:green;text-decoration:underline" tabindex="-1" > Показники </a> 
                <p style="color:blue; background-color: white " id="abon_info">  </p>

                <div id="dialog_payinput_err" style="color:red;"> </div>

               <div class="pane" id ="payinput_inner" style="height:350px; overflow-y: scroll; ">    
                <table id="dialog_payinput_log">
                    <tr>
                        <th width="20px">№</th>
                        <th width="60px">Рахунок</th>
                        <th width="200px">Абонент</th>
                        <th width="200px">Адреса</th>
                        <th width="50px"> Період</th>
                        <th width="50px" >Сума</th>
                    </tr>
                 </table>

               </div>
               <div class="pane" style=" bottom:0; left:0; right:0;">
                <table id="dialog_payinput_sum">
                    <tr>
                        <td>Кільк.пачки</td>
                        <td width="60px">
                            <input type="text" name="total_cnt_h" value="" size="7" id="ftotal_cnt_h" readonly />
                        </td>
                        <td>Кільк.заведено</td>
                        <td width="60px">
                            <input type="text" name="total_cnt" value="" size="7" id="ftotal_cnt" readonly />
                        </td>

                        <td>Сума пачки</td>
                        <td width="60px">
                            <input type="text" name="total_summ_h" value="" size="10" id="ftotal_summ_h" readonly />                            
                        </td>
                        <td>Сума заведено</td>
                        <td width="60px">
                            <input type="text" name="total_summ" value="" size="10" id="ftotal_summ" readonly />                                                        
                        </td>
                    </tr>
                 </table>
                   
               </div>
                
            </div>    
        
        <div class="pane" style="position:absolute; bottom:0; left:0; right:0;" >
                   <button name="submitButton" type="submit" class ="btn" id="bt_save" value="save" >Прийняти та закрити</button>
                   <span id="lwait_save" style="color:navy; display:none; "> Зачекайте закінчення операції ... </span>                   
        </div>

      </FORM>
      </div>      

    
        <div id="dialog_payedit" style="display:none; overflow:visible; padding:1px; "> 
        <form id="fPayEdit" name="fPayEdit" method="post" action="payment_operations_pay_edit.php" >
          <div class="pane"  >
            <input name="id_doc" type="hidden" id="fid" value="" />              
            <input name="id_headpay" type="hidden" id="fid_headpay" value="" />
            <input name="oper" type="hidden" id="foper" value="" />            
   
            <p>
                <input name="id_paccnt" type="hidden" id = "fid_paccnt" size="10" value= "" data_old_value = ""/>
                <label>Абонент &nbsp &nbsp
                    <input name="book" type="text" id = "fbook" size="5" value= "" data_old_value = "" readonly />
                </label> 
                <label> /
                    <input name="code" type="text" id = "fcode" size="7" value= "" data_old_value = "" readonly />
                </label>                 
                <input  name="abon" type="text" id = "fabon" style ="width:315px" value= "" data_old_value = "" readonly />
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
                    <input name="reg_date" type="text" size="12" value="" id="freg_date_pay" class="dtpicker" data_old_value = "" />
                </label>
                <label>Дата надх.
                    <input name="pay_date" type="text" size="12" value="" id="fpay_date" class="dtpicker" data_old_value = "" />
                </label>

            </p>

            <p>            
                <label >Сума 
                    <input name="value" type="text" id = "fvalue" size="10" value= "" data_old_value = ""/>
                </label> 
                <label >в.т.ч ПДВ 
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
                    <input name="mmgg" type="text" size="12" value="" id="fmmgg_p"  readonly />
                </label>

                <label>Період опл.
                    <input name="mmgg_pay" type="text" size="12" value="" id="fmmgg_pay" class="dtpicker" data_old_value = "" />
                </label>

                <label>Період пачки
                    <input name="mmgg_hpay" type="text" size="12" value="" id="fmmgg_hpay" class="dtpicker" data_old_value = "" />
                </label>

            </p>

            <p> Примітка <br/>
                <textarea  style="height: 20px" cols="80" name="note" id="fnote" data_old_value = "" ></textarea>
            </p>

                <p>                                
                    <label>Оператор
                        <input name="user_name" type="text" id = "fuser_name" size="30" value= ""  data_old_value = "" readonly />
                    </label> 
                    <label>Час заведення
                        <input name="dt" type="text" id = "fdt" size="20" value= ""  data_old_value = "" readonly />
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
    
      <div id="grid_selabon" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 100000;" >
             <table id="abon_en_sel_table" style="margin:1px;"></table>
             <div id="abon_en_sel_tablePager"></div>
      </div>
    
    
      <div id="dialog-confirm" title="" style="display:none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text"> </p>
      </div>

    <div id="dialog-changedate" title="Дата редагування" style="display:none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text">Вкажіть дату редагування </p>
        <br/>
        <input name="date_change" type="text" size="20" class="dtpicker" id ="fdate_change"/>
    </div>
   
    <div id="dialog-find" title="Пошук" style="display:none;">
        <p id="dialog-text"> Пошук (книга/рах) 
         <input name="book" type="text" size="7" value="" id="find_book"  /> /
         <input name="code" type="text" size="7" value="" id="find_code"  />
        </p>
    </div>

    <div id="dialog-find-sum" title="Пошук по сумі" style="display:none;">
        <p id="dialog-text"> Сума
          <input name="find_sum" type="text" size="10" value="" id="ffind_sum"  /> 
        </p>
    </div>
     
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
    
    <form id="fpaccnt_sel_params" name="paccnt_sel_params" target="paccnt_win" method="post" action="abon_en_main.php">
        <input type="hidden" name="select_mode" value="1" />
    </form>
    
    <form id="fpayheader_sel_params" name="payheader_sel_params" target="paccnt_win" method="post" action="payment_operations.php">
        <input type="hidden" name="select_mode" value="1" />
    </form>    
    
    <div id="calc_progress" style ="" > 
      <p> Йде обробка... </p>
    
      <div id ="progress_indicator" style="position:absolute; top:50px; left:50%"> </div>
      <div id ="progress_time" style="position:absolute; height:20px;  top:80px; left:40%"> </div>
    </div>

<form id="freps_params" name="reps_params" method="post" action="rep_main_build_html.php" target="_blank">
        
 <input type="hidden" name="template_name" id="ftemplate_name" value="pay_list" />      
 <input type="hidden" name="oper" id="foper" value="pay_pack_print" />      
 <input type="hidden" name="period_str" id = "fperiod_str" size="30" value= ""  />
 <input type="hidden" name="dt_b" size="10"  id ="fdt_b" value= "" />
 <input type="hidden" name="to_xls" id="fxls" value="0" />  
 <input type="hidden" name="id_pack" id="fid_pack" value="" />  
 <input type="hidden" name="grid_params" id="fgrid_params" value="" />  
</form> 
    
<?php

print('<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>');


end_mpage();
?>