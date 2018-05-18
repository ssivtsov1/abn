<?php

header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();
error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];

$nmp1 = "АЕ-Журнал довідок";
  
start_mpage($nmp1); //заголовок
head_addrpage();  // хедер, подключение библиотек

$Query=" select to_char(fun_mmgg(), 'DD.MM.YYYY') as mmgg;";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$mmgg = $row['mmgg'];

//$lzones=DbTableSelList($Link,'eqk_zone_tbl','id','nm');
//$lzoneselect=DbTableSelect($Link,'eqk_zone_tbl','id','nm');

//$lindicoper =DbTableSelList($Link,'cli_indic_type_tbl','id','name');
//$lindicoperselect =DbTableSelect($Link,'cli_indic_type_tbl','id','name');
//$staff_dep= DbTableSelList($Link,'prs_department','id','name');

//$r_edit = CheckLevel($Link,'показники',$session_user);
$r_edit = 3;
$town_hidden=CheckTownInAddrHidden($Link);
 
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
print('<script type="text/javascript" src="dovid_list.js?version='.$app_version.'"></script> ');
//print('<script type="text/javascript" src="runner_sectors_sel.js?version='.$app_version.'"></script> ');
//print('<script type="text/javascript" src="staff_list_sel.js?version='.$app_version.'"></script> ');

print('<script type="text/javascript" src="check_session.js?version='.$app_version.'"></script> ');

$ltype= DbTableSelList($Link,'rep_sprav_types_tbl','id','name');

echo <<<SCRYPT
<script type="text/javascript">

    var mmgg = '{$mmgg}';        
    var r_edit = $r_edit; 
    var town_hidden = $town_hidden;
    var ltype = $ltype;        

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
                   
        <div class="ui-corner-all ui-state-default" id="pActionBar"> 
            <label>Період
                <input name="mmgg" type="text" size="15" class="dtpicker" id ="fmmgg" value= "" />
            </label>
            &nbsp;&nbsp;
            <button type="button" class ="btn btnSmall" id="bt_sel">Вибрати</button>
        </div>

        <div id="pDovid_table" style="padding:2px; margin:3px;">    
            <table id="dovid_table" > </table>
            <div   id="dovid_tablePager" ></div>
        </div>    

    </DIV>
    
    
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

    
 <form id="freps_params" name="reps_params" method="post" action="spravka_main_build_html.php" target="_blank" style="display:none;" >
        
        <DIV id="preps_header">
            <input type="hidden" name="template_name" id="ftemplate_name" value="" />      
            <input type="hidden" name="oper" id="foper" value="" />      
            <p>
                Період
                <input  name="period_str" type="text" id = "fperiod_str" size="30" value= "" readonly />
                З  
                <input name="dt_b" type="text" size="10" class="dtpicker" id ="fdt_b" value= "" />
                По     
                <input name="dt_e" type="text" size="10" class="dtpicker" id ="fdt_e" value= "" />                
                &nbsp;&nbsp;                
                <input type="hidden" name="to_xls2" id="fxls2" value="0" />  
            </p>
            <p>
                <label>Номер довідки &nbsp &nbsp
                    <input name="num_sp" type="text" size="10" id ="fnum_sp" value= "" />
                </label> 
                <label>Дата видачі довідки &nbsp &nbsp
                    <input name="dt_sp" type="text" size="10" class="dtpicker" id ="fdt_sp" value= "" />
                </label> 
            </p>
            <p>
                <input name="id_paccnt" type="hidden" id = "fid_paccnt" size="10" value= "" data_old_value = ""/>    
            </p>            

            <p>
                <label>Номер запиту &nbsp &nbsp
                    <input name="num_input" type="text" size="10" id ="fnum_input" value= "" />
                </label> 

                <label>Дата запиту &nbsp &nbsp
                    <input name="dt_input" type="text" size="10" class="dtpicker" id ="fdt_input" value= "" />
                </label> 
            </p>
            <p>            
                <label>Кількість членів родини 
                    <input name="people_count" type="text" size="10" id ="fpeople_count" value= "" />
                </label> 
            </p>
            
            <p>            
                <label>Опалювальна площа 
                    <input name="heat_area" type="text" size="10" id ="fheat_area" value= "" />
                </label> 
            </p>                

            <p>            
                <label>Соціальний норматив користування послугами з електропостачання для субсидії
                    <input name="social_norm" type="text" size="10" id ="fsocial_norm" value= "" />
                </label> 
            </p>                
            
            <p>                  
              <label><input type="hidden" name="hotw" id="fhotw" value="1" /> 
               Наявність гарячої води  </label> 
            </p>            
            <p>                        
              <label><input type="hidden" name="hotw_gas" id="fhotw_gas" value="1" /> 
               Наявність газового водонагрівача  </label> 
            </p>            
            <p>                        
              <label><input type="hidden" name="coldw" id="fcoldw" value="1" /> 
               Наявність холодної води  </label> 
            </p>            

            <p>                        
              <label><input type="hidden" name="plita" id="fplita" value="1" /> 
               стаціонарна електроплита  </label> 
            </p>            
            
            <p>                        
              <label><input type="hidden" name="show_dte" id="fshow_dte" value="1" /> 
               Друк боргу на кінець місяця  </label> 
            </p>            

            <p>            
                <input name="id_person" type="hidden" id = "fid_person" size="10" />                             
                <label>Оператор
                  <input name="person" type="text" id = "fperson" size="40" data_old_value = ""/>
                </label> 
            </p>
            <p>                        
              <label><input type="hidden" name="show_norm" id="fshow_norm" value="0"  /> 
               Показувати пільгову норму споживання </label> 
            </p>            
            <p>                        
              <label><input type="hidden" name="write_protocol" id="fwrite_protocol" value="0" /> 
               Писати в журнал </label> 
            </p>            
            <input type="hidden" name="archive_print" id="fgrid_params" value="1" />  
        </DIV>

</FORM>    
    
    
<?php

print('<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>');


end_mpage();
?>