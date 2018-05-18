<?php

header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();
error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];

$gn1 = "lgt_family";

$gn1_table = $gn1."_table";
$gn1_pager = $gn1."_tablePager";

$lrel=     DbTableSelList($Link,'cli_family_rel_tbl','id','nm_rel');
$lrelselect=DbTableSelect($Link,'cli_family_rel_tbl','id','nm_rel');

$id_lgt = sql_field_val('id_lgt','int');
$fio = sql_field_val('fio_lgt','string');

$nmp1 = "АЕ-члени родини $fio"; 
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

print('<script type="text/javascript" src="abon_en_lgtfamily_old.js"></script> ');


echo <<<SCRYPT
<script type="text/javascript">
    
var lrel = $lrel;
var id_lgt = $id_lgt;
 
</script>
SCRYPT;

//middle_mpage(); // верхнее меню
?>

</head>
<body >

    
<DIV id="pmain_footer">
   <a href="javascript:void(0)" id="debug_ls1">show debug window</a> 
   <a href="javascript:void(0)" id="debug_ls2">hide debug window</a> 
   <a href="javascript:void(0)" id="debug_ls3">clear debug window</a>
</DIV>

<div id="grid_dform">    
<table id="<?php echo $gn1_table ?>" > </table>
<div id="<?php echo $gn1_pager ?>" ></div>
</div>    



        <div id="dialog_editlgtform" style="display:none; overflow:visible; ">        
        <form id="fFamilyEdit" name="fFamilyEdit" method="post" action="abon_en_lgtfamily_edit.php" >
          <div class="pane"  >
            <input name="id" type="hidden" id="fid" value="" />
            <input name="id_lgt" type="hidden" id="fid_lgt" value="<?php echo $id_lgt; ?>" />
            <input name="oper" type="hidden" id="foper" value="" />            
            <div class="pane"  >    
                <p>
                    <label>Член сім'ї 
                        <input name="fio" type="text" id = "ffio" size="50" value= "" data_old_value = ""/>
                    </label> 
                    
                    <input type="hidden"   name="lgt"  value="No" />                    
                    <label>
                        <input type="checkbox" name="lgt" id="flgt" value="Yes" data_old_checked = ""/>
                        Пільговик
                    </label>
                    
                </p>

                <p>            
                    <label>Дата народження
                        <input name="dt_birth" type="text" size="15" class="dtpicker" id ="fdt_birth" value= "" data_old_value = "" />
                    </label>

                    <label>Сімейні відносини
                        <select name="id_rel" size="1" id="fid_rel" value= "" data_old_value = "" >
                            <?php echo "$lrelselect" ?>;
                        </select>                    

                    </label> 
                </p>
            </div>    
            <div class="pane"  >    
                <p>
                    <label>Дата початку
                        <input name="dt_start" type="text" size="15" class="dtpicker" id ="flgtfamdt_b" value= "" data_old_value = "" />
                    </label>
                    <label>Дата закінчення
                        <input name="dt_end" type="text" size="15" class="dtpicker" id ="flgtfamdt_e" value= "" data_old_value = "" />
                    </label>

                </p>

            </div>    

        </div>    
        <div class="pane" >
                <button name="submitButton" type="submit" class ="btn" id="bt_edit" value="edit" >Записати</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_add" value="add" style="display:none;">Записати новий</button>                
                <button name="resetButton" type="button" class ="btn" id="bt_reset" value="bt_reset" >Відмінити</button>         
        </div>
            
      </FORM>
            
      </div>                
    
    <div id="dialog-confirm" title="" style="display:none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text"> </p></p>
    </div>


<?php
print('<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>');


end_mpage();
?>