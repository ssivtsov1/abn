<?php

header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();
error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];

$nmp1 = "АЕ-Відомості зняття показань";
  
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


$lioper=DbTableSelList($Link,'indic_opration','id','name');
$lioperselect=DbTableSelect($Link,'indic_opration','id','name');

$lzones=DbTableSelList($Link,'eqk_zone_tbl','id','nm');
$lzoneselect=DbTableSelect($Link,'eqk_zone_tbl','id','nm');

$lindicoper =DbTableSelList($Link,'cli_indic_type_tbl','id','name');
$lindicoperselect =DbTableSelect($Link,'cli_indic_type_tbl','id','name');
$staff_dep= DbTableSelList($Link,'prs_department','id','name');

$r_edit = CheckLevel($Link,'показники',$session_user);

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
print('<script type="text/javascript" src="ind_packs.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="runner_sectors_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="staff_list_sel.js?version='.$app_version.'"></script> ');

print('<script type="text/javascript" src="check_session.js?version='.$app_version.'"></script> ');

echo <<<SCRYPT
<script type="text/javascript">
    var lioper = $lioper;
    var lzones = $lzones;
    var mmgg = '{$mmgg}';        
    var r_edit = $r_edit; 
    var lindicoper =$lindicoper; 
    var staff_dep = $staff_dep;     
    var id_res   = $id_res 
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
        <form id="fHeaderEdit" name="fHeaderEdit" method="post" action="ind_packs_edit.php" >
          <div class="pane"  >
            <input name="id_pack" type="hidden" id="fid" value="" />
            <input name="oper" type="hidden" id="foper" value="" />            
            <input name="change_date" type="hidden" id ="fchange_date" value="" />                            
            <input name="work_period" type="hidden" id ="fwork_period" value="" />                            

            <div class="pane"  >    

                <p>            
                    <label>Номер відомості.
                        <input name="num_pack" type="text" id = "fnum_pack" size="20" class="enterastab" value= "" data_old_value = ""/>
                    </label> 
                    <label>Дата 
                        <input name="dt_pack" type="text" size="12" value="" id="fdt_pack" class="dtpicker enterastab" data_old_value = "" />
                    </label>

                </p>
                
                <p>            
                    <input name="id_sector" type="hidden" id = "fid_sector" size="10" value= "" data_old_value = ""/>    
                    <label >Дільниця
                        <input name="sector" type="text" id = "fsector" size="60" value= "" data_old_value = "" tabindex="-1" readonly />
                    </label> 
                    <button type="button" class ="btnSel" id="btSectorSel"></button>       
                </p>
                
                <p>            
                    <input name="id_runner" type="hidden" id = "fid_runner" size="10" value= "" data_old_value = ""/>    
                    <input name="operator" type="hidden" id = "foperator" size="10" value= "" data_old_value = ""/>                        
                    <label >Кур'єр / контролер
                        <input name="runner" type="text" id = "frunner" size="60" value= "" data_old_value = "" tabindex="-1" readonly />
                    </label> 
                    <button type="button" class ="btnSel" id="btRunnerSel"></button>       
                </p>
 
                <p> 
                    <label>Робота
                        <select name="id_ioper" size="1" id = "fid_ioper" size="10" class="enterastab" value= "" data_old_value = "">
                            <?php echo "$lioperselect" ?>;
                        </select>                    
                    </label> 
                </p>
                
            </div>    

        </div>    
        <div class="pane" >
                <button name="submitButton" type="submit" class ="btn" id="bt_edit" value="edit" >Записати</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_add" value="add" style="display:none;">Записати новий</button>
                <button name="resetButton" type="button" class ="btn" id="bt_reset" value="bt_reset" >Відмінити</button> 
                &nbsp;&nbsp;
                <button name="submitButton" type="submit" class ="btn" id="bt_refresh" value="refresh" >Оновити відомість</button>
                &nbsp;&nbsp;                
                <span id="lwait" style="color:navy; display:none; "> Зачекайте закінчення операції ... </span>
        </div>
            
      </FORM>
            
      </div>  
    

        <div id="dialog_indicform" style="display:none; overflow:visible; padding:1px; ">        
        <form id="fIndicEdit" name="fIndicEdit" method="post" action="ind_packs_indic_one_edit.php" >
          <div class="pane"  >
            <input name="id" type="hidden" id="fid" value="" />
            <input name="id_pack" type="hidden" id="fid_pack" value="" />
            <input name="oper" type="hidden" id="foper" value="" />            

            <div class="pane"  >    

                <p>            
                    <label>Показники по 
                        <input name="book" type="text" id = "fbook" size="5" value= "" tabindex="-1" readonly /> / 
                        <input name="code" type="text" id = "fcode" size="8" value= "" tabindex="-1" readonly /> <br/>
                        <input name="address" type="text" id = "faddress" size="50" value= "" tabindex="-1" readonly /> &nbsp;
                        <input name="abon" type="text" id = "fabon" size="50" value= "" tabindex="-1" readonly /> 
                    </label> 
                </p>

                <p>            
                    <label> Лічильник
                        <input name="num_meter" type="text" id = "fnum_meter" size="15" value= "" tabindex="-1" readonly /> / 
                    </label> 
                    
                    <label>Зона
                        <select name="id_zone" size="1" id = "fid_zone" size="10" value= "" tabindex="-1" disabled >
                            <?php echo "$lzoneselect" ?>;
                        </select>                    
                    </label> 
                    
                </p>
                
                <p>       
                    <label>Попередні показники
                        <input name="p_indic" type="text" id = "fp_indic" size="12" value= "" tabindex="-1" readonly />
                    </label> 
                    <label>Дата попередніх
                        <input name="dt_p_indic" type="text" size="12" value="" id="fdt_p_indic" class="dtpicker"  tabindex="-1" readonly />
                    </label>
                </p>

                <p>       
                    <label><b>Поточні показники </b>&nbsp;&nbsp
                        <input name="indic" type="text" id = "findic" size="12" value= ""  data_old_value = "" />
                    </label> 
                    <label><b>Дата </b> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <input name="dt_indic" type="text" size="12" value="" id="fdt_indic" class="dtpicker"  data_old_value = "" />
                    </label>

                    <label><b>Спожито</b>
                        <input name="demand" type="text" id = "fdemand" size="12" value= "" readonly />
                    </label> 
                    
                </p>

                <p>       
                    <label>Походження показників
                        <select name="id_operation" size="1" id = "fid_operation" size="10" value= "" tabindex="-1" >
                            <?php echo "$lindicoperselect" ?>;
                        </select>                    
                    </label> 
                </p>                
                <p>                                
                    <label><b>Реальні показники (якщо відрізняються) </b>&nbsp;&nbsp
                        <input name="indic_real" type="text" id = "findic_real" size="12" value= ""  data_old_value = "" />
                    </label> 
                </p>                
                <p>                                
                    <label>Показники завів
                        <input name="user_name" type="text" id = "fuser_name" size="30" value= ""  data_old_value = "" readonly />
                    </label> 
                    <label>Час заведення
                        <input name="dt_input" type="text" id = "fdt_input" size="20" value= ""  data_old_value = "" readonly />
                    </label> 
                </p>                
                
            </div>    

        </div>    
        <div class="pane" >
                <button name="submitButton" type="submit" class ="btn" id="bt_edit" value="edit" >Записати</button>
                <button name="resetButton" type="button" class ="btn" id="bt_reset" value="bt_reset" >Відмінити</button>         
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
  
<div id="calc_progress" style ="" > 
    <p> Програма працює... </p>
    
    <div id ="progress_indicator" style="position:absolute; top:50px; left:50%"> </div>
    <div id ="progress_time" style="position:absolute; height:20px;  top:80px; left:40%"> </div>
</div>

    
<?php

print('<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>');


end_mpage();
?>