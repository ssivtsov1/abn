<?php

header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';
session_name("session_kaa");
session_start();

error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];
$ids = $_SESSION['id_sess'];

  
   $nm1 = "АЕ-Встановлення/заміна лічильника та інші роботи";

   $id_work =0;
   $id_paccnt =0;
   $id_meter=0;
   $idk_work=0;
   $mode = 0;
   
   if ((isset($_POST['id_work']))&&($_POST['id_work']!=''))    
   {
     $id_work = $_POST['id_work']; 
   }

   if ((isset($_POST['idk_work']))&&($_POST['idk_work']!=''))    
   {
     $idk_work = $_POST['idk_work']; 
   }
   
   if ((isset($_POST['id_paccnt']))&&($_POST['id_paccnt']!=''))    
   {
     $id_paccnt = $_POST['id_paccnt']; 
   }

   if ((isset($_POST['id_meter']))&&($_POST['id_meter']!=''))    
   {
     $id_meter = $_POST['id_meter']; 
   }

   if ((isset($_POST['mode']))&&($_POST['mode']!=''))
   {
      $mode = $_POST['mode']; 
   }
   
   if ((isset($_POST['date_work']))&&($_POST['date_work']!=''))
   {
      $date_work = $_POST['date_work']; 
   }
   else
      $date_work ="";
 
$Query=" select to_char(fun_mmgg(), 'DD.MM.YYYY') as mmgg;";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$mmgg = $row['mmgg'];   
   
if ($id_meter!=0)   
{
  $Query=" select to_char(dt_b, 'DD.MM.YYYY') as dt_b from clm_meterpoint_tbl where id = $id_meter;";
  $result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
  $row = pg_fetch_array($result);
  $meter_min_date = $row['dt_b'];
}
else $meter_min_date='';
 
$r_meter_edit = CheckLevel($Link,'картка-лічильники',$session_user);
$r_work_edit = CheckLevel($Link,'картка-роботи',$session_user);

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
print('<script type="text/javascript" src="js/jquery.layout.resizeTabLayout.min-1.2.js"></SCRIPT>');
print('<script type="text/javascript" src="js/date-uk-UA.js"></script> ');
print('<script type="text/javascript" src="js/str_pad.js"></script>');

print('<script type="text/javascript" src="meter_work.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="dov_meters_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="dov_compi_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="dov_tp_sel.js?version='.$app_version.'"></script> '); 
print('<script type="text/javascript" src="staff_list_sel.js?version='.$app_version.'"></script> ');

$lmeters=DbTableSelList($Link,'eqk_meter_tbl','id','name');
$lphase=DbTableSelList($Link,'eqk_phase_tbl','id','name');
$lvolt=DbTableSelList($Link,'eqk_voltage_tbl','id','voltage');
$lmeterplaceselect =DbTableSelect($Link,'eqk_meter_places_tbl','id','name');
$lzones=DbTableSelList($Link,'eqk_zone_tbl','id','nm');
$lzoneselect=DbTableSelect($Link,'eqk_zone_tbl','id','nm');

$lworkselect=DbTableSelect($Link,'cli_works_tbl','id','name');
$lworkmetstatus =DbTableSelList($Link,'cli_metoper_tbl','id','name');
$lmetersselect =DbTableSelect($Link," (select id,num_meter from clm_meterpoint_tbl where id_paccnt = $id_paccnt) as ss ",'id','num_meter');

$staff_dep= DbTableSelList($Link,'prs_department','id','name');

?>
<style type="text/css"> 
/*#pmain_content {padding:3px}*/
#pwork_header {padding:1px; font-size: 14px;}
#pwork_center {padding:1px}
/* #pmeterzones {float:left; border-width:1;} */
#pMeterParam_left {padding:1px;  background-color: #CCCCCC; border-color: #444444; border-style: solid; border-width:1px;}
#pMeterParam_right {padding:1px;  background-color: #CCCCCC; border-color: #444444; border-style: solid; border-width:1px;}
/*#pMeterParam_buttons {margin:3px;  background-color: #CCCCCC; border-color: #444444; border-style: solid; border-width:1; padding: 5px; }*/

#pMeterParam {height:200px}
.tabPanel {padding:1px; margin:1px; }
.ui-tabs .ui-tabs-panel {padding:1px;}
.ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default {  color: #111111; }

</style>   

<script type="text/javascript">

var mode = <?php echo "$mode" ?>;
var id_work = <?php echo "$id_work" ?>;
var idk_work = <?php echo "$idk_work" ?>;
var id_paccnt = <?php echo "$id_paccnt" ?>;
var id_meter = <?php echo "$id_meter" ?>;
var lworkmetstatus = <?php echo "$lworkmetstatus" ?>;  
var lkind_meter = <?php echo "$lmeters" ?>;
var lphase = <?php echo "$lphase" ?>;
var lvolt = <?php echo "$lvolt" ?>;
var lzones = <?php echo "$lzones" ?>; 

var id_session = <?php echo "$ids" ?>; 
var date_work = '<?php echo "$date_work" ?>'; 
var meter_min_date = '<?php echo "$meter_min_date" ?>'; 
var r_meter_edit = <?php echo "$r_meter_edit" ?>; 
var r_work_edit = <?php echo "$r_work_edit" ?>; 
var staff_dep = <?php echo "$staff_dep" ?>; 
var mmgg = '<?php echo "$mmgg" ?>'; 
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

<DIV id="pmain_content">
 
    <form id="fWorkEdit" name="fWorkEdit" method="post" action="meter_work_edit.php" >    
    <DIV id="pwork_header">
    
           <div class="pane"  >
            <input name="id" type="hidden" id="fid" value="" />
            <input name="oper" type="hidden" id="foper" value="" />            
            <input name="change_date" type="hidden" id ="fchange_date" value="" />                
            <input type="hidden" name="id_paccnt" id = "fid_paccnt" size="10" value= "" />                 
            <input type="hidden" name="id_meter" id = "fid_meter" size="10" value= "" />                 
            <input type="hidden" name="id_session" id = "fid_session" size="10" value= "" />                 
            
            <p> 
            <label>Книга/особовий рахунок &nbsp &nbsp
            <input name="book" type="text" id = "fbook" size="5" value= "" data_old_value = ""/>
            </label> 
            <label> /
            <input name="code" type="text" id = "fcode" size="10" value= "" data_old_value = ""/>            
            </label>                 
            </p>

            <p>
                <label id="addr_str"> </label> ,
                <label id="abon">     </label>

            <p> 
                <label>Тип роботи &nbsp &nbsp&nbsp &nbsp 
                    <select name="idk_work" size="1" id="fidk_work" value= "" data_old_value = "" >
                        <?php echo "$lworkselect" ?>;
                    </select>                    
                </label>      
                
                <label>Дата роботи 
                    <input name="dt_work" type="text" size="10" class="dtpicker" id ="fdt_work" value= "" data_old_value = "" />
                </label>

                <label>Період
                    <input name="work_period" type="text" size="10" class="dtpicker" id ="fwork_period" value= "" data_old_value = "" />
                </label>
                
            </p>
            
            <p>  
                <input name="id_position" type="hidden" id = "fid_position" size="10" value= "" data_old_value = ""/>    
                <label> Роботу виконав
                    <input name="position" type="text" id = "fposition" size="50" value= "" data_old_value = "" readonly />
                </label> 
                <button type="button" class ="btnSel" id="btCntrlSel"></button>      
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <label>№ акта
                    <input name="act_num" type="text" size="15"  id ="fact_num" value= "" data_old_value = "" />
                </label>
            </p>
            
            <p> Примітка <br/>
                <textarea  style="height: 20px" cols="100" name="note" id="fnote" data_old_value = "" ></textarea>
                <input name="indication_json" type="hidden" id = "findication_json"  value= "" data_old_value = ""/>
            </p>
            
          </div>    
        <div class="pane" >
                <button name="submitButton" type="submit" class ="btn" id="bt_edit" value="edit" >Записати зміни</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_add" value="add" style="display:none;">Записати роботу</button>                
                <button name="resetButton" type="button" class ="btn" id="bt_reset" value="bt_reset" >Відмінити</button>         
        </div>
            
   
      
      <DIV>

      </DIV>
        
        
    
    </DIV>

    <DIV id="pwork_center">
    
               <div id="pindic_table" > 
                <table id="indic_table" style="margin:1px;"></table>
                <div id="indic_tablePager"></div>
               </div>
               
               <div id ="pMeterParam" > 

                <div id="pMeterParam_left" class="pane">
                <div id="pMeterEditForm" >    
                <p> <b>Встановити лічильник:</b></p>                     
                <div style="margin:3px;  border-color: #444444; border-style: solid; border-width:1px; padding: 5px; " >
                <p> 
                <input type="hidden" name="newmeter_id" id = "fnewmeter_id" size="10" value= ""  />                 
                
                <input type="hidden" name="code_eqp" id = "fcode_eqp" size="10" value= "" data_old_value = "" />                 

                <input type="hidden" name="id_type_meter" id = "fid_type_meter" size="10" value= "" data_old_value = "" /> 

                <label>Тип лічильника
                <input name="type_meter" type="text" id = "ftype_meter" style ="width:170px" value= "" data_old_value = "" readonly  />
                </label> 
                <button type="button" class ="btnSel" id="show_mlist"></button>
                <label> Номер 
                <input name="num_meter" type="text" style ="width:135px" id="fnum_meter" value= "" data_old_value = "" />
                </label>
                </p>
                <p>
                  <label> Розрядність
                     <input name="carry" type="text" size="5" id = "fcarry" value= "" data_old_value = "" />
                  </label>

                  <label>Дата повірки
                     <input name="dt_control" type="text" style="width: 80px;" class="dtpicker" id ="fdt_control" value= "" data_old_value = "" />
                  </label>

                </p>
                
                </div>
                <div style="margin:3px;  border-color: #444444; border-style: solid; border-width:1px; padding: 5px; " >

                <p>
                <label>Коеф. трансформації
                <input name="coef_comp" type="text" size="12" id="fcoef_comp" value= "1" data_old_value = "1" />
                </label>
                <button type="button" class ="btn btnSmall" id="toggle_comp">Вимірювальні тр.</button>                                
                </p>
                <div id="pMeterParam_comp">    
                <p>
                <input type="hidden" name="id_typecompa" id = "fid_typecompa" size="10" value= "" data_old_value = "" />     
                <label>Трансформатор струму
                <input name="typecompa" type="text" id = "ftypecompa" size="30" value= "" data_old_value = "" readonly />
                </label>
                <button type="button" class ="btnSel" id="show_compa"></button>      
                <label>Дата повірки тр. струму
                <input type="text" name="dt_control_ca" class="dtpicker" id ="fdt_control_ca" size="15" value= "" data_old_value = "" />
                </label>
                </p>
                <p>
                <input type="hidden" name="id_typecompu" id = "fid_typecompu" size="10" value= "" data_old_value = "" />         
                <label>Трансформатор напруги
                <input name="typecompu" type="text" id = "ftypecompu" size="30" value= "" data_old_value = "" readonly />
                </label>
                <button type="button" class ="btnSel" id="show_compu"></button>            
                <label>Дата повірки тр. напруги
                <input type="text" name="dt_control_cu" class="dtpicker" id ="fdt_control_cu" size="15" value= "" data_old_value = "" />
                </label>
                </p>
                </div>
                </div>
                <div style="margin:3px;  border-color: #444444; border-style: solid; border-width:1px; padding: 5px; " >
                    <p>
                         <label>Місце встановлення
                             <select name="id_extra" size="1" id="fid_extra" value= "" data_old_value = "" >
                                 <?php echo "$lmeterplaceselect" ?>;
                             </select>                    
                         </label>      
                        <label>Встановлена потужність
                        <input name="power" type="text" style="width: 70px;" id ="fpower" value= "" data_old_value = "" />
                    </label>
                    </p> 
                    <p> 
                    <input type="hidden"   name="calc_losts"  value="No" />                                            
                    <label style="display:none;">
                    <input type="checkbox" name="calc_losts" id="fcalc_losts" value="Yes" data_old_checked = ""/>
                    Розрахунок втрат</label>
                    <input type="hidden"   name="magnet"  value="No" />                                            
                    <label style="display:none;">
                    <input type="checkbox" name="magnet" id="fmagnet" value="Yes" data_old_checked = "" />
                    Індикатор магніта</label>
                    </p>
                    <p>
                    <input type="hidden" name="id_station" id ="fid_station" size="10" value= "" data_old_value = "" /> </label>                        
                    <label>Підключений до ТП
                    <input type="text" name="station" id ="fstation" style="width: 180px;" value= "" data_old_value = "" readonly /> </label>
                    <button type="button" class ="btnSel" id="show_tplist"></button>
                    &nbsp;&nbsp;
                    <input type="hidden"   name="smart"  value="No" />                                            
                    <label >
                    <input type="checkbox" name="smart" id="fsmart" value="Yes" data_old_checked = "" />
                    Підкл. до SMART</label>
                    
                    </p>

                </div>
                </div>        
 
                    
                </div>
                <div id="pMeterParam_right">    
                   <div id="paccnt_meter_zones" > 
                    <table id="paccnt_meter_zones_table" style="margin:1px;"></table>
                    <div   id="paccnt_meter_zones_tablePager"></div>
                   </div>
                </div>

                
            </div>        
    </DIV>

 </form>    
</DIV>

   
     <div id="grid_selmeter" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1;z-index: 100000;" >   
            <table id="dov_meters_table" style="margin:1px;"></table>
            <div id="dov_meters_tablePager"></div>
     </div>    

     <div id="grid_selci" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1;z-index: 100000;" >   
             <table id="dov_compi_table" style="margin:1px;"></table>
             <div id="dov_compi_tablePager"></div>
     </div>    

     <div id="grid_seltp" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1 ;z-index: 100000;" >   
             <table id="dov_tp_table" style="margin:1px;"></table>
             <div id="dov_tp_tablePager"></div>
     </div>    
    
     <div id="grid_selperson" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 100000;" > 
         <table id="person_sel_table" style="margin:1px;"></table>
         <div id="person_sel_tablePager"></div>
     </div>    
    
    
    <div id="dialog-changedate" title="Дата редагування" style="display:none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text">Вкажіть дату редагування </p></p>
        <br>
        <input name="date_change" type="text" size="20" class="dtpicker" id ="fdate_change"/>
    </div>
    
    <div id="dialog-confirm" title="Удалить учет?" style="display:none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text">Учет будет удален. Продолжить? </p></p>
    </div>
    
    <div id="dialog-newmeterzone" title="Нова зона" style="display:none;">

      <div id="pnewmeterzone">
        <input type="hidden" name="id" id = "fnewmeterzone_id" size="10" value= ""  />                             
        <p>    
        <label style="display: inline-block; width: 250px;" > Зона
           <select style="float:right" name="id_zone" size="1" id="fid_zone" value= "" data_old_value = "" >
              <?php echo "$lzoneselect" ?>;                        
           </select>                    
        </label>
        </p>   
        <br/>
        <p>           
        <label style="display: inline-block; width: 250px;" > Показники
                <input  style="float:right" name="indic" id ="findic" type="text" size="19" />
        </label>    
        </p>           
      </div>        
    </div>
    
    <form id="fcntrl_sel_params" name="cntrl_sel_params" target="cntrl_win" method="post" action="staff_list.php">
      <input type="hidden" name="select_mode" value="1" />
      <input type="hidden" name="id_person" id="fcntrl_sel_params_id_cntrl" value="0" />
    </form>
    

<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>


<?php

end_mpage();
?>