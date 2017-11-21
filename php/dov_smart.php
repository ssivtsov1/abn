<?php

header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();
error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];

$nmp1 = "АЕ-Будинки СМАРТ";
$cp1 = "Перелік будинків зі СМАРТ";


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

print('<script type="text/javascript" src="dov_smart.js"></script> ');

$r_edit = CheckLevel($Link,'довідник-смарт',$session_user);

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
<table id="dov_smart_table"> </table>
<div   id="dov_smart_tablePager" ></div>
</div>    

        <div id="dialog_editform" style="display:none; overflow:visible; ">        
        <form id="fEdit" name="fEdit" method="post" action="dov_smart_edit.php" >
          <div class="pane"  >
            <input name="id" type="hidden" id="fid" value="" />
            <input name="oper" type="hidden" id="foper" value="" />            

            <label>Ідентифікатор
            <input name="ident" type="text" id = "fident" size="10" value= "" data_old_value = ""/>
            </label> 

            <label>Папка 
            <input name="path" type="text" id = "fpath" size="20" value= "" data_old_value = ""/>
            </label> 
            
            <label>Файл 
            <input name="name" type="text" id = "fname" size="20" value= "" data_old_value = ""/>
            </label> 

            <br>

            <input name="addr" type="hidden" id = "faddr" size="10" value= "" data_old_value = ""/>    
            <label>Адреса 
            <input name="addr_str" type="text" id = "faddr_str" size="70" value= "" data_old_value = ""/>
            </label> 
            <button type="button" class ="btnSel" id="btAddrSel"></button>      
            <br>

            <label>Книга/особовий рахунок &nbsp &nbsp
            <input name="book" type="text" id = "fbook" size="5" value= "" data_old_value = ""/>
            </label> 
            <label> /
            <input name="code" type="text" id = "fcode" size="10" value= "" data_old_value = ""/>            
            </label>                 
            
        </div>    
        <div class="pane" >
                <button name="submitButton" type="submit" class ="btn" id="bt_edit" value="edit" >Записати</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_add" value="add" style="display:none;">Записати новий</button>                
                <button name="resetButton" type="button" class ="btn" id="bt_reset" value="bt_reset" >Відмінити</button>         
        </div>
            
      </FORM>
            
      </div>                

    <form id="fadr_sel_params" name="adr_sel_params" target="adr_win" method="post" action="adr_tree_selector.php">
        <input type="hidden" name="select_mode" value="1" />
        <input type="hidden" name="address" id="fadr_sel_params_address" value="" />
    </form>

    <div id="dialog-confirm" title="" style="display:none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text"> </p></p>
    </div>

<?php
print('<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>');



end_mpage();
?>