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
   $nmp1 = "АЕ-Категорії пільг вибір";
}
else
{
   $selmode = 0; 
   $nmp1 = "АЕ-Категорії пільг";
    
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

 
print('<script type="text/javascript" src="lgt_list.js?version='.$app_version.'"></script> ');

$lgi_grp_state= DbTableSelList($Link,'lgi_grp_state_tbl','id','name');
$lgi_budjet=DbTableSelList($Link,'lgi_budjet_tbl','id','name');
$lgi_doc=DbTableSelList($Link,'lgi_document_tbl','id','name');
$lgi_docselect=DbTableSelect($Link,'lgi_document_tbl','id','name');
$lgi_budjetselect=DbTableSelect($Link,'lgi_budjet_tbl','id','name');
$lgi_grp_stateselect= DbTableSelect($Link,'lgi_grp_state_tbl','id','name');

$lgi_tar_grp= DbTableSelList($Link,'lgi_tar_grp_tbl','id','name');
$lgi_tar_grpselect= DbTableSelect($Link,'lgi_tar_grp_tbl','id','name');

$lgi_calc= DbTableSelList($Link,'lgi_calc_header_tbl','id','name');
$lgi_calcselect= DbTableSelect($Link,'lgi_calc_header_tbl','id','name');

$r_edit = CheckLevel($Link,'довідник-пільги',$session_user);

echo <<<SCRYPT
<script type="text/javascript">
var lgi_grp_state = $lgi_grp_state;
var lgi_budjet = $lgi_budjet;
var lgi_doc = $lgi_doc;
var lgi_tar_grp = $lgi_tar_grp;
var lgi_calc = $lgi_calc;
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

    </div>        
    
        <div id="dialog_editform" style="display:none; overflow:visible; padding:1px; ">        
        <form id="fLgtEdit" name="fLgtEdit" method="post" action="lgt_list_grp_edit.php" >
          <div class="pane"  >
            <input name="id" type="hidden" id="fid" value="" />
            <input name="oper" type="hidden" id="foper" value="" />           
            <input name="change_date" type="hidden" id ="fchange_date" value="" />                
            <div class="pane"  >    

                <p>            
                    <label>Пільга
                        <input name="name" type="text" size="90" id ="fname" value= "" data_old_value = "" />
                    </label>
                </p>
                <p>
                    <label>Код
                        <input name="ident" type="text" size="10" id ="fident" value= "" data_old_value = "" />
                    </label>

                    <label>Додат. код
                        <input name="alt_code" type="text" size="10" id ="falt_code" value= "" data_old_value = "" />
                    </label>

                    <label>КФК код
                        <input name="kfk_code" type="text" size="10" id ="fkfk_code" value= "" data_old_value = "" />
                    </label>
                </p>
                
                <p>            
                    
                    <label>  Назва для рахунку
                        <input name="bill_name" type="text" size="40" id ="fbill_name" value= "" data_old_value = "" />
                    </label>
                    
                </p>
                
                <p>            
                    <input name="id_kategor" type="hidden" id = "fid_kategor" size="10" value= "" data_old_value = ""/>    
                    <label >Категорія 
                        <input name="kategor" type="text" id = "fkategor" size="70" value= "" data_old_value = ""/>
                    </label> 
                    <button type="button" class ="btnSel" id="btCategorSel"></button>       
                    <button type="button" class ="btnClear" id="btCategorClear"></button>                       
                </p>

                <p>
                    <label> Бюджет
                        <select name="id_budjet" size="1" id="fid_budjet" value= "" data_old_value = "" >
                            <?php echo "$lgi_budjetselect" ?>;
                        </select>                    
                    </label> 


                    <label> Документ
                        <select name="id_document" size="1" id="fid_document" value= "" data_old_value = "" >
                            <?php echo "$lgi_docselect" ?>;
                        </select>                    
                    </label> 
                </p>

                <p>
                    <label> Статус
                        <select name="id_state" size="1" id="fid_state" value= "" data_old_value = "" >
                            <?php echo "$lgi_grp_stateselect" ?>;
                        </select>                    
                    </label> 

                </p>

                <p>
                    <label> Метод розрахунку пільги
                        <select name="id_calc" size="1" id="fid_calc" value= "" data_old_value = "" >
                            <?php echo "$lgi_calcselect" ?>;
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

    <div id="dialog-changedate" title="Дата" style="display:none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text">Вкажіть дату редагування </p></p>
        <br>
        <input name="date_change" type="text" size="20" class="dtpicker" id ="fdate_change"/>
    </div>
    
    
      <form id="flgtcatsel_params" name="lgtcatsel_params" target="lgtcat_win" method="post" action="lgt_category.php">
        <input type="hidden" name="select_mode" value="1" />
      </form>
    
<?php

print('<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>');


end_mpage();
?>