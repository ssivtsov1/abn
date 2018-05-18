<?php

header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();
error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];
/*
if (isset($_SESSION['id_sess'])) {
  $ids = $_SESSION['id_sess'];

  $res_par = sel_par($ids);
  $row_par = pg_fetch_array($res_par);
  $cons = $row_par['con_str1'];
  //$Q = $row_par['qu'];
  $Link = pg_connect($cons);
}
*/


$nmp1 = "АЕ-Ідентифікатори прав доступа";
$cp1 = "Ідентифікатори прав доступа";
$gn1 = "syi_enviroment";

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

print('<script type="text/javascript" src="dov_enviroment.js"></script> ');

$laccesslist=DbTableSelList($Link,'syi_access_lvl','id','name');
$laccess=DbTableSelect($Link,'syi_access_lvl','id','name');

$r_edit = CheckLevel($Link,'адмін-групи прав',$session_user);

echo <<<SCRYPT
<script type="text/javascript">
var laccesslist = $laccesslist;
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
   <table id="dov_enviroment_table" style="margin:1px;"></table>
   <div id="dov_enviroment_tablePager"></div>
</div>    
    


        <div id="dialog_editform" style="display:none; overflow:visible; ">        
        <form id="fEdit" name="fEdit" method="post" action="dov_enviroment_edit.php" >
          <div class="pane"  >
            <input name="id" type="hidden" id="fid" value="" />
            <input name="oper" type="hidden" id="foper" value="" />            
            
            <label>Назва 
            <input name="name" type="text" id = "fname" size="60" value= "" data_old_value = ""/>
            </label> 
            <br>
            <label>Ідентифікатор
            <input name="ident" type="text" id = "fident" size="20" value= "" data_old_value = ""/>
            </label> 

            <label> Права
                <select name="default_rule" size="1" id="fdefault_rule">
                    <?php echo "$laccess" ?>;                        
                </select>                    
            </label>
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