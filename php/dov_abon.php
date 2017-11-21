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
   $nmp1 = "АЕ-Перелік абонентів вибір";
   
   $id_abon= 0;          
   if (isset($_POST['id_abon']))
   {
      $id_abon= $_POST['id_abon'];
   }
       
}
else
{
   $id_abon= 0;              
   $selmode = 0; 
   $nmp1 = "АЕ-Перелік абонентів";
    
}    
 

$cp1 = "Перелік абонентів";
$gn1 = "dov_abon";

$gn1_table = $gn1."_table";
$gn1_pager = $gn1."_tablePager";

start_mpage($nmp1); //заголовок
head_addrpage();  // хедер, подключение библиотек

print('<link rel="stylesheet" type="text/css" href="css/ded_styles.css" /> ');
    
print('<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script> ');
print('<script type="text/javascript" src="js/jquery.form.js"></script>');
print('<script type="text/javascript" src="js/jquery.validate.min.js?version='.$app_version.'" ></script>');
print('<script type="text/javascript" src="js/jquery.ui.datepicker-uk.js"></script> ');
print('<script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script> ');

print('<link rel="stylesheet" type="text/css" href="css/layout-default-latest.css" /> ');
print('<script type="text/javascript" src="js/jquery.layout.min-latest.js"></SCRIPT>');

print('<script type="text/javascript" src="dov_abon.js?version='.$app_version.'"></script> ');

$r_edit = CheckLevel($Link,'довідник-абоненти',$session_user);

echo <<<SCRYPT
<script type="text/javascript">
    
var selmode = $selmode;
var id_abon = $id_abon;
var r_edit = $r_edit;                  
</script>
SCRYPT;

//middle_mpage(); // верхнее меню
?>

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

<div id="grid_dform">    
<table id="<?php echo $gn1_table ?>" > </table>
<div id="<?php echo $gn1_pager ?>" ></div>
</div>    

        <div id="dialog_editform" style="display:none; overflow:visible; ">        
        <form id="fAbonEdit" name="fAbonEdit" method="post" action="dov_abon_edit.php" >
          <div class="pane"  >
            <input name="id" type="hidden" id="fid" value="" />
            <input name="oper" type="hidden" id="foper" value="" />    
            <input type="hidden" name="change_date"  id="fchange_date" value="" />                    
            <div class="pane"  >
            <p>
            <label>Прізвище 
            <input name="last_name" type="text" id = "flast_name" size="50" value= "" data_old_value = ""/>
            </label> 
            </p>

            <p>
            <label>Ім'я 
            <input name="name" type="text" id = "fname" size="40" value= "" data_old_value = ""/>
            </label> 
           
            <label>По батькові
            <input name="patron_name" type="text" id = "fpatron_name" size="40" value= "" data_old_value = ""/>
            </label> 
            
            </p>
            </div>
            <div class="pane"  >
            <p>            
            <input name="addr_reg" type="hidden" id = "faddr_reg" size="10" value= "" data_old_value = ""/>    
            <label>Адреса реєстрації
            <input name="addr_reg_str" type="text" id = "faddr_reg_str" size="70" value= "" data_old_value = "" readonly/>
            </label> 
            <button type="button" class ="btnSel" id="btAddr1Sel"></button>      
            </p>
            <p>            
            <input name="addr_live" type="hidden" id = "faddr_live" size="10" value= "" data_old_value = ""/>    
            <label>Адреса проживання
            <input name="addr_live_str" type="text" id = "faddr_live_str" size="70" value= "" data_old_value = "" readonly/>
            </label> 
            <button type="button" class ="btnSel" id="btAddr2Sel"></button>      
            <button type="button" class ="btn btnCopy" id="btAddr2Copy"></button>      
            </p>
            </div>
            <div class="pane"  >
            <p>
            <label>Серія паспорта
            <input name="s_doc" type="text" id = "fs_doc" size="10" value= "" data_old_value = ""/>
            </label> 
            <label>Номер 
            <input name="n_doc" type="text" id = "fn_doc" size="20" value= "" data_old_value = ""/>
            </label> 
            </p>
            <p>            
            <label>Дата видачі
            <input name="dt_doc" type="text" size="20" value="" id="fdt_doc" class="dtpicker" data_old_value = "" />
            </label>
            </p>
            <p>            
            <label>Ким виданий
            <input name="who_doc" type="text" id = "fwho_doc" size="100" value= "" data_old_value = ""/>
            </label> 
            </p>
            <p>            
            <label>Ідентифікаційний номер
            <input name="tax_number" type="text" id = "ftax_number" size="30" value= "" data_old_value = ""/>
            </label> 
            </p>
            </div>
            
            <p>            
            <label>Тел. домашній 
            <input name="home_phone" type="text" id = "fhome_phone" size="20" value= "" data_old_value = ""/>
            </label> 
            <label>Тел. робочій 
            <input name="work_phone" type="text" id = "fwork_phone" size="20" value= "" data_old_value = ""/>
            </label> 
            <label>Тел. мобільний 
            <input name="mob_phone" type="text" id = "fmob_phone" size="20" value= "" data_old_value = ""/>
            </label> 

            </p>

            <p>            
            <label>Електронна пошта
            <input name="e_mail" type="text" id = "fe_mail" size="40" value= "" data_old_value = ""/>
            </label> 
            </p>            
            
            <p>            
            <label>Примітка
            <input name="note" type="text" id = "fnote" size="100" value= "" data_old_value = ""/>
            </label> 
            </p>
            
            <p>            
            <label>Дата
            <input name="dt_b" type="text" size="20" value="" id="fdt_b" class="dtpicker" data_old_value = "" />
            </label>
            <label>Дата занесення в базу
            <input name="dt_input" type="text" size="20" value="" id="fdt_input" data_old_value = "" readonly />
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

    <div id="dialog-changedate" title="Дата редагування" style="display:none;">
        <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text">Вкажіть дату редагування </p></p>
    <br>
    <input name="date_change" type="text" size="20" class="dtpicker" id ="fdate_change"/>
    </div>

    <div id="dialog-confirm" title="Удалить?" style="display:none;">
        <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text"> Продолжить? </p></p>
    </div>
    
    <form id="fadr_sel_params" name="adr_sel_params" target="adr_win" method="post" action="adr_tree_selector.php">
        <input type="hidden" name="select_mode" value="1" />
        <input type="hidden" name="address" id="fadr_sel_params_address" value="" />
    </form>
    


<?php
print('<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>');


end_mpage();
?>