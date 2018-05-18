<?php

header('Content-type: text/html; charset=utf-8');
//header("Cache-Control: max-age=86400"); 

require 'abon_en_func.php';
require 'abon_ded_func.php';
session_name("session_kaa");
session_start();

error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id']; 


if ((isset($_POST['mode']))&&($_POST['mode']=='1'))
{
   
   $mode = 1; 
   $id_paccnt =0;
   $nm1 = "АЕ-Новий особовий рахунок абонента";
}
else
{
   $mode = 0; 
   $nm1 = "АЕ-Особовий рахунок абонента";

   if ((isset($_POST['id_paccnt']))&&($_POST['id_paccnt']!=''))     
   {
     $id_paccnt = $_POST['id_paccnt']; 
       
   }
    
}    

$Query=" select to_char(fun_mmgg(), 'DD.MM.YYYY') as mmgg;";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$mmgg = $row['mmgg'];

$Query=" select syi_resid_fun()::int as id_res;";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$id_res = $row['id_res'];  


$r_edit = CheckLevel($Link,'картка-загальне',$session_user);
$r_meter_edit = CheckLevel($Link,'картка-лічильники',$session_user);
$r_lgt_edit = CheckLevel($Link,'картка-пільги',$session_user);
$r_plomb_edit = CheckLevel($Link,'картка-пломби',$session_user);
$r_dog_edit = CheckLevel($Link,'картка-договор',$session_user);
$r_allmeter_edit = CheckLevel($Link,'картка-лічильники-екстра',$session_user);
$r_allmeter_direct = CheckLevel($Link,'картка-лічильники-виправлення',$session_user);

$r_work_edit = CheckLevel($Link,'картка-роботи',$session_user);
$r_abonedit = CheckLevel($Link,'довідник-абоненти',$session_user);
$r_work_extra_edit = CheckLevel($Link,'картка-роботи-екстра',$session_user);
$r_hist_edit = CheckLevel($Link,'картка-правка-история',$session_user);


start_mpage($nm1); 
head_addrpage();

$flag_cek = is_cek($Link);  //принадлежность РЭСа к ЦЭК

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

print('<script type="text/javascript" src="abon_en_paccnt.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="abon_en_paccnt_meters.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="abon_en_paccnt_lgt.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="abon_en_paccnt_dogovor.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="abon_en_paccnt_plomb.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="abon_en_paccnt_notlive.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="abon_en_paccnt_works.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="dov_abon_embed.js?version='.$app_version.'"></script> ');

print('<script type="text/javascript" src="dov_meters_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="dov_compi_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="dov_tp_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="abon_en_lgtfamily.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="lgt_calc_sel.js?version='.$app_version.'"></script> ');

if($flag_cek==0)  
    print('<script type="text/javascript" src="tarif_list_sel.js?version='.$app_version.'"></script> ');
else
    print('<script type="text/javascript" src="tarif_list_sel_cek.js?version='.$app_version.'"></script> ');

print('<script type="text/javascript" src="runner_sectors_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="lgt_list_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="staff_list_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="dov_addparam_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="check_session.js?version='.$app_version.'"></script> ');

$lmeters=DbTableSelList($Link,'eqk_meter_tbl','id','name');
$lphase=DbTableSelList($Link,'eqk_phase_tbl','id','name');
$lvolt=DbTableSelList($Link,'eqk_voltage_tbl','id','voltage');

$lzones=DbTableSelList($Link,'eqk_zone_tbl','id','nm');
$lzoneselect=DbTableSelect($Link,'eqk_zone_tbl','id','nm');

$ldocs=DbTableSelList($Link,'cli_doc_tbl','id','name');
$ldocselect=DbTableSelect($Link,'cli_doc_tbl','id','name');
 
$ldogovortype=DbTableSelList($Link,'cli_agreem_tbl','id','name');
$ldogovortypeselect=DbTableSelect($Link,'cli_agreem_tbl','id','name');
$lhousetypeselect=DbTableSelect($Link,'cli_house_type_tbl','id','name');

$lplombtype =DbTableSelList($Link,'plomb_type','id','name');
$lplombowner =DbTableSelList($Link,'plomb_owner','id','name');
$lplombplace =DbTableSelList($Link,'plomb_places','id','name');
$lplombtypeselect =DbTableSelect($Link,'plomb_type','id','name');
$lplombownerselect =DbTableSelect($Link,'plomb_owner','id','name');
$lplombplaceselect =DbTableSelect($Link,'plomb_places','id','name');

$lmeterplaceselect =DbTableSelect($Link,'eqk_meter_places_tbl','id','name');

//$lmetersselect =DbTableSelect($Link," (select id,num_meter from clm_meterpoint_tbl where id_paccnt = $id_paccnt) as ss ",'id','num_meter');

$lworktypes =DbTableSelList($Link,'cli_works_tbl','id','name');
$lworkmetstatus =DbTableSelList($Link,'cli_metoper_tbl','id','name');

$lrel=     DbTableSelList($Link,'cli_family_rel_tbl','id','nm_rel');
$lrelselect=DbTableSelect($Link,'cli_family_rel_tbl','id','nm_rel');

$lgi_budjet=DbTableSelList($Link,'lgi_budjet_tbl','id','name');
$lgi_calc= DbTableSelList($Link,'lgi_calc_header_tbl','id','name');

$staff_dep= DbTableSelList($Link,'prs_department','id','name');

$llgtreasonselect =DbTableSelect($Link,'lgi_change_reason_tbl','id','name');
$lresselect=DbTableSelect($Link,'syi_resinfo_tbl','id_department','small_name');

?>
<style type="text/css"> 
#pmain_content {padding:2px}
#pmain_header {padding:1px}
#pwork_header {padding:3px; font-size: 14px;}
#pwork_center {padding:3px}
/* #pmeterzones {float:left; border-width:1;} */
#pMeterParam_left {padding: 1px;  background-color: #CCCCCC; border-color: #444444; border-style: solid; border-width:1px;}
#pMeterParam_right {padding: 1px;  background-color: #CCCCCC; border-color: #444444; border-style: solid; border-width:1px;}
#pMeterParam_buttons {margin:3px;  background-color: #CCCCCC; border-color: #444444; border-style: solid; border-width:1px; padding: 5px; }

#pMeterParam {height:190px}

