<?php

header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();
error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];

$nmp1 = "АЕ-Опал.період";
$cp1 = "Опалювальні періоди";
$gn1 = "dov_heat";
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

print('<script type="text/javascript" src="dov_heat.js?version='.$app_version.'"></script> ');

$r_edit = CheckLevel($Link,'довідник-опалювальні періоди',$session_user);
$lregion=      DbTableSelList($Link,'cli_region_tbl','id','name');
$lregionselect=DbTableSelect($Link,'cli_region_tbl','id','name');

echo <<<SCRYPT
<script type="text/javascript">
    var r_edit = $r_edit;       
    var lregion = $lregion;      
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
        <form id="fEdit" name="fTpEdit" method="post" action="dov_heat_edit.php" >
          <input name="id" type="hidden" id="fid" value="" />
          <input name="oper" type="hidden" id="foper" value="" />            
            
          <div class="pane"  >
            <p>
                <label>Дата початку
                    <input name="dt_b" type="text" size="12" value="" id="fdt_b" class="dtpicker" data_old_value = "" />
                </label>                

                <label>Дата закінчення
                    <input name="dt_e" type="text" size="12" value="" id="fdt_e" class="dtpicker" data_old_value = "" />
                </label>                
            <p/>
            <p>
                   <label>Регіон
                        <select name="id_region" size="1" id = "fid_region"  value= "" data_old_value = "">
                            <?php echo "$lregionselect" ?>;
                        </select>                    
                    </label> 
            </p>
            <p>            
                    <label>Для багатоповерхових
                        <input name="floor" type="text" size="5" id ="ffloor" value= "" data_old_value = "" />
                    </label>
            </p>
           
            <br/>
              <textarea  style="height: 20px" cols="70" name="note" id="fnote" data_old_value = "" ></textarea>
            
        </div>    
        <div class="pane" >
                <button name="submitButton" type="submit" class ="btn" id="bt_edit" value="edit" >Записати</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_add" value="add" style="display:none;">Записати новий</button>                
                <button name="resetButton" type="button" class ="btn" id="bt_reset" value="bt_reset" >Відмінити</button>         
        </div>
            
      </FORM>
            
      </div>                

<div id="dialog-confirm" title="Удалить?" style="display:none;">
    <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
    <p id="dialog-text"> Удалить? </p></p>
</div>


<?php
print('<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>');




end_mpage();
?>