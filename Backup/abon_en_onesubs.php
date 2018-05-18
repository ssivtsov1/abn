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
      
}
$nm1 = "АЕ-субсидія абонента $paccnt_info";

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

print('<script type="text/javascript" src="abon_en_onesubs.js?version='.$app_version.'"></script> ');

$r_edit = CheckLevel($Link,'субсидія',$session_user);

?>
<style type="text/css"> 
#pmain_content {padding:3}

.ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default {  color: #111111; }

</style>   


<script type="text/javascript">

var id_paccnt = <?php echo "$id_paccnt" ?>;
var paccnt_info = <?php echo "'$paccnt_info'" ?>; 
var r_edit = <?php echo "$r_edit" ?>; 
  
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

    <DIV id="pmain_content" style="margin:1px;padding:1px;">

        <div id="psubs_list" style="margin:1px;padding:1px;" > 
            <table id="subs_table" style="margin:1px;padding:1px;"></table>
            <div id="subs_tablePager"></div>
        </div>

    </DIV>

        <div id="dialog_editform" style="display:none; overflow:visible; ">        
        <form id="fSubsEdit" name="fSubsEdit" method="post" action="abon_en_onesubs_edit.php" >
          <div class="pane"  >
            <input name="id" type="hidden" id="fid" value="" />
            <input name="oper" type="hidden" id="foper" value="" />    
            <input name="id_paccnt" type="hidden" id="fid_paccnt" value="" /> 
            <div class="pane"  >
                
            <p>            
                <label>Місяць надходження
                    <input name="mmgg" type="text" size="15" value="" id="fmmgg"  data_old_value = "" readonly />
                </label>

                <label>Період з
                    <input name="dt_b" type="text" size="15" value="" id="fdt_b"  data_old_value = "" readonly />
                </label>

                <label>по
                    <input name="dt_e" type="text" size="15" value="" id="fdt_e"  data_old_value = "" readonly />
                </label>
                
            </p>
            <p>                                        
               <label>Сума субсидії
                    <input name="sum_subs" type="text" id = "fsum_subs" size="10" value= "" data_old_value = "" readonly />
               </label> 
 
               <label>Доплата субсидії
                    <input name="sum_recalc" type="text" id = "fsum_recalc" size="10" value= "" data_old_value = "" readonly />
               </label> 
            <p>                            

            <p>                                        
               <label>Обов'язкова плата
                    <input name="ob_pay" type="text" id = "fob_pay" size="10" value= "" data_old_value = ""/>
               </label> 
 
               <label>Кількість осіб
                    <input name="kol_subs" type="text" id = "fkol_subs" size="10" value= "" data_old_value = ""/>
               </label> 
            <p>                            
                
            </div>

          </div>    

        <div class="pane" >
                <button name="submitButton" type="submit" class ="btn" id="bt_edit" value="edit" >Записати</button>
<!--                <button name="submitButton" type="submit" class ="btn" id="bt_add" value="add" style="display:none;">Записати новий</button>   -->             
                <button name="resetButton" type="button" class ="btn" id="bt_reset" value="bt_reset" >Відмінити</button>         
        </div>
            
      </FORM>
            
      </div>           
    
<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>

<div id="dialog-confirm" title="Удалить?" style="display:none;">
    <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
    <p id="dialog-text"> Продолжить? </p></p>
</div>


<?php

end_mpage();
?>