.tabPanel {padding:1px; margin: 1px; }
.ui-tabs .ui-tabs-panel {padding:1px;}
.ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default {  color: #111111; }

</style>   

<script type="text/javascript">

var mode = <?php echo "$mode" ?>;
var id_paccnt = <?php echo "$id_paccnt" ?>;
 
var lkind_meter = <?php echo "$lmeters" ?>;
var lphase = <?php echo "$lphase" ?>;
var lvolt = <?php echo "$lvolt" ?>;
var lzones = <?php echo "$lzones" ?>; 
var ldocs = <?php echo "$ldocs" ?>; 
var ldogovortype = <?php echo "$ldogovortype" ?>; 
var lworktypes = <?php echo "$lworktypes" ?>; 
var lworkmetstatus = <?php echo "$lworkmetstatus" ?>; 

var lplombtype = <?php echo "$lplombtype" ?>; 
var lplombowner = <?php echo "$lplombowner" ?>; 
var lplombplace = <?php echo "$lplombplace" ?>; 
var lrel = <?php echo "$lrel" ?>; 

var r_edit = <?php echo "$r_edit" ?>; 
var r_meter_edit = <?php echo "$r_meter_edit" ?>; 
var r_lgt_edit = <?php echo "$r_lgt_edit" ?>; 
var r_allmeter_edit = <?php echo "$r_allmeter_edit" ?>; 
var r_allmeter_direct = <?php echo "$r_allmeter_direct" ?>; 
var r_work_edit = <?php echo "$r_work_edit" ?>; 
var r_dog_edit = <?php echo "$r_dog_edit" ?>; 
var r_plomb_edit = <?php echo "$r_plomb_edit" ?>; 
var r_abonedit = <?php echo "$r_abonedit" ?>; 
var r_work_extra_edit = <?php echo "$r_work_extra_edit" ?>; 
var r_hist_edit = <?php echo "$r_hist_edit" ?>; 

var lgi_calc = <?php echo "$lgi_calc" ?>; 
var lgi_budjet = <?php echo "$lgi_budjet" ?>; 

var staff_dep = <?php echo "$staff_dep" ?>; 

var mmgg = <?php echo "'$mmgg'" ?>; 
var id_res = <?php echo "$id_res" ?>;
</script>


</head>
<body >


<DIV id="pmain_header" > 
    <?php main_menu(); ?>
</DIV>
    
<DIV id="pmain_footer">
   <a href="javascript:void(0)" id="debug_ls1">show debug window</a> 
   <a href="javascript:void(0)" id="debug_ls2">hide debug window</a> 
   <a href="javascript:void(0)" id="debug_ls3">clear debug window</a>
   <a href="javascript:void(0)" id="show_peoples">Исполнители</a>
</DIV>

<DIV id="pmain_content">
    
    <DIV id="pwork_header">
        
        <form id="fAccEdit" name="fAccEdit" method="post" action="abon_en_paccnt_edit.php" >
          <div class="pane" style="overflow-x: hidden" >
            <input name="id" type="hidden" id="fid" value="" />
            <input name="oper" type="hidden" id="foper" value="" />            
            <input name="change_date" type="hidden" id ="fchange_date" value="" />                
            
            <div style="display: inline-block;"> 
            <label>Книга/рахунок &nbsp &nbsp
            <input name="book" type="text" id = "fbook" style="width: 50px;" value= "" data_old_value = ""/>
            </label> 
            <label> /
            <input name="code" type="text" id = "fcode" style="width: 70px;" value= "" data_old_value = ""/>            
            </label>                 
            </div>
            &nbsp &nbsp &nbsp 
            <div style="display: inline-block; width: 210px;"> 
            <label>Дата початку
                  <input name="dt_b" type="text" style="width: 80px;"  class="dtpicker" id ="fdt_b" value= "" data_old_value = "" />
            </label>
            </div>  
            &nbsp  
            <label style="display: inline-block; width: 180px; margin:2px;"> Дод.угода
                <input name="dt_dod" type="text" style="width: 80px;" class="dtpicker" id ="fdt_dod" value= "" data_old_value = "" />
            </label>
             &nbsp 
            <label style="display: inline-block; margin:2px;"> Підр.
              <select name="id_dep" size="1" id="fid_dep" value= "" data_old_value = "" >
                <?php echo "$lresselect" ?>;                        
              </select>                    
            </label>
            &nbsp 
            <input type="hidden"   name="archive"  value="0" />                                            
            <label id="archive_label" style="display: inline-block; width: 170px; color:red; ">
              <input type="checkbox" name="archive" id="farchive" value="1" data_old_checked = "" onclick="return false" onkeydown="return false" />
              Архів з
              <input name="dt_archive" type="text" style="width: 80px;" class="dtpicker" id ="fdt_archive" value= "" data_old_value = "" readonly />
            </label>
            
            <br/>
            <nobr>            
            <div style="display: inline-block; white-space: normal;">
            <input name="addr" type="hidden" id = "faddr" size="10" value= "" data_old_value = ""/>    
            <label style="display: inline-block; width: 520px;" >Адреса 
            <input style="float:right; width: 414px;" name="addr_str" type="text" id = "faddr_str"  value= "" data_old_value = ""  tabindex="-1"  readonly />
            </label> 
            <button type="button" class ="btnSel" id="btAddrSel"></button>       
            </div>
            <div style="display: inline-block; white-space: nowrap;"> 
            &nbsp &nbsp
            <input type="hidden"   name="activ"  value="No" /> 
            <label style="display: inline-block; width: 130px; ">
              <input type="checkbox" name="activ" id="factiv" value="Yes" data_old_checked = ""/>
              Підключений
            </label>
            &nbsp
             <a href="javascript:void(0)" id="switch_label" style="width: 200px; color:red; white-space: nowrap;" tabindex="-1" ></a> 
            </div>
            </nobr>            
            <br/>
            <nobr>
            <div style="display: inline-block; white-space: normal;">     
            <input name="id_abon" type="hidden" id = "fid_abon" size="10" value= "" data_old_value = ""/>    
            <label style="display: inline-block; width: 486px;">Абонент  
            <input style="float:right; width: 381px;" name="abon" type="text" id = "fabon" value= "" data_old_value = "" tabindex="-1" readonly />
            </label> 
            <button type="button" class ="btn btnInfo" id="btAbonOpen"></button>     
            <button type="button" class ="btnSel" id="btAbonSel"></button>     
            </div>
            &nbsp &nbsp
            <div style="display: inline-block; white-space: nowrap;"> 
            <input type="hidden"   name="rem_worker"  value="No" />                 
            <label style="display: inline-block; width: 130px;">
              <input type="checkbox" name="rem_worker" id="frem_worker" value="Yes" data_old_checked = ""/>
              Працівник РЕМ
            </label>
            &nbsp 
             <a href="javascript:void(0)" id="lgt_label" style="color:green; white-space: nowrap;" tabindex="-1" ></a> 
            </div>
            </nobr>
            <br/>
            <nobr>            
            <div style="display: inline-block; white-space: normal;">     
            <input name="id_gtar" type="hidden" id = "fid_gtar" size="10" value= "" data_old_value = ""/>    
            <label style="display: inline-block; width: 486px;">Тариф
            <input style="float:right;width: 381px;" name="gtar" type="text" id = "fgtar"  value= "" data_old_value = "" tabindex="-1" readonly />
            </label> 
            <button type="button" class ="btn btnFamily" id="btTarFamily"></button>
            <button type="button" class ="btnSel" id="btTarSel"></button>
            </div>
            <div style="display: inline-block; white-space: nowrap;">             
            &nbsp &nbsp
            <input type="hidden"   name="not_live"  value="No" />                 
            <label style="display: inline-block; width: 130px;">
              <input type="checkbox" name="not_live" id="fnot_live" value="Yes" data_old_checked = ""/>
              Не проживає
            </label>
            &nbsp 
             <!--<a href="abon_en_subs.php" target="_blank" id="subs_label" style="color:green; "></a> -->
             <a href="javascript:void(0)" id="subs_label" style="color:green; white-space: nowrap;" tabindex="-1" ></a> 
            </div>
            </nobr>
            <br/>
            <div style="display: inline-block;">         
            <input name="id_sector" type="hidden" id = "fid_sector" size="10" value= "" data_old_value = ""/>    
            <label style="display: inline-block; width: 520px;">Дільниця
            <input style="float:right;width: 414px;" name="sector" type="text" id = "fsector" value= "" data_old_value = "" tabindex="-1" readonly />
            </label> 
            <button type="button" class ="btnSel" id="btSectorSel"></button>      

            </div>
            &nbsp &nbsp
            <input type="hidden"   name="pers_cntrl"  value="No" />                 
            <label style="display: inline-block; width: 170px; margin:2px;">
              <input type="checkbox" name="pers_cntrl" id="fpers_cntrl" value="Yes" data_old_checked = ""/>
              Особливий контроль
            </label>

            &nbsp &nbsp
            <input type="hidden"   name="green_tarif"  value="No" />
            <label style="display: inline-block; width: 140px; margin:2px;">
              <input type="checkbox" name="green_tarif" id="fgreen_tarif" value="Yes" data_old_checked = ""/>
              Зелений тариф
            </label>
            
            <p> 
            <label>Тип житла&nbsp &nbsp&nbsp &nbsp &nbsp 
                  <select name="idk_house" size="1" id="fidk_house" value= "" data_old_value = "" >
                     <?php echo "$lhousetypeselect" ?>;
                   </select>                    
            </label>      

            &nbsp &nbsp    
            <label> Опал. площа,м2
                <input name="heat_area" type="text" id = "fheat_area" style="width: 70px;" value= "" data_old_value = ""/>
            </label> 

            &nbsp &nbsp    
            <label> № субсідії
                <input name="n_subs" type="text" id = "fn_subs" style="width: 80px;" value= "" data_old_value = ""/>
            </label> 
            <button type="button" class ="btnSel" id="btNameSubsSel"></button>   
            
            <input type="hidden"   name="recalc_subs"  value="No" />
            <label style="display: inline-block; width: 100px; margin:2px;">
              <input type="checkbox" name="recalc_subs" id="frecalc_subs" value="Yes" data_old_checked = ""/>
              Повернення
            </label>

            
            &nbsp &nbsp 
            <label> Сальдо
                <input name="saldo" type="text" style="text-align:right; width: 70px;" id="fsaldo" value="" readonly />                        
            </label>                 
            </p>
            
            <p> Примітка &nbsp &nbsp &nbsp &nbsp
                <a href="runner_sectors.php" target="_blank" id="sector_label" style=" color:blue;" tabindex="-1" ></a> 
                <br/>
                <textarea  style="height: 20px" cols="65" name="note" id="fnote" data_old_value = "" ></textarea>

                <input name="id_cntrl" type="hidden" id = "fid_cntrl" size="10" value= "" data_old_value = ""/>    
                <label style="display: inline-block; width: 270px;"> Дод.ознака
                <input style="float:right;width: 180px;" name="cntrl" type="text" id = "fcntrl" value= "" data_old_value = "" readonly />
                </label> 
                <button type="button" class ="btnSel" id="btAddParamSel"></button>   
                <button type="button" class ="btnClear" id="btAddParamClear"></button>                       
                
            </p> 

           
          </div>    
             <div id="subs_name_panel" class="pane" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 1000;" >
                <p> 
                 <label> Отримувач субсидії
                    <input name="subs_name" type="text" id = "fsubs_name" style="width: 400px;" value= "" data_old_value = ""/>
                 </label> 
                </p>                  
                 <button type="button" class ='btnClose' id='bt_SubsNameclose' style='height:20px;font-size:-3' > Закрити </button>
             </div>    
            
        <div class="pane" >
                <button name="submitButton" type="submit" class ="btn" id="bt_edit" value="edit" >Записати</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_add" value="add" style="display:none;">Записати новий</button>
                <button name="resetButton" type="button" class ="btn" id="bt_reset" value="bt_reset" >Відмінити</button>
                <button type="button" class ="btn" id="bt_mainhistory" value="bt_mainhistory" >Історія</button>
                <button type="button" class ="btn" id="bt_saldo">Сальдо</button>		                                        
                <button type="button" class ="btn" id="bt_subs">Історія субсидії</button>
                <button type="button" class ="btn" id="bt_switch">Відключення</button>
                <button type="button" class ="btn" id="bt_showtree" value="bt_showtree" >Схема</button>                 
                &nbsp;&nbsp;&nbsp;
                <button type="button" class ="btn" id="bt_delabon" value="bt_delabon" >Видалити</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_archabon" value="arch" >В архів</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_unarchabon" value="unarch" >Поновити з архіву</button>
        </div>
            
      </FORM>
            
      
      <DIV>
       <form id="fabon_sel_params" name="abon_sel_params" target="abon_win" method="post" action="dov_abon.php">
         <input type="hidden" name="select_mode" value="1" />
         <input type="hidden" name="id_abon" id="fabon_sel_params_id_abon" value="0" />
       </form>
          
       <form id="fcntrl_sel_params" name="cntrl_sel_params" target="cntrl_win" method="post" action="staff_list.php">
         <input type="hidden" name="select_mode" value="1" />
         <input type="hidden" name="id_person" id="fcntrl_sel_params_id_cntrl" value="0" />
       </form>

       <form id="ftarif_sel_params" name="tarif_sel_params" target="tar_win" method="post" action="tarif_list.php">
         <input type="hidden" name="select_mode" value="1" />
       </form>

       <form id="flgt_sel_params" name="lgt_sel_params" target="lgt_win" method="post" action="lgt_list.php">
         <input type="hidden" name="select_mode" value="1" />
       </form>
          
      </DIV>
    
    </DIV>

    <DIV id="pwork_center">
     
     <!--   <div id="paccnt_tabs"> -->
            <ul id="tabButtons"> 
		<li><a href="#tab_meters">Лічильники</a></li>
                <li><a href="#tab_lgt">Пільги</a></li>
		<li><a href="#tab_dogovor">Договір</a></li>
                <li><a href="#tab_plomb">Пломби</a></li>
                <li><a href="#tab_works">Роботи</a></li>
                <li><a href="#tab_notlive">Тимчасове непрож.</a></li>
            </ul>            

            <div id="tab_meters" class="tabPanel ui-widget-content ui-tabs-hide" > 

               <div id="paccnt_meters_list" > 
                <table id="paccnt_meters_table" style="margin:1px;"></table>
                <div id="paccnt_meters_tablePager"></div>
               </div>
                
               <div id ="pMeterParam" >
                <form id="fMeterParam" name="fMeterParam" method="post" action="abon_en_paccnt_meters_edit.php" >
                <div id="pMeterParam_left" class="pane">
                <div id="pMeterEditForm" >    
                <div class="pane" > 
                <p>
                <input type="hidden" name="id" id = "fid" size="10" value= ""  />                 
                <input type="hidden" name="oper" id="foper" value="" />            
                <input type="hidden" name="change_date"  id="fchange_date" value="" />                    
                <input type="hidden" name="id_paccnt" id = "fid_paccnt" size="10" value= "" />                 
                
                <input type="hidden" name="code_eqp" id = "fcode_eqp" size="10" value= "" data_old_value = "" />                 

                <input type="hidden" name="id_type_meter" id = "fid_type_meter" size="10" value= "" data_old_value = "" /> 
                           
                <label>Тип лічильника
                <input style ="width:170px" name="type_meter" type="text" id = "ftype_meter"  value= "" data_old_value = "" readonly  />
                </label> &nbsp;
                <button type="button" class ="btnSel" id="show_mlist"></button>
                <label> Номер 
                <input name="num_meter" type="text" style ="width:135px" id="fnum_meter" value= "" data_old_value = "" />
                </label> &nbsp;
                <label> Розрядність
                <input name="carry" type="text" size="5" id = "fcarry" value= "" data_old_value = "" />
                </label> &nbsp;
                
                <label>К.тр
                <input name="coef_comp" type="text" size="5" id="fcoef_comp" value= "1" data_old_value = "1" />
                </label>
                <button type="button" class ="btn btnSmall" id="toggle_comp">Вимірювальні тр.</button>                                
                
                </p>
                <p>
                  <label>Дата встановлення
                     <input name="dt_b" type="text" style="width: 80px;" class="dtpicker" id ="fdt_start" value= "" data_old_value = "" />
                  </label>
                  <label>Дата повірки лічильника
                     <input name="dt_control" type="text" style="width: 80px;" class="dtpicker" id ="fdt_control" value= "" data_old_value = "" />
                  </label>

                </p>
                
                </div>
                <div id="pMeterParam_comp" class="pane" >
                <p>
                <input type="hidden" name="id_typecompa" id = "fid_typecompa" size="10" value= "" data_old_value = "" />     
                <label>Трансформатор струму
                <input name="typecompa" type="text" id = "ftypecompa" size="30" value= "" data_old_value = "" readonly />
                </label>
                <button type="button" class ="btnSel" id="show_compa"></button>      
                <label>Дата повірки тр. струму
                <input type="text" name="dt_control_ca" class="dtpicker" id ="fdt_control_ca" style="width: 80px;" value= "" data_old_value = "" />
                </label>
                </p>
                <p>
                <input type="hidden" name="id_typecompu" id = "fid_typecompu" size="10" value= "" data_old_value = "" />         
                <label>Трансформатор напруги
                <input name="typecompu" type="text" id = "ftypecompu" size="30" value= "" data_old_value = "" readonly />
                </label>
                <button type="button" class ="btnSel" id="show_compu"></button>            
                <label>Дата повірки тр. напруги
                <input type="text" name="dt_control_cu" class="dtpicker" id ="fdt_control_cu" style="width: 80px;" value= "" data_old_value = "" />
                </label>
                </p>
                </div>
                
                <div class="pane" >
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
                    <input type="text" name="station" id ="fstation" style="width: 180px;" value= "" data_old_value = "" tabindex="-1" readonly /> </label>
                    <button type="button" class ="btnSel" id="show_tplist"></button>
                    &nbsp;&nbsp;&nbsp;
                    <input type="hidden"   name="smart"  value="No" />                                            
                    <label >
                    <input type="checkbox" name="smart" id="fsmart" value="Yes" data_old_checked = "" />
                    Підкл. до SMART</label>
                    
                    &nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#777"> Код </span> <span style="color:#777" id ="fcode_meter">  </span>
                    <button type="button" class ="btnGear" id="btedit_meter_direct"></button>
                    </p>

                </div>
                </div>        
                <div id="hist2meter_div" style="display:none;"> 
                     <table id="hist2meter_table" style="margin:1px;"></table>
                     <div id="hist2meter_tablePager"></div>
                </div>            
                    
                </div>
                <div id="pMeterParam_right">    
                   <div id="paccnt_meter_zones" > 
                    <table id="paccnt_meter_zones_table" style="margin:1px;"></table>
                    <div   id="paccnt_meter_zones_tablePager"></div>
                   </div>

                    <div id="paccnt_meter_zones_hist" style="display:none;"> 
                    <table id="paccnt_meter_zones_hist_table" style="margin:1px;"></table>
                    <div   id="paccnt_meter_zones_hist_tablePager"></div>
                   </div>

                    
                </div>

                <div id="pMeterParam_buttons"  >
                
                <button name="submitButton" type="submit" class ="btn" id="bt_edit" value="edit" >Записати</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_add" value="add" style="display:none;">Записати новий</button>                
                <button name="resetButton" type="button" class ="btn" id="bt_reset" value="reset" >Відмінити</button>         
                
                </div>
                </form>
            </div>

            </DIV>    

            <div id="tab_lgt" class="tabPanel ui-widget-content ui-tabs-hide">
               <div id="paccnt_lgt_list" > 
                <table id="paccnt_lgt_table" style="margin:1px;"></table>
                <div id="paccnt_lgt_tablePager"></div>
               </div>

               <div id ="pLgtParam" >
                <form id="fLgtParam" name="fLgtParam"  target=""  method="post" action="abon_en_paccnt_lgt_edit.php" >
                <div id="pLgtParam_left" class="pane" style="padding:1px">
                 <input type="hidden" name="id" id = "fid" size="10" value= ""  />                 
                 <input type="hidden" name="oper" id="foper" value="" />            
                 <input type="hidden" name="change_date"  id="fchange_date" value="" />
                 <input type="hidden" name="id_reason" id = "fid_reason" size="10" value= ""  />                 
                 <input type="hidden" name="id_paccnt" id = "fid_paccnt" size="10" value= "" />                 
                 <div class="pane"  >
                     <p>            
                         <input name="id_grp_lgt" type="hidden" id = "fid_grp_lgt" size="10" value= "" data_old_value = ""/>    
                         <label> Пільга
                             <input name="grp_lgt" type="text" id = "fgrp_lgt" size="70" value= "" data_old_value = "" readonly />
                         </label> 
                         <button type="button" class ="btnSel" id="btLgtSel"></button>      
                         &nbsp; 
                         <label> Кільк. сім'ї
                             <input name="family_cnt" type="text" id = "ffamily_cnt" size="5" value= "" data_old_value = "" />
                         </label> 
                         
                         <button type="button" class ="btn btnSmall" id="btFamily">Члени сім'ї</button>      
                    </p>
                         <input name="id_calc" type="hidden" id = "fid_calc_lgt" size="10" value= "" data_old_value = ""/>    
                         <label> Розрахунок
                             <input name="calc_name" type="text" id = "fcalc_name_lgt" size="40" value= "" data_old_value = "" tabindex="-1" readonly />
                         </label> 
                         <button type="button" class ="btnSel" id="btLgtCalcSel"></button> 
                         
                         <label style="color:blue" id="calc_lgt_info"> </label> 
                             
                         <label style="display:none;" >Пріорітет пільги
                             <input name="prior_lgt" type="text" id = "fprior_lgt" size="5" value= "" data_old_value = ""/>
                         </label> 
                         
                     </p>
                     <p>            
                         <label> Особа, якій надана пільга
                             <input name="fio_lgt" type="text" id = "ffio_lgt" style="width: 315px;" value= "" data_old_value = ""/>
                         </label> 

                         <label>Ідентиф. номер
                             <input name="ident_cod_l" type="text" id = "fident_cod_l" style="width: 100px;" size="15" value= "" data_old_value = ""/>
                         </label> 
                             
                     </p>
                 </div>   
                 <div class="pane"  >
                     <p>            
                         <label>Вид документа
                             <select name="id_doc" size="1" id="fid_doc" value= "" data_old_value = "" >
                                 <?php echo "$ldocselect" ?>;
                             </select>                    
                                 
                         </label> 
                     
                     
                         <label>Серія док.
                             <input name="s_doc" type="text" id = "fs_doc" style="width: 75px;" value= "" data_old_value = ""/>
                         </label> 
                         <label>Номер док.
                             <input name="n_doc" type="text" id = "fn_doc" style="width: 100px;" value= "" data_old_value = ""/>
                         </label> 
                         <label>Дата видачі
                             <input name="dt_doc" type="text" style="width: 80px;" value="" id="fdt_doc" class="dtpicker" data_old_value = "" />
                         </label>
                         <label>Дійсний до
                             <input name="dt_doc_end" type="text" style="width: 80px;" value="" id="fdt_doc_end" class="dtpicker" data_old_value = "" />
                         </label>
                         
                     </p>
                 </div>
                 <div class="pane"  >    
                     <p>
                         <label>Дата початку
                             <input name="dt_start" type="text" size="13" class="dtpicker" id ="flgtdt_b" value= "" data_old_value = "" />
                         </label>
                         <label>Дата закінчення
                             <input name="dt_end" type="text" size="13" class="dtpicker" id ="flgtdt_e" value= "" data_old_value = "" />
                         </label>
                         
                         <label>
                            <input type="hidden"   name="closed"  value="0" />                  
                            <input type="checkbox" name="closed" id="fclosed" value="1" data_old_checked = ""/>
                                Не відкривати
                         </label>
                         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                         <label>Дата перереєстр.
                             <input name="dt_reg" type="text" size="13" class="dtpicker" id ="fdt_reg" value= "" data_old_value = "" />
                         </label>
                         
                     </p>
                     <p>                     
                        Примітка &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <textarea  style="height: 15px; width:400px;" name="note" id="flgt_note" data_old_value = "" ></textarea>
                        
                        <label id ="lid_reason" >Підстава
                            <select name="id_reason_new" size="1" id="fid_reason_new" value= "" data_old_value = "" >
                                <?php echo "$llgtreasonselect" ?>;
                            </select>                    
                        </label>      
                        
                     </p>                     
                 </div>    
                 
                </div>

                <div class ="pane" id="pLgtParam_buttons"  >
                
                <button name="submitButton" type="submit" class ="btn" id="bt_edit" value="edit" >Записати</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_add" value="add" style="display:none;">Записати новий</button>                
                <button name="resetButton" type="button" class ="btn" id="bt_reset" value="reset" >Відмінити</button>         
                 &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
                <label style="color:black" id="lgt_edit_info"> </label> 
                </div>
                </form>
              </div>

              <div id="hist_lgt_div" style="display:none;"> 
                     <table id="hist_lgt_table" style="margin:1px;"></table>
                     <div id="hist_lgt_tablePager"></div>
              </div>            
                
            </DIV>
                
            <div id="dialog_editlgtform" style="display:none; overflow:visible; z-index: 100000; ">        
                <form id="fFamilyEdit" name="fFamilyEdit" method="post" action="abon_en_lgtfamily_edit.php" >
                    <div class="pane"  >
                        <input name="id" type="hidden" id="fid" value="" />
                        <input name="id_lgt" type="hidden" id="fid_lgt" value="<?php echo $id_lgt; ?>" />
                        <input name="id_paccnt" type="hidden" id = "fid_paccnt" size="10" value= "" />
                        <input name="oper" type="hidden" id="foper" value="" />            
                        <div class="pane"  >    
                            <p>
                                <label>Член сім'ї 
                                    <input name="fio" type="text" id = "ffio" size="50" value= "" data_old_value = ""/>
                                </label> 
                                <input type="hidden"   name="lgt"  value="No" />                    
                                <label>
                                    <input type="checkbox" name="lgt" id="flgt" value="Yes" data_old_checked = ""/>
                                    Пільговик
                                </label>
                            </p>
                            <p>            
                                <label>Дата народження
                                    <input name="dt_birth" type="text" size="15" class="dtpicker" id ="fdt_birth" value= "" data_old_value = "" />
                                </label>
                                <label>Сімейні відносини
                                    <select name="id_rel" size="1" id="fid_rel" value= "" data_old_value = "" >
                                        <?php echo "$lrelselect" ?>;
                                    </select>                    
                                </label> 
                            </p>
                        </div>    
                        <div class="pane"  >    
                            <p>
                                <label>Дата початку
                                    <input name="dt_start" type="text" size="15" class="dtpicker" id ="flgtfamdt_b" value= "" data_old_value = "" />
                                </label>
                                <label>Дата закінчення
                                    <input name="dt_end" type="text" size="15" class="dtpicker" id ="flgtfamdt_e" value= "" data_old_value = "" />
                                </label>
                            </p>
                            <p>
                            <input type="hidden"   name="active"  value="No" />                    
                                <label>
                                    <input type="checkbox" name="active" id="factive" value="Yes" data_old_checked = ""/>
                                    Враховується
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
     
            <div id="tab_dogovor" class="tabPanel ui-widget-content ui-tabs-hide">
               <div id="paccnt_dogovor_list" > 
                <table id="paccnt_dogovor_table" style="margin:1px;"></table>
                <div id="paccnt_dogovor_tablePager"></div>
               </div>

               <div id ="pDogovorParam" >
                <form id="fDogovorParam" name="fDogovorParam"  target=""  method="post" action="abon_en_paccnt_dogovor_edit.php" >
                 <input type="hidden" name="id" id = "fid" size="10" value= ""  />                 
                 <input type="hidden" name="oper" id="foper" value="" />            
                 <input type="hidden" name="change_date"  id="fchange_date" value="" />                    
                 <input type="hidden" name="id_paccnt" id = "fid_paccnt" size="10" value= "" />                 
                 <div class="pane"  >
                 <div class="pane"  >
                     <p>            
                         <label>Вид договора
                             <select name="id_iagreem" size="1" id="fid_iagreem" value= "" data_old_value = "" >
                                 <?php echo "$ldogovortypeselect" ?>;
                             </select>                    
                     </p>
                     <p>
                         </label> 
                         <label>Номер
                             <input name="num_agreem" type="text" id = "fnum_agreem" size="20" value= "" data_old_value = ""/>
                         </label> 
                         <label>Дата 
                             <input name="date_agreem" type="text" size="12" value="" id="fdate_agreem" class="dtpicker" data_old_value = "" />
                         </label>
                         <label>Шифр
                             <input name="shifr" type="text" id = "fshifr" size="20" value= "" data_old_value = ""/>
                         </label> 
                     </p>
                     <p>            
                         <input name="id_abon" type="hidden" id = "fid_abon" size="10" value= "" data_old_value = ""/>    
                         <label> Договір підписав
                             <input name="agreem_abon" type="text" id = "fagreem_abon" size="50" value= "" data_old_value = ""/>
                         </label> 
                         <button type="button" class ="btnSel" id="btAbonDogSel"></button>      
                     </p>
                     <p style="display:none;">            
                         <input name="id_town_agreem" type="text" id = "fid_town_agreem" size="10" value= "" data_old_value = ""/>    
                         <label> Населений пункт
                             <input name="town" type="text" id = "ftown" size="50" value= "" data_old_value = ""/>
                         </label> 
                         <button type="button" class ="btnSel" id="btTownDogSel"></button>      
                     </p>

                     <p>            
                         <label>Потужність
                             <input name="power" type="text" id = "fpower" size="15" value= "" data_old_value = ""/>
                         </label> 
                         <label>Категорія
                             <input name="categ" type="text" id = "fcateg" size="15" value= "" data_old_value = ""/>
                         </label> 
                             
                     </p>
                 </div>   

                 <div class="pane"  >    
                     <p>
                         <label>Дата початку
                             <input name="dt_b" type="text" size="15" class="dtpicker" id ="fdogdt_b" value= "" data_old_value = "" />
                         </label>
                         <label>Дата закінчення
                             <input name="dt_e" type="text" size="15" class="dtpicker" id ="fdogdt_e" value= "" data_old_value = "" />
                         </label>
                             
                     </p>
                     
                 </div>    
                 </div>   
                <div class ="pane" id="pDogovorParam_buttons"  >
                
                <button name="submitButton" type="submit" class ="btn" id="bt_edit" value="edit" >Записати</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_add" value="add" style="display:none;">Записати новий</button>                
                <button name="resetButton" type="button" class ="btn" id="bt_reset" value="reset" >Відмінити</button>         
                
                </div>
                </form>
              </div>
                
                
            </DIV>
                
     
            <div id="tab_plomb" class="tabPanel ui-widget-content ui-tabs-hide">
               <div id="paccnt_plomb_list" > 
                <table id="paccnt_plomb_table" style="margin:1px;"></table>
                <div id="paccnt_plomb_tablePager"></div>
               </div>

               <div id ="pPlombParam" >
                <form id="fPlombParam" name="fPlombParam"  target=""  method="post" action="abon_en_paccnt_plomb_edit.php" >
                 <input type="hidden" name="id" id = "fid" size="10" value= ""  />                 
                 <input type="hidden" name="oper" id="foper" value="" />            
                 <input type="hidden" name="change_date"  id="fchange_date" value="" />                    
                 <input type="hidden" name="id_paccnt" id = "fid_paccnt" size="10" value= "" />                 
                 <div class="pane"  >
                   <div class="pane"  >
                     <p>            
                         <label>Номер пломби 
                             <input name="plomb_num" type="text" id = "fplomb_num" size="20" value= "" data_old_value = ""/>
                         </label> 

                         <label>Тип пломби
                             <select name="id_type" size="1" id="fid_type" value= "" data_old_value = "" >
                                 <?php echo "$lplombtypeselect" ?>;
                             </select>                    
                         </label>                              
                     </p>
                     <p>
                         <label>Місце встановлення
                             <select name="id_place" size="1" id="fid_place" value= "" data_old_value = "" >
                                 <?php echo "$lplombplaceselect" ?>;
                             </select>                    
                         </label>                              

                         <label>Приналежність пломби
                             <select name="id_plomb_owner" size="1" id="fid_plomb_owner" value= "" data_old_value = "" >
                                 <?php echo "$lplombownerselect" ?>;
                             </select>                    
                         </label>                              
                     </p>
                     
                     <p> 
                         <label>Лічильник
                             <select name="id_meter" size="1" id = "fid_meter" size="10" value= "" data_old_value = "">
                             </select>                    
                             <input name="num_meter" type="hidden" id = "fnum_meter" size="20" value= "" data_old_value = ""/>
                         </label> 
                     </p>
                   </div>  
                   <div class="pane"  >
                     <p>                                 
                         <label style="display: inline-block; width: 250px;" >Дата встановлення 
                             <input name="dt_on" type="text" size="12" value="" id="fdt_on" class="dtpicker" data_old_value = "" />
                         </label>
                     
                         <input name="id_person_on" type="hidden" id = "fid_person_on" size="10" value= "" data_old_value = ""/>                             
                         <label>Пломбу встановив
                             <input name="person_on" type="text" id = "fperson_on" size="40" value= "" data_old_value = ""/>
                         </label> 
                         <button type="button" class ="btnSel" id="btPlombPersonOnSel"></button>      
                     </p>

                     <p>                                 
                         <label style="display: inline-block; width: 250px;" >Дата видалення  &nbsp&nbsp&nbsp &nbsp 
                             <input name="dt_off" type="text" size="12" value="" id="fdt_off" class="dtpicker" data_old_value = "" />
                         </label>
                     
                         <input name="id_person_off" type="hidden" id = "fid_person_off" size="10" value= "" data_old_value = ""/>                             
                         <label>Пломбу видалив&nbsp&nbsp&nbsp
                             <input name="person_off" type="text" id = "fperson_off" size="40" value= "" data_old_value = ""/>
                         </label> 
                         <button type="button" class ="btnSel" id="btPlombPersonOffSel"></button>      
                     </p>
                    </div> 
                     <p>            
                         <label>Примітка
                             <input name="comment" type="text" id = "fcomment" size="100" value= "" data_old_value = ""/>
                         </label> 
                             
                     </p>
                 </div>   

                <div class ="pane" id="pPlombParam_buttons"  >
                
                <button name="submitButton" type="submit" class ="btn" id="bt_edit" value="edit" >Записати</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_add" value="add" style="display:none;">Записати новий</button>                
                <button name="resetButton" type="button" class ="btn" id="bt_reset" value="reset" >Відмінити</button>         
                
                </div>
                </form>
              </div>
                
            </DIV>     
     
            <div id="tab_notlive" class="tabPanel ui-widget-content ui-tabs-hide">
               <div id="paccnt_notlive_list" > 
                <table id="paccnt_notlive_table" style="margin:1px;"></table>
                <div id="paccnt_notlive_tablePager"></div>
               </div>

               <div id ="pNotliveParam" >
                <form id="fNotliveParam" name="fNotLiveParam"  target=""  method="post" action="abon_en_paccnt_notlive_edit.php" >
                 <input type="hidden" name="id" id = "fid" size="10" value= ""  />                 
                 <input type="hidden" name="oper" id="foper" value="" />            
                 <input type="hidden" name="change_date"  id="fchange_date" value="" />                    
                 <input type="hidden" name="id_paccnt" id = "fid_paccnt" size="10" value= "" />                 
                 <div class="pane"  >
                   <div class="pane"  >
                       
                       
                     <p>            
                         <label>Номер док.
                             <input name="num_doc" type="text" id = "fnum_doc" size="20" value= "" data_old_value = ""/>
                         </label> 
                         <label>Дата док.
                             <input name="date_doc" type="text" size="12" value="" id="fdate_doc" class="dtpicker" data_old_value = "" />
                         </label>

                     </p>
                   </div>  
                   <div class="pane"  >
                     <p>                                 
                         <label style="display: inline-block; width: 350px;" >Дата початку непроживання &nbsp&nbsp&nbsp
                             <input name="dt_b" type="text" size="12" value="" id="fdt_b_notlive" class="dtpicker" data_old_value = "" />
                         </label>
                     </p>

                     <p>                                 
                         <label style="display: inline-block; width: 350px;" >Дата закінчення непроживання 
                             <input name="dt_e" type="text" size="12" value="" id="fdt_e_notlive" class="dtpicker" data_old_value = "" />
                         </label>
                     </p>
                    </div> 
                     <p>            
                         <label>Примітка
                             <input name="comment" type="text" id = "fcomment" size="100" value= "" data_old_value = ""/>
                         </label> 
                             
                     </p>
                 </div>   

                <div class ="pane" id="pNotliveParam_buttons"  >
                
                <button name="submitButton" type="submit" class ="btn" id="bt_edit" value="edit" >Записати</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_add" value="add" style="display:none;">Записати новий</button>                
                <button name="resetButton" type="button" class ="btn" id="bt_reset" value="reset" >Відмінити</button>         
                
                </div>
                </form>
              </div>
                
            </DIV>     
            <div id="tab_works" class="tabPanel ui-widget-content ui-tabs-hide">
               <div id="paccnt_works_list" > 
                <table id="paccnt_works_table" style="margin:1px;"></table> 
                <div id="paccnt_works_tablePager"></div>
               </div>
               <div id="paccnt_works_indic_list" > 
                <table id="paccnt_works_indic_table" style="margin:1px;"></table>
                <div id="paccnt_works_indic_tablePager"></div>
               </div>

            </DIV>     

    </DIV>

    
</DIV>

     <div id="grid_selmeter" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 100000;" >   
            <table id="dov_meters_table" style="margin:1px;"></table>
            <div id="dov_meters_tablePager"></div>
     </div>    

     <div id="grid_selci" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 100000;" >   
             <table id="dov_compi_table" style="margin:1px;"></table>
             <div id="dov_compi_tablePager"></div>
     </div>    

     <div id="grid_seltp" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 100000;" >   
             <table id="dov_tp_table" style="margin:1px;"></table>
             <div id="dov_tp_tablePager"></div>
     </div>    

     <div id="grid_seltarif" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 100000;" >   
             <table id="dov_tarif_table" style="margin:1px;"></table>
             <div id="dov_tarif_tablePager"></div>
     </div>    
    
     <div id="grid_lgtfamily" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px ;z-index: 500;" >   
             <table id="lgt_family_table" style="margin:1px;"></table>
             <div id="lgt_family_tablePager"></div>
     </div>    

     <div id="grid_selcalc" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 100000;" > 
             <table id="lgt_calc_sel_table" style="margin:1px;"></table>
             <div id="lgt_calc_sel_tablePager"></div>
     </div>    
    
    <div id="grid_selsector" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 100000;" >   
        <table id="sectors_sel_table" style="margin:1px;"></table>
        <div id="sectors_sel_tablePager"></div>
    </div>       

     <div id="grid_sellgt" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 100000;" > 
             <table id="lgt_sel_table" style="margin:1px;"></table>
             <div id="lgt_sel_tablePager"></div>
     </div>    
    
     <div id="grid_selperson" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 100000;" > 
         <table id="person_sel_table" style="margin:1px;"></table>
         <div id="person_sel_tablePager"></div>
     </div>    
    
     <div id="grid_selparam" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 100000;" > 
             <table id="dov_param_table" style="margin:1px;"></table>
             <div id="dov_param_tablePager"></div>
     </div>    
    
    
    <div id="dialog-changedate" title="Дата редагування" style="display:none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text">Вкажіть дату редагування </p></p>
        <br>
        <input name="date_change" type="text" size="20" class="dtpicker" id ="fdate_change"/>
    </div>

    <div id="dialog-lgt_changedate" title="Дата редагування" style="display:none;">
	<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text">Вкажіть дату редагування пільг</p>
        <input name="date_change" type="text" size="20" class="dtpicker" id ="fdate_change_lgt"/>
        <br/>
        <label>Причина коригування
             <select name="id_reason" size="1" id="fid_reason" value= "" data_old_value = "" >
                 <?php echo "$llgtreasonselect" ?>;
             </select>                    
        </label>      
    </div>

    <div id="dialog-lgt_confirm" title="Удалить учет?" style="display:none;">
	<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text">Учет будет удален. Продолжить? </p>
        <br/>
        <label>Причина коригування
             <select name="id_reason" size="1" id="fid_reason" value= "" data_old_value = "" >
                 <?php echo "$llgtreasonselect" ?>;
             </select>                    
        </label>      
        
    </div>
    
    
    <div id="dialog-confirm" title="Удалить учет?" style="display:none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text">Учет будет удален. Продолжить? </p></p>
    </div>

    <div id="dialog-changemeterdirect" title="Корегування лічильника" style="display:none;">
	<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text">Корегування даних лічильника без заміни!!!</p>
        <br/>
        <input type="hidden" name="id_type_meter_direct" id = "fid_type_meter_direct" size="10" value= "" data_old_value = "" /> 
        <input type="hidden" name="code_meter_direct" id = "fcode_meter_direct" size="10" value= "" data_old_value = "" /> 
        <p>
        <label>Тип лічильника
            <input style ="width:170px" name="type_meter_direct" type="text" id = "ftype_meter_direct"  value= "" data_old_value = "" readonly  />
        </label> 
        <button type="button" class ="btnSel" id="show_mlist_direct"></button>
         </p>
         <p>
        <label> Номер 
           <input name="num_meter_direct" type="text" style ="width:135px" id="fnum_meter_direct" value= "" data_old_value = "" />
        </label> &nbsp;
         </p>
         <p>
        <label> Розрядність
           <input name="carry_direct" type="text" size="5" id = "fcarry_direct" value= "" data_old_value = "" />
        </label>
         </p>        
    </div>
    
    
    <div id="dialog-newmeterzone" title="Нова зона" style="display:none;">


        <p>    
        <label style="display: inline-block; width: 250px;" > Зона
           <select style="float:right" name="id_zone" size="1" id="fid_zone" value= "" data_old_value = "" >
              <?php echo "$lzoneselect" ?>;                        
           </select>                    
        </label>
        </p>   
        <p>           
        <label> Дата встановлення
                <input  name="date_install" id ="fdate_install" type="text" size="19" class="dtpicker" />
        </label>    
        </p>           

    </div>

    <div id="dialog-mainhistory" title="Історія змін особового рахунку" style="display:none;z-index: 1005;">
     <div id="grid_mainhistory" >   
             <table id="mainhistory_table" style="margin:1px;"></table> 
             <div id="mainhistory_tablePager"></div>
     </div>    
    </div>
    
    
    <DIV style="display:none;">
      <form id="fpaccnt_params" name="paccnt_params" method="post" action="">
         <input type="text" name="mode" id="pmode" value="0" />
         <input type="text" name="id_meter" id="pid_meter" value="0" />         
         <input type="text" name="id_paccnt" id="pid_paccnt" value="0" />                  
         <input type="text" name="id_work" id="pid_work" value="0" />                  
         <input type="text" name="idk_work" id="pidk_work" value="0" />  
         <input type="text" name="date_work" id="pdate_work" value="" />  
         <input type="text" name="paccnt_info" id="ppaccnt_info" value="" />                  
         <input type="text" name="paccnt_book" id="ppaccnt_book" value="" />                  
         <input type="text" name="paccnt_code" id="ppaccnt_code" value="" />                  
         <input type="text" name="paccnt_name" id="ppaccnt_name" value="" />                           
      </form>
    </DIV>
    
    
        <div id="dialog_editAbonform" style="display:none; overflow:visible; ">        
        <form id="fAbonEdit" name="fAbonEdit" method="post" action="dov_abon_edit.php" >
          <div class="pane"  >
            <input name="id" type="hidden" id="fid" value="" />
            <input name="oper" type="hidden" id="foper" value="" />    
            <input type="hidden" name="change_date"  id="fchange_date" value="" />                    
            <div class="pane"  >
            <p> 
            <label>Прізвище 
            <input name="last_name" type="text" id = "flast_name" size="50" value= "" data_old_value = ""/>
            </label> 
            </p>

            <p>
            <label>Ім'я 
            <input name="name" type="text" id = "fname" size="40" value= "" data_old_value = ""/>
            </label> 
           
            <label>По батькові
            <input name="patron_name" type="text" id = "fpatron_name" size="40" value= "" data_old_value = ""/>
            </label> 
            
            </p>
            </div>
            <div class="pane"  >
            <p>            
            <input name="addr_reg" type="hidden" id = "faddr_reg" size="10" value= "" data_old_value = ""/>    
            <label>Адреса реєстрації
            <input name="addr_reg_str" type="text" id = "faddr_reg_str" size="70" value= "" data_old_value = "" readonly/>
            </label> 
            <button type="button" class ="btnSel" id="btAddr1Sel"></button>      
            </p>
            <p>            
            <input name="addr_live" type="hidden" id = "faddr_live" size="10" value= "" data_old_value = ""/>    
            <label>Адреса проживання
            <input name="addr_live_str" type="text" id = "faddr_live_str" size="70" value= "" data_old_value = "" readonly/>
            </label> 
            <button type="button" class ="btnSel" id="btAddr2Sel"></button>      
            <button type="button" class ="btn btnCopy" id="btAddr2Copy"></button>      
            </p>
            </div>
            <div class="pane"  >
            <p>
            <label>Серія паспорта
            <input name="s_doc" type="text" id = "fs_doc" size="10" value= "" data_old_value = ""/>
            </label> 
            <label>Номер 
            <input name="n_doc" type="text" id = "fn_doc" size="20" value= "" data_old_value = ""/>
            </label> 
            </p>
            <p>            
            <label>Дата видачі
            <input name="dt_doc" type="text" size="20" value="" id="fdt_doc_abon" class="dtpicker" data_old_value = "" />
            </label>
            </p>
            <p>            
            <label>Ким виданий
            <input name="who_doc" type="text" id = "fwho_doc" size="100" value= "" data_old_value = ""/>
            </label> 
            </p>
            <p>            
            <label>Ідентифікаційний номер
            <input name="tax_number" type="text" id = "ftax_number" size="30" value= "" data_old_value = ""/>
            </label> 
            </p>
            </div>
            
            <p>            
            <label>Тел. домашній 
            <input name="home_phone" type="text" id = "fhome_phone" size="20" value= "" data_old_value = ""/>
            </label> 
            <label>Тел. робочій 
            <input name="work_phone" type="text" id = "fwork_phone" size="20" value= "" data_old_value = ""/>
            </label> 
            <label>Тел. мобільний 
            <input name="mob_phone" type="text" id = "fmob_phone" size="20" value= "" data_old_value = ""/>
            </label> 

            </p>

            <p>            
            <label>Електронна пошта
            <input name="e_mail" type="text" id = "fe_mail" size="40" value= "" data_old_value = ""/>
            </label> 
            </p>            
            
            <p>            
            <label>Примітка
            <input name="note" type="text" id = "fnote" size="100" value= "" data_old_value = ""/>
            </label> 
            </p>
            
            <p>            
            <label>Дата
            <input name="dt_b" type="text" size="20" value="" id="fdt_b_abon" class="dtpicker" data_old_value = "" />
            </label>
            <label>Дата занесення в базу
            <input name="dt_input" type="text" size="20" value="" id="fdt_input_abon" data_old_value = "" readonly />
            </label>

            </p>
                        
            
        </div>    
        <div class="pane" >
                <button name="submitButton" type="submit" class ="btn" id="bt_edit" value="edit" >Записати</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_add" value="add" style="display:none;">Записати новий</button>                
                <button name="resetButton" type="button" class ="btn" id="bt_reset" value="bt_reset" >Відмінити</button>         
        </div>
            
      </FORM>
            
      </div>                
    
    <form id="fadr_sel_params" name="adr_sel_params" target="adr_win" method="post" action="adr_tree_selector.php">
        <input type="hidden" name="select_mode" value="1" />
        <input type="hidden" name="address" id="fadr_sel_params_address" value="" />
    </form>
    
    

<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>


<?php

end_mpage();
?>