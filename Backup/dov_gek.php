<?php

header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();
error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];

$nmp1 = "АЕ-ЖЕКи";
$cp1 = "Перелік ЖЕКів";
$gn1 = "dov_gek";
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

print('<script type="text/javascript" src="dov_gek.js"></script> ');

$r_edit = CheckLevel($Link,'довідник-жек',$session_user);

echo <<<SCRYPT
<script type="text/javascript">
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
        <form id="fBuildEdit" name="fTpEdit" method="post" action="dov_gek_edit.php" >
          <div class="pane"  >
            <p>
            <label> Код для Енерго
               <input name="id" type="text" size="15" id="fid" value="" />
            </label> 
            <input name="oper" type="hidden" id="foper" value="" />            

            <label> Будинок
                <input name="name" type="text" size="35" id = "fname" value= "" data_old_value = "" />
            </label>
            </p>
            <p>
              <label> Номер ЖЕК
                  <input name="num_gek" type="text" size="10" id = "fnum_gek" value= "" data_old_value = "" />
              </label>
            </p>
            <p>    
            <input name="addr" type="hidden" id = "faddr" size="10" value= "" data_old_value = ""/>    
            <label>Адреса 
            <input name="addr_str" type="text" id = "faddr_str" size="70" value= "" data_old_value = ""/>
            </label> 
            <button type="button" class ="btnSel" id="btAddrSel"></button>      
            <button type="button" class ="btnClear" id="btAddrClear"></button>       
            </p>
        </div>    
        <div class="pane" >
                <button name="submitButton" type="submit" class ="btn" id="bt_edit" value="edit" >Записати</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_add" value="add" style="display:none;">Записати новий</button>                
                <button name="resetButton" type="button" class ="btn" id="bt_reset" value="bt_reset" >Відмінити</button>         
        </div>
            
      </FORM>
            
      </div>                


    <form id="fadr_sel_params" name="adr_sel_params" target="adr_win" method="post" action="adr_tree_selector.php">
        <input type="hidden" name="select_mode" id="fadr_sel_params_select_mode" value="1" />
        <input type="hidden" name="address" id="fadr_sel_params_address" value="" />
        <input type="hidden" name="full_addr_mode" id="ffull_addr_mode" value="1" />
    </form>

<div id="dialog-confirm" title="Удалить?" style="display:none;">
    <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
    <p id="dialog-text"> Удалить? </p></p>
</div>


<?php
print('<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>');




end_mpage();
?>