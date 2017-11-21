<?php

header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();
error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];

$nmp1 = "АЕ-Підстанції";
$cp1 = "Перелік підстанцій";
$gn1 = "dov_tp";
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

print('<script type="text/javascript" src="dov_tp.js"></script> ');
print('<script type="text/javascript" src="dov_fider_sel.js"></script> ');

$lvolt=DbTableSelList($Link,'eqk_voltage_tbl','id','voltage');
$lvoltage=DbTableSelect($Link,'eqk_voltage_tbl','id','voltage');

$r_edit = CheckLevel($Link,'довідник-фідери',$session_user);

echo <<<SCRYPT
<script type="text/javascript">
var lvolt = $lvolt;
var r_edit = $r_edit;                 
</script>
SCRYPT;

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
</DIV>

<div id="grid_dform">    
<table id="<?php echo $gn1_table ?>" > </table>
<div id="<?php echo $gn1_pager ?>" ></div>
</div>    

        <div id="dialog_editform" style="display:none; overflow:visible; ">        
        <form id="fTpEdit" name="fTpEdit" method="post" action="dov_tp_edit.php" >
          <div class="pane"  >
            <input name="id" type="hidden" id="fid" value="" />
            <input name="oper" type="hidden" id="foper" value="" />            
            
            <label>Назва 
            <input name="name" type="text" id = "fname" size="40" value= "" data_old_value = ""/>
            </label> 
            <br>

            <input name="addr" type="hidden" id = "faddr" size="10" value= "" data_old_value = ""/>    
            <label>Адреса 
            <input name="addr_str" type="text" id = "faddr_str" size="70" value= "" data_old_value = ""/>
            </label> 
            <button type="button" class ="btnSel" id="btAddrSel"></button>      
            <br>

            <input name="id_fider" type="hidden" id = "fid_fider" size="10" value= "" data_old_value = ""/>    
            <label>Фідер 
            <input name="fider" type="text" id = "ffider" size="70" value= "" data_old_value = ""/>
            </label> 
            <button type="button" class ="btnSel" id="btFiderSel"></button>      
            
            <br>
            <label>Дата встановлення
            <input name="dt_install" type="text" size="20" value="" id="fdt_install" class="dtpicker" data_old_value = "" />
            </label>
            <br>
            <label> Напруга, кВ
                <select name="id_voltage" size="1" id="fid_voltage">
                    <?php echo "$lvoltage" ?>;                        
                </select>                    
            </label>
            <p> 
            <label> Потужність
             <input name="power" type="text" size="10" id = "fpower" />
             </label>
            </p>
            
            
            <label>
            <input type="checkbox" name="abon_ps" id="fabon_ps" value="Yes" data_old_checked = "" />
            Належить абоненту </label>
            <br>
        </div>    
        <div class="pane" >
                <button name="submitButton" type="submit" class ="btn" id="bt_edit" value="edit" >Записати</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_add" value="add" style="display:none;">Записати новий</button>                
                <button name="resetButton" type="button" class ="btn" id="bt_reset" value="bt_reset" >Відмінити</button>         
        </div>
            
      </FORM>
            
      </div>                

      <div id="grid_selfider" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1; z-index: 100000; overflow:visible; " >   
      <table id="dov_fider_table" style="margin:1px;"></table>
      <div id="dov_fider_tablePager"></div>
      </div>    

    <form id="fadr_sel_params" name="adr_sel_params" target="adr_win" method="post" action="adr_tree_selector.php">
        <input type="hidden" name="select_mode" value="1" />
        <input type="hidden" name="address" id="fadr_sel_params_address" value="" />
    </form>



<?php
print('<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>');



end_mpage();
?>