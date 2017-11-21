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
   $nmp1 = "АЕ-Способи розрахунку пільг вибір";
}
else
{
   $selmode = 0; 
   $nmp1 = "АЕ-Способи розрахунку пільг";
    
}    

start_mpage($nmp1); //заголовок
head_addrpage();  // хедер, подключение библиотек

print('<link rel="stylesheet" type="text/css" href="css/ded_styles.css" /> ');
print('<link rel="stylesheet" type="text/css" href="css/layout-default-latest.css" /> ');
print('<script type="text/javascript" src="js/jquery.layout.min-latest.js"></SCRIPT>');
print('<script type="text/javascript" src="js/jquery.form.js"></script>');
print('<script type="text/javascript" src="js/jquery.validate.min.js?version='.$app_version.'" ></script>');
print('<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script> ');
print('<script type="text/javascript" src="js/jquery.ui.datepicker-uk.js"></script> ');
print('<script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script> ');


print('<script type="text/javascript" src="lgt_calc.js?version='.$app_version.'"></script> ');

//$lgi_grp_state= DbTableSelList($Link,'lgi_grp_state_tbl','id','name');
//$lgi_budjet=DbTableSelList($Link,'lgi_budjet_tbl','id','name');
//$lgi_doc=DbTableSelList($Link,'lgi_document_tbl','id','name');
//$lgi_docselect=DbTableSelect($Link,'lgi_document_tbl','id','name');
$lgi_calctype=DbTableSelList($Link,'lgk_calc_type_tbl','id','name');
$lgi_calctypeselect= DbTableSelect($Link,'lgk_calc_type_tbl','id','name');

$lgi_tar_grp= DbTableSelList($Link,'lgi_tar_grp_tbl','id','name');
$lgi_tar_grpselect= DbTableSelect($Link,'lgi_tar_grp_tbl','id','name');

$r_edit = CheckLevel($Link,'довідник-способи розрахунку пільг',$session_user);
echo <<<SCRYPT
<script type="text/javascript">
//var lgi_grp_state = $lgi_grp_state;
//var lgi_budjet = $lgi_budjet;
var lgi_calctype = $lgi_calctype;
var lgi_tar_grp = $lgi_tar_grp;
var r_edit = $r_edit;             
var selmode = $selmode;    
    
var tar_hidden;    
if (selmode==1)
{
   tar_hidden = true;
}
else
{
   tar_hidden = false;
}
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

    <DIV id="pmain_center" style="padding:2px; margin:3px;">    
        <div id="pLgtGrp_table" style="padding:2px; margin:3px;">    
            <table id="lgt_grp_table" > </table>
            <div   id="lgt_grp_tablePager" ></div>
        </div>    

        <div id="pLgtNorm_table" style="padding:2px; margin:3px;">    
            <table id="lgt_norm_table" > </table>
            <div   id="lgt_norm_tablePager" ></div>
        </div>    

    </div>        
    
        <div id="dialog_editform" style="display:none; overflow:visible; padding:1px; ">        
        <form id="fLgtEdit" name="fLgtEdit" method="post" action="lgt_calc_grp_edit.php" >
          <div class="pane"  >
            <input name="id" type="hidden" id="fid" value="" />
            <input name="oper" type="hidden" id="foper" value="" />            
            <div class="pane"  >    

                <p>            
                    <label>Назва
                        <input name="name" type="text" size="60" id ="fname" value= "" data_old_value = "" />
                    </label>

                    <label>Код
                        <input name="ident" type="text" size="20" id ="fident" value= "" data_old_value = "" />
                    </label>
                </p>
                
                <p>
                    <label> Метод розрахунку пільги
                        <select name="id_calc_type" size="1" id="fid_calc_type" value= "" data_old_value = "" >
                            <?php echo "$lgi_calctypeselect" ?>;
                        </select>                    
                    </label> 

                </p>

            </div>    
            <div class="pane"  >    
                <p>
                    <label>Дата початку
                        <input name="dt_b" type="text" size="15" class="dtpicker" id ="flgtdt_b" value= "" data_old_value = "" />
                    </label>
                    <label>Дата закінчення
                        <input name="dt_e" type="text" size="15" class="dtpicker" id ="fdt_e" value= "" data_old_value = "" />
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

      <form id="flgtcatsel_params" name="lgtcatsel_params" target="lgtcat_win" method="post" action="lgt_category.php">
        <input type="hidden" name="select_mode" value="1" />
      </form>
    
<?php

print('<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>');


end_mpage();
?>