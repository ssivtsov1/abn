<?php

header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();
error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];

$nmp1 = "АЕ-Списки оглядів/перевірок";
  
start_mpage($nmp1); //заголовок
head_addrpage();  // хедер, подключение библиотек

$Query=" select to_char(fun_mmgg(), 'DD.MM.YYYY') as mmgg;";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$mmgg = $row['mmgg'];


$Query=" select value_ident::int as value from syi_sysvars_tbl where ident='id_res'; ";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($lnk) );
$row = pg_fetch_array($result);
$id_res = $row['value'];


//$lioper=DbTableSelList($Link,'indic_opration','id','name');
//$lioperselect=DbTableSelect($Link,'indic_opration','id','name');

$lzones=DbTableSelList($Link,'eqk_zone_tbl','id','nm');
//$lzoneselect=DbTableSelect($Link,'eqk_zone_tbl','id','nm');

//$lindicoper =DbTableSelList($Link,'cli_indic_type_tbl','id','name');
//$lindicoperselect =DbTableSelect($Link,'cli_indic_type_tbl','id','name');
$staff_dep= DbTableSelList($Link,'prs_department','id','name');

$lworkselect=DbTableSelect($Link,'cli_works_tbl','id','name');
$lworktypes =DbTableSelList($Link,'cli_works_tbl','id','name');


$r_edit = CheckLevel($Link,'картка-роботи',$session_user);

print('<link rel="stylesheet" type="text/css" href="css/ded_styles.css" /> ');
print('<link rel="stylesheet" type="text/css" href="css/layout-default-latest.css" /> ');
print('<script type="text/javascript" src="js/jquery.layout.min-latest.js"></SCRIPT>');
print('<script type="text/javascript" src="js/jquery.form.js"></script>');
print('<script type="text/javascript" src="js/jquery.validate.min.js?version='.$app_version.'" ></script>');
print('<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script> ');
print('<script type="text/javascript" src="js/jquery.ui.datepicker-uk.js"></script> ');
print('<script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script> ');
print('<script type="text/javascript" src="js/spin.min.js"></script> ');
print('<script type="text/javascript" src="js/str_pad.js"></script>');
print('<script type="text/javascript" src="work_packs.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="runner_sectors_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="staff_list_sel.js?version='.$app_version.'"></script> ');

print('<script type="text/javascript" src="check_session.js?version='.$app_version.'"></script> ');

echo <<<SCRYPT
<script type="text/javascript">

    var lzones = $lzones;
    var mmgg = '{$mmgg}';        
    var r_edit = $r_edit; 
    
    var staff_dep = $staff_dep;     
    var id_res   = $id_res ;
    var lworktypes = $lworktypes    ;
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
        <a href="javascript:void(0)" id="show_peoples">Исполнители</a>                
    </DIV>

    <DIV id="pmain_center">    
        
        
        <div id="pHeaders_table" style="padding:2px; margin:3px;">    
            
        <div class="ui-corner-all ui-state-default" id="pActionBar"> 
            <label>Період
                <input name="mmgg" type="text" size="15" class="dtpicker" id ="fmmgg" value= "" />
            </label>
            &nbsp;&nbsp;
            <button type="button" class ="btn btnSmall" id="bt_sel">Вибрати</button>
        </div>
            
            <table id="headers_table" > </table>
            <div   id="headers_tablePager" ></div>
        </div>    

        <div id="pIndic_table" style="padding:2px; margin:3px;">    
            <table id="indic_table" > </table>
            <div   id="indic_tablePager" ></div>
        </div>    

    </DIV>
    
        <div id="dialog_editform" style="display:none; overflow:visible; padding:1px; ">        
        <form id="fHeaderEdit" name="fHeaderEdit" method="post" action="work_packs_edit.php" >
          <div class="pane"  >
            <input name="id" type="hidden" id="fid" value="" /> 
            <input name="oper" type="hidden" id="foper" value="" />            
            <input name="change_date" type="hidden" id ="fchange_date" value="" />                            
            <input name="work_period" type="hidden" id ="fwork_period" value="" />                            

            <div class="pane"  >    

            <p>      
                <label>Тип роботи &nbsp &nbsp&nbsp &nbsp 
                    <select name="idk_work" size="1" id="fidk_work" value= "" data_old_value = "" >
                        <?php echo "$lworkselect" ?>;
                    </select>                    
                </label>      
                
                <label>Дата роботи 
                    <input name="dt_work" type="text" size="10" class="dtpicker" id ="fdt_work" value= "" data_old_value = "" />
                </label>
                <!--
                <label>Період
                    <input name="work_period" type="text" size="10" class="dtpicker" id ="fwork_period" value= "" data_old_value = "" />
                </label>
                -->
            </p>
            
            <p>  
                <input name="id_position" type="hidden" id = "fid_position" size="10" value= "" data_old_value = ""/>    
                <label> Виконавець
                    <input name="position" type="text" id = "fposition" size="50" value= "" data_old_value = "" readonly />
                </label> 
                <button type="button" class ="btnSel" id="btCntrlSel"></button>      
                <!--
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <label>№ акта
                    <input name="act_num" type="text" size="15"  id ="fact_num" value= "" data_old_value = "" />
                </label> -->
            </p>
            
            <p> Примітка <br/>
                <textarea  style="height: 20px" cols="70" name="note" id="fnote" data_old_value = "" ></textarea>
            </p>
             <div class="pane"  >
                Критерій вибору абонентів:
                <p>            
                     <label >Книга
                        <input name="book" type="text" id = "fbook" size="10" value= "" data_old_value = ""  />
                    </label> 
                </p>
                
                <p>            
                    <input name="id_sector" type="hidden" id = "fid_sector" size="10" value= "" data_old_value = ""/>    
                    <label >Дільниця
                        <input name="sector" type="text" id = "fsector" size="40" value= "" data_old_value = "" tabindex="-1" readonly />
                    </label> 
                    <button type="button" class ="btnSel" id="btSectorSel"></button>
                    <button type="button" class ="btnClear" id="btSectorClear"></button>
                </p>
                
                <p>    
                    <input name="addr" type="hidden" id = "faddr" size="10" value= "" data_old_value = ""/>    
                    <label>Адреса 
                        <input name="addr_str" type="text" id = "faddr_str" size="70" value= "" data_old_value = ""  readonly />
                    </label> 
                    <button type="button" class ="btnSel" id="btAddrSel"></button>      
                    <button type="button" class ="btnClear" id="btAddrClear"></button>
                </p>
            </div>        
            </div>    

        </div>    
        <div class="pane" >
                <button name="submitButton" type="submit" class ="btn" id="bt_edit" value="edit" >Записати</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_add" value="add" style="display:none;">Записати новий</button>
                <button name="resetButton" type="button" class ="btn" id="bt_reset" value="bt_reset" >Відмінити</button> 
                &nbsp;&nbsp;
                <span id="lwait" style="color:navy; display:none; "> Зачекайте закінчення операції ... </span>
        </div>
            
      </FORM>
            
      </div>  
    
    
      <div id="dialog-confirm" title="" style="display:none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text"> </p></p>
      </div>
    
    <div id="grid_selperson" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 100000;" > 
         <table id="person_sel_table" style="margin:1px;"></table>
         <div id="person_sel_tablePager"></div>
    </div>    


    <div id="dialog-changedate" title="Дата" style="display:none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text">Вкажіть дату редагування </p></p>
        <br>
        <input name="date_change" type="text" size="20" class="dtpicker" id ="fdate_change"/>
    </div>

    <form id="fcntrl_sel_params" name="cntrl_sel_params" target="cntrl_win" method="post" action="staff_list.php">
        <input type="hidden" name="select_mode" value="1" />
        <input type="hidden" name="id_person" id="fcntrl_sel_params_id_cntrl" value="0" />        
    </form>
    
     <div id="grid_selsector" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 100000;" >   
            <table id="sectors_sel_table" style="margin:1px;"></table>
            <div id="sectors_sel_tablePager"></div>
     </div>       

    <form id="fadr_sel_params" name="adr_sel_params" target="adr_win" method="post" action="adr_tree_selector.php">
        <input type="hidden" name="select_mode" id="fadr_sel_params_select_mode" value="1" />
        <input type="hidden" name="address" id="fadr_sel_params_address" value="" />
        <input type="hidden" name="full_addr_mode" id="ffull_addr_mode" value="0" />
    </form>
    
    
<div id="calc_progress" style ="" > 
    <p> Програма працює... </p>
    
    <div id ="progress_indicator" style="position:absolute; top:50px; left:50%"> </div>
    <div id ="progress_time" style="position:absolute; height:20px;  top:80px; left:40%"> </div>
</div>

    
    
<?php

print('<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>');


end_mpage();
?>