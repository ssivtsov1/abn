<?php

header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();
error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];

$nmp1 = "АЕ-Дільниці кур'єрів";
  
start_mpage($nmp1); //заголовок
head_addrpage();  // хедер, подключение библиотек
$flag_cek = is_cek($Link);  //принадлежность РЭСа к ЦЭК

print('<link rel="stylesheet" type="text/css" href="css/ded_styles.css" /> ');
print('<link rel="stylesheet" type="text/css" href="css/layout-default-latest.css" /> ');
print('<script type="text/javascript" src="js/jquery.layout.min-latest.js"></SCRIPT>');
print('<script type="text/javascript" src="js/jquery.form.js"></script>');
print('<script type="text/javascript" src="js/jquery.validate.min.js?version='.$app_version.'" ></script>');
print('<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script> ');
print('<script type="text/javascript" src="js/jquery.ui.datepicker-uk.js"></script> ');
print('<script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script> ');
print('<script type="text/javascript" src="staff_list_sel.js?version='.$app_version.'"></script> ');

if($flag_cek==0){
    print('<script type="text/javascript" src="runner_sectors.js?version='.$app_version.'"></script> ');
}
else {
    print('<script type="text/javascript" src="runner_sectors_cek.js?version='.$app_version.'"></script> ');
}


$r_edit = CheckLevel($Link,'довідник-дільниці',$session_user);

$staff_dep= DbTableSelList($Link,'prs_department','id','name');

$lregion=      DbTableSelList($Link,'cli_region_tbl','id','name');
$lregionselect=DbTableSelect($Link,'cli_region_tbl','id','name');

echo <<<SCRYPT
<script type="text/javascript">
    var r_edit = $r_edit;
    var staff_dep = $staff_dep;
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

    <DIV id="pmain_center" style="padding:2px; margin:3px;">    
        <div id="pSectors_table" style="padding:2px; margin:3px;">    
            <table id="sectors_table" > </table>
            <div   id="sectors_tablePager" ></div>
        </div>    

        <div id="pAccnt_table" style="padding:2px; margin:3px;">    
            <table id="accnt_table" > </table>
            <div   id="accnt_tablePager" ></div>
        </div>    

    </div>        
    
        <div id="dialog_editform" style="display:none; overflow:visible; padding:1px; ">        
        <form id="fSectorEdit" name="fSectorEdit" method="post" action="runner_sectors_edit.php" >
          <div class="pane"  >
            <input name="id" type="hidden" id="fid" value="" />
            <input name="oper" type="hidden" id="foper" value="" />            
            <input name="change_date" type="hidden" id ="fchange_date" value="" />                            
            <div class="pane"  >    

                <p>            
                    <label>Назва
                        <input name="name" type="text" size="50" id ="fname" value= "" data_old_value = "" />
                    </label>
                    <label>Код
                        <input name="code" type="text" size="10" id ="fcode" value= "" data_old_value = "" />
                    </label>

                </p>
                
                <p>            
                    <input name="id_runner" type="hidden" id = "fid_runner" size="10" value= "" data_old_value = ""/>    
                    <label >Кур'єр/контролер(показники)
                        <input name="runner" type="text" id = "frunner" size="40" value= "" data_old_value = "" readonly />
                    </label> 
                    <button type="button" class ="btnSel" id="btRunnerSel"></button>       
                    <button type="button" class ="btnClear" id="btRunnerClear"></button>
                </p>

                <p>            
                    <input name="id_kontrol" type="hidden" id = "fid_kontrol" size="10" value= "" data_old_value = ""/>    
                    <label >Контролер
                        <input name="controler" type="text" id = "fcontroler" size="60" value= "" data_old_value = "" readonly />
                    </label> 
                    <button type="button" class ="btnSel" id="btKontrolSel"></button>  
                    <button type="button" class ="btnClear" id="btKontrolClear"></button>       
                </p>
                
                <p>            
                    <input name="id_operator" type="hidden" id = "fid_operator" size="10" value= "" data_old_value = ""/>    
                    <label >Оператор
                        <input name="operator" type="text" id = "foperator" size="60" value= "" data_old_value = "" readonly />
                    </label> 
                    <button type="button" class ="btnSel" id="btOperatorSel"></button>   
                    <button type="button" class ="btnClear" id="btOperatorClear"></button>       
                </p>
                
                <p>             
                    <label>Примітка
                        <input name="notes" type="text" size="80" id ="fnotes" value= "" data_old_value = "" />
                    </label>
                </p>
                <p> 
                    <label>Друк в порядку особових рахунків
                        <input type="checkbox" name="sort_flag" id="fsort_flag" value="1" data_old_checked = ""/>
                    </label>
                </p>
                 
                <p>
                    <label>Район
                        <select name="id_region" size="1" id = "fid_region"  value= "" data_old_value = "">
                            <?php echo "$lregionselect" ?>;
                        </select>                    
                    </label> 
                            
                    <label>Зона
                        <input name="zone" type="text" size="10" id ="fzone" value= "" data_old_value = "" />
                    </label>
                    
                </p>
                
            </div>    
            <div class="pane"  >    
                <p>
                    <label>Дата
                        <input name="dt_b" type="text" size="15" class="dtpicker" id ="fdt_b" value= "" data_old_value = "" />
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
    
      <div id="pAccnt_oper_table"  title="" style="display:none; margin:1px; padding:1px;" >    
            <table id="accnt_oper_table" > </table>
            <div   id="accnt_oper_tablePager" ></div>
       </div>    

       <div id="grid_selperson" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 100000;" > 
         <table id="person_sel_table" style="margin:1px;"></table>
         <div id="person_sel_tablePager"></div>
       </div>    
    
    
      <div id="dialog-confirm" title="" style="display:none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text"> </p></p>
      </div>

    <div id="dialog-changedate" title="Дата редагування" style="display:none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text">Вкажіть дату редагування </p></p>
        <br>
        <input name="date_change" type="text" size="20" class="dtpicker" id ="fdate_change"/>
    </div>

    <form id="fcntrl_sel_params" name="cntrl_sel_params" target="cntrl_win" method="post" action="staff_list.php">
        <input type="hidden" name="select_mode" value="1" />
    </form>
    
    
<?php

print('<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>');


end_mpage();
?>