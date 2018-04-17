<?php
header('Content-type: text/html; charset=utf-8');

require_once 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();
//error_reporting(0);
$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];

$nam = "АЕ-Звіти";

start_mpage($nam);
head_addrpage();


$Query=" select to_char(fun_mmgg(), 'DD.MM.YYYY') as mmgg;";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$mmgg = $row['mmgg'];

$Query=" select value_ident::int as value from syi_sysvars_tbl where ident='id_res'; ";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($lnk) );
$row = pg_fetch_array($result);
$id_res = $row['value'];

if ($id_res==310)
    $ind_blank = 'ind_blank';
else
    $ind_blank = 'ind_blank_res';


$lmeters=DbTableSelList($Link,'eqk_meter_tbl','id','name');
$lphase=DbTableSelList($Link,'eqk_phase_tbl','id','name');

$lgi_budjet=DbTableSelList($Link,'lgi_budjet_tbl','id','name');
$lgi_calc= DbTableSelList($Link,'lgi_calc_header_tbl','id','name');

$staff_dep= DbTableSelList($Link,'prs_department','id','name');
$lindicoperselect =DbTableSelect($Link,'cli_indic_type_tbl','id','name');

$lregionselect=DbTableSelect($Link,'cli_region_tbl','id','name');
$ltasklist=DbTableSelect($Link,'cli_tasks_tbl','id','name');
$controlerlist=DbTableSelect($Link,'prs_persons','id','represent_name');

$r_off = CheckLevel($Link,'відключення',$session_user);
$town_hidden=CheckTownInAddrHidden($Link);

print('<link rel="stylesheet" type="text/css" href="css/ded_styles.css" /> ');
print('<link rel="stylesheet" type="text/css" href="css/layout-default-latest.css" /> ');
    
print('<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script> ');
print('<script type="text/javascript" src="js/jquery.form.js"></script>');
print('<script type="text/javascript" src="js/jquery.validate.min.js?version='.$app_version.'" ></script>');
print('<script type="text/javascript" src="js/jquery.ui.datepicker-uk.js"></script> ');
print('<script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script> ');
print('<script type="text/javascript" src="js/jquery.layout.min-latest.js"></SCRIPT>');
print('<script type="text/javascript" src="js/spin.min.js"></script> ');
print('<script type="text/javascript" src="js/date-uk-UA.js"></script> ');

print('<script type="text/javascript" src="reps_main.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="runner_sectors_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="tarif_list_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="dov_meters_multisel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="abon_en_main_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="lgt_list_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="staff_list_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="dov_addparam_sel.js?version='.$app_version.'"></script> ');
?>

<script type="text/javascript">

var mmgg = <?php echo "'$mmgg'" ?>;
var lkind_meter = <?php echo "$lmeters" ?>;
var lphase = <?php echo "$lphase" ?>;
var lgi_calc = <?php echo "$lgi_calc" ?>; 
var lgi_budjet = <?php echo "$lgi_budjet" ?>; 
var staff_dep = <?php echo "$staff_dep" ?>; 

var r_off = <?php echo "$r_off" ?>;
var town_hidden = <?php echo "$town_hidden" ?>; 
var id_res = <?php echo "$id_res" ?>;  
//var dt_b = $("#fdt_b").val();
//var dt_b = ch_dt

function ch_dt(p){
   var dt_1 = p;
   //alert(dt_1);
}    

</script>

<style type="text/css"> 

  #bwarning_del   {color: #FFF !important; background: url("images/ui-bg_glass_45_c095a4_1x400.png") repeat-x scroll 50% 50% #C095A4 !important;}
  
</style>   

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
    
    <form id="freps_params" name="reps_params" method="post" action="rep_main_build_html.php" target="_blank">
        
        <DIV id="preps_header">
            <input type="hidden" name="template_name" id="ftemplate_name" value="" />      
            <input type="hidden" name="oper" id="foper" value="" />      
            <!--
            <p style ="color: firebrick; ">
                <b>Просьба не запускать Звіт по реалізації до 8.20. Пытаюсь ускорить его работу.</b>
            </p> -->
            <p>
                Період
                <button type="button" class ="btn btnSmall" id="btPeriodDec"> &nbsp;&lt;&nbsp; </button>     
                <input  name="period_str" type="text" id = "fperiod_str" size="30" value= "" readonly />
                <button type="button" class ="btn btnSmall" id="btPeriodInc">&nbsp;&gt;&nbsp;</button>     
            
                &nbsp;&nbsp;
                З  
                <input name="dt_b" type="text" size="10" class="dtpicker" id ="fdt_b" value= "" onchange="ch_dt(this.value)"/>
                По     
                <input name="dt_e" type="text" size="10" class="dtpicker" id ="fdt_e" value= "" />                
                &nbsp;&nbsp;                
                <label><input type="checkbox" name="to_xls" id="fxls" value="1" /> Звіт в EXCEL</label> 
                
                &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;
                <button type="button" class ="btnSel" id="btShowAddParam"></button>                       
                
            </p>
            <p>
                <label>На дату &nbsp &nbsp
                    <input name="dt_rep" type="text" size="10" class="dtpicker" id ="fdt_rep" value= "" />
                </label> 
                &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;
                
                <label>За рік &nbsp &nbsp
                    <input name="year_rep" type="text" size="10" id ="fyear_rep" value= "" />
                </label> 
                
            </p>
            <p>
                <label>Сума боргу/споживання &nbsp &nbsp
                    <input name="sum_value" type="text" id = "fsum_value" size="10" value= "" data_old_value = ""/>
                </label> 
                
                <select name="sum_direction" size="1" id = "fsum_direction" size="10" value= "" tabindex="-1" >
                         <option value="1" selected >Більше</option>
                         <option value="2">Менше</option>
                         <option value="3">Рівно</option>
                         <option value="4">Менше(більше 0)</option>
                </select>
                &nbsp;&nbsp;                                
                <label>Борг тривалістю/кільк. місяців &nbsp &nbsp
                    <input name="debet_month" type="text" id = "fdebet_month" size="5" value= "" data_old_value = ""/>
                </label> 
                
                <label id="loldbill_only"><input type="checkbox" name="oldbill_only" id="foldbill_only" value="1" /> Без рахунків поточного місяця</label>                 
            </p>            
            <p>
                <input name="id_gtar" type="hidden" id = "fid_gtar" size="10" value= "" data_old_value = ""/>    
                <label> 
                    Тариф  
                    <input name="gtar" type="text" id = "fgtar" size="60" value= "" data_old_value = "" readonly />
                </label> 
                <button type="button" class ="btnSel" id="btTarSel"></button>      
                <button type="button" class ="btnClear" id="btTarClear"></button>       
                
                <input type="hidden" name="id_type_meter" id = "fid_type_meter" size="10" value= "" data_old_value = "" /> 
                <input type="hidden" name="id_type_meter_array" id = "fid_type_meter_array" size="10" value= "" data_old_value = "" /> 
                &nbsp &nbsp &nbsp &nbsp
                <label>Тип приладу обліку
                <input style ="width:170px" name="type_meter" type="text" id = "ftype_meter"  value= "" data_old_value = "" readonly  />
                </label> &nbsp;
                <button type="button" class ="btnSel" id="btMeterSel"></button>
                <button type="button" class ="btnClear" id="btMeterClear"></button>       
                
            </p>                        
            
            <p>
                <input name="addr_town" type="hidden" id = "faddr_town" size="10" value= "" data_old_value = ""/>    
                <label >Населений пункт
                    <input  name="addr_town_name" type="text" id = "faddr_town_name" size="50" value= "" data_old_value = "" readonly />
                </label> 
                <button type="button" class ="btnSel" id="btAddrTownSel"></button>       
                <button type="button" class ="btnClear" id="btAddrTownClear"></button>       
                <label id ="ltown_detal"><input type="checkbox" name="town_detal" id="ftown_detal" value="1" /> Друкувати підсумки по нас.пунктах </label>
            </p>
            <p>
                <input name="id_paccnt" type="hidden" id = "fid_paccnt" size="10" value= "" data_old_value = ""/>    
                <label>Книга/особовий рахунок &nbsp &nbsp
                    <input name="book" type="text" id = "fbook" size="5" value= "" data_old_value = ""/>
                </label> 
                <label> /
                    <input name="code" type="text" id = "fcode" size="10" value= "" data_old_value = ""/>            
                </label>                 
                <button type="button" class ="btn btnSmall" id="btPaccntFind">+</button>                       
                <input  name="paccnt_name" type="text" id = "fpaccnt_name" size="50" value= "" data_old_value = "" readonly />
                <button type="button" class ="btnSel" id="btPaccntSel"></button>
                <button type="button" class ="btnClear" id="btPaccntClear"></button>
                
                <label id="lrem_worker"><input type="checkbox" name="rem_worker" id="frem_worker" value="1" /> Працівники РЕМ</label>
            </p>            

            <p>            
                <input name="id_sector" type="hidden" id = "fid_sector" size="10" value= "" data_old_value = ""/>    
                <label >Дільниця
                    <input name="sector" type="text" id = "fsector" size="50" value= "" data_old_value = "" readonly />
                </label> 
                <button type="button" class ="btnSel" id="btSectorSel"></button>   
                <button type="button" class ="btnClear" id="btSectorClear"></button> 
                <button type="button" class ="btnPlan" id="btSectorPlan"></button> 
                <button type="button" class ="btnPlanID" id="btSectorPlanID"></button> 
                
                &nbsp;&nbsp; &nbsp;  

                <label>Регіон
                     <select name="id_region" size="1" id = "fid_region"  value= "" data_old_value = "">
                         <?php echo "$lregionselect" ?>;
                     </select>                    
                </label> 
                
            </p>
             
            <p>
                <input name="id_grp_lgt" type="hidden" id = "fid_grp_lgt" size="10" value= "" data_old_value = ""/>    
                <label> Пільга
                    <input name="grp_lgt" type="text" id = "fgrp_lgt" size="70" value= "" data_old_value = "" readonly />
                </label> 
                <button type="button" class ="btnSel" id="btLgtSel"></button> 
                <button type="button" class ="btnClear" id="btLgtClear"></button>       
                
                &nbsp;&nbsp;                                
                <label>Кількість пільговиків &nbsp &nbsp
                    <input name="family_cnt" type="text" id = "ffamily_cnt" size="5" value= "" data_old_value = ""/>
                </label>                 

            </p>
            <p>            
                <input name="id_person" type="hidden" id = "fid_person" size="10" value= "" data_old_value = ""/>                             
                <label>Працівник
                  <input name="person" type="text" id = "fperson" size="40" value= "" data_old_value = ""/>
                </label> 
                <button type="button" class ="btnSel" id="btPersonSel"></button>      
                <button type="button" class ="btnClear" id="btPersonClear"></button>                       

             &nbsp;&nbsp;                
             <label id="lsum_only"><input type="checkbox" name="sum_only" id="fsum_only" value="1" /> Тільки підсумки</label> 
             &nbsp;&nbsp;   
             <label id="lshow_pager"><input type="checkbox" name="show_pager" id="fshow_pager" value="1" checked /> Показувать сторінками</label> 
             &nbsp;&nbsp;   
             <label id="lold_years"><input type="checkbox" name="old_years" id="fold_years" value="1"  /> Тільки попередні роки</label> 
                             
            </p>
            
            <p>       
                 <label>Походження показників
                     <select name="id_operation" size="1" id = "fid_operation" size="10" value= "" tabindex="-1" >
                         <?php echo "$lindicoperselect" ?>;
                     </select>                    
                 </label> 

                 <!--<span style="color:#2E8B57">Для ускорения работы временно отключен постоянный расчет задолженности по годам/месяцам!  </span>-->
                
                <input name="id_cntrl" type="hidden" id = "fid_cntrl" size="10" value= "" data_old_value = ""/>    
                <label style="display: inline-block; width: 270px;"> Дод.ознака
                <input style="float:right;width: 180px;" name="cntrl" type="text" id = "fcntrl" value= "" data_old_value = "" readonly />
                </label> 
                <button type="button" class ="btnSel" id="btAddParamSel"></button>      
                <button type="button" class ="btnClear" id="btAddParamClear"></button>                       
                
                &nbsp;&nbsp;   
                <label> Тип завдання
                    <select name="idk_work" size="1" id="fidk_work">
                        <?php echo "$ltasklist" ?>;                        
                    </select>                    
                </label>
                
            </p>                

            
            <input type="hidden" name="grid_params" id="fgrid_params" value="" />  
            <input type="hidden" name="report_caption" id="freport_caption" value="" />  
            
            <div id="dadd_params"  style="display:none;">
                <textarea  style="height: 20px" cols="100" name="note" id="fnote" data_old_value = "" ></textarea>
            </div>
          
        </DIV>

        <DIV id="preps_buttons">

            <div class="ui-corner-all ui-state-default" id="pActionBar">
                <div class="pane">
                    Перелік абонентів
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('abon_list')" id="bt_abon_list"  value="abon_list" highlight="#fdt_rep,#faddr_town_name,#fgtar,#ltown_detal,#fbook,#lshow_pager,#ftype_meter,#fsector,#fid_region,#fcntrl,#lsum_only">Всі</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bt_abon_list_max"  value="abon_list_max" highlight="#fdt_rep,#faddr_town_name,#fgtar,#ltown_detal, #fbook,#lshow_pager,#ftype_meter,#fsector,#fgrp_lgt,#fid_region,#fcntrl,#lrem_worker">Всі докладно</button> &nbsp;                    
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('abon_list')" id="bt_abon_list_3f" value="abon_list_3f" highlight="#fdt_rep,#faddr_town_name,#fgtar,#ltown_detal,#lshow_pager,#ftype_meter,#fbook,#fsector,#fid_region,#fcntrl,#lsum_only">Трифазні</button>  &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('abon_list')" id="babon_list_reswork" value="abon_list_reswork" highlight="#fdt_rep,#faddr_town_name,#fgtar,#ltown_detal,#lshow_pager,#ftype_meter,#fbook,#fsector,#fid_region,#fcntrl,#lsum_only">Працівники РЕМ</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('abon_list')" id="babon_list_dach" value="abon_list_dach" highlight="#fdt_rep,#faddr_town_name,#fgtar,#ltown_detal,#lshow_pager,#ftype_meter,#fbook,#fsector,#fid_region,#fcntrl,#lsum_only">Дачі</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('abon_list')" id="babon_list_vip" value="abon_list_vip" highlight="#fdt_rep,#faddr_town_name,#fgtar,#ltown_detal,#lshow_pager,#ftype_meter,#fbook,#fsector,#fid_region,#fcntrl,#lsum_only">Особ.контроль</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" id="babon_list_notlive" value="abon_list_notlive" highlight="#fdt_rep,#faddr_town_name,#fgtar,#ltown_detal,#lshow_pager,#ftype_meter,#fbook,#fsector,#fid_region">Тимчасово непроживають</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bt_abon_zerodem" value="abon_zerodem" highlight="#fperiod_str,#faddr_town_name,#ltown_detal,#lshow_pager,#fbook,#fsector,#fid_region, #fcntrl ">Нульове спож.</button> 
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bt_abon_zerodem_year" value="abon_zerodem_year" highlight="#fperiod_str,#faddr_town_name,#ltown_detal,#lshow_pager,#fbook,#fsector,#fid_region,#fcntrl">Нульове спож. рік</button> 
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('abon_list')" id="bt_abon_list_nometer"  value="abon_list_nometer" highlight="#fdt_rep,#faddr_town_name,#fgtar,#ltown_detal,#fbook,#lshow_pager,#fbook,#fsector,#fid_region,#fcntrl,#lsum_only">Без лічильників</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('abon_list')" id="babon_list_nosector" value="abon_list_nosector" highlight="#fdt_rep,#faddr_town_name,#fgtar,#ltown_detal,#lshow_pager,#ftype_meter,#fbook,#fid_region,#fcntrl,#lsum_only">Без дільниці</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('abon_list')" id="babon_list_nodogovor" value="abon_list_nodogovor" highlight="#fdt_rep,#faddr_town_name,#fgtar,#ltown_detal,#lshow_pager,#ftype_meter,#fbook,#fid_region,#fcntrl,#lsum_only">Без договору</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('abon_list_tmp_dogovor')" id="babon_list_tmp_dogovor" value="abon_list_tmp_dogovor" highlight="#fdt_rep,#faddr_town_name,#fgtar,#ltown_detal,#lshow_pager,#ftype_meter,#fbook,#fid_region,#fcntrl">З тимчас.договором</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('abon_list')" id="babon_list_noagreement" value="abon_list_noagreement" highlight="#fdt_rep,#faddr_town_name,#fgtar,#ltown_detal,#lshow_pager,#ftype_meter,#fbook,#fid_region,#fcntrl,#lsum_only">Без дод. угоди</button> &nbsp; 
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('abon_list')" id="babon_list_agreement" value="abon_list_agreement" highlight="#fdt_rep,#faddr_town_name,#fgtar,#ltown_detal,#lshow_pager,#ftype_meter,#fbook,#fid_region,#fcntrl,#lsum_only">З додатковою угодою</button> &nbsp; <br/> 
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bt_abon_list_new"  value="abon_list_new" highlight="#fperiod_str,#fdt_b,#fdt_e, #faddr_town_name,#fgtar,#ltown_detal, #fbook,#lshow_pager,#ftype_meter,#fsector,#fid_region">Нові абоненти</button> &nbsp; 
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bt_agreem_rep_all" onclick="setTemplate('agreem_rep')" value="agreem_rep_all" highlight="#faddr_town_name,#fbook,#lshow_pager,#fsector,#fid_region">Договори</button> &nbsp; 
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bt_agreem_rep" value="agreem_rep" highlight="#fdt_b,#fdt_e,#faddr_town_name,#fbook,#lshow_pager,#fsector,#fid_region">Договори за період</button> &nbsp;                     
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bt_biginfo2016" value="biginfo2016" highlight="#faddr_town_name,#fpaccnt_name,#fbook,#fcode,#fsector,#fid_region">Інформаційний лист</button> &nbsp;
                </div>     
               
                <div class="pane">                
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bt_zvit" value="zvit" highlight="#fperiod_str,#faddr_town_name,#fbook,#fcode,#fid_region">Звіт по реалізації</button>
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('zvit')" id="bt_zvit_fast" value="zvit_fast" highlight="#fperiod_str,#faddr_town_name,#fbook,#fcode,#fid_region">Звіт по реалізації(без перерах.сальдо)</button> 
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('zvit')" id="bt_zvit_read" value="zvit_read" highlight="#fperiod_str,#faddr_town_name,#fbook,#fcode,#fid_region">Звіт по реалізації(без переформування)</button>  
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bt_zvit_column_move" value="zvit_column_move" highlight="#fperiod_str,#fid_region">Перенесення сальдо у звіті</button> <br/>
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bt_oborab" value="oborab" highlight="#fdt_b,#fdt_e,#fpaccnt_name,#fbook,#fcode">Обiгова вiдомiсть по абон.</button>
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bt_tar_abon" value="tarif_abon_sum" highlight="#fperiod_str,#fgtar,#lshow_pager,#lsum_only,#fbook,#fsector,#fid_region">Аналіз споживання по тарифам.</button>
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bt_abon_cnt" value="abon_cnt" highlight="#fperiod_str,#faddr_town_name,#fid_region">Чисельність</button>
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bt_debetor_cnt" value="debetor_cnt" highlight="#fperiod_str,#faddr_town_name,#fid_region">Заходи по боржникам</button>
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bt_debetor_cnt_list" value="debetor_cnt_list" highlight="#fperiod_str,#faddr_town_name,#fbook,#fsector,#fid_region ">Заходи (перелік) </button>
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bt_debetor_cnt_3month" value="debetor_cnt_3month" highlight="#fperiod_str,#faddr_town_name,#fid_region">Заходи по боржникам(3/6 міс.)</button>
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bt_9nkre" value="9nkre" highlight="#fdt_b,#fdt_e,#fid_region">9НКРЕ</button> 
                </div>
                
                <div class="pane">
                    Інформація по групам споживачів
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('abon_saldo_list')" id="babon_saldo_list" value="abon_saldo_list" highlight="#fperiod_str,#faddr_town_name,#ltown_detal,#fgtar,#lshow_pager,#lsum_only,#fbook,#fsector,#fid_region,#ftype_meter" >Всі</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('abon_saldo_list')" id="babon_saldo_list_reswork" value="abon_saldo_list_reswork" highlight="#fperiod_str,#faddr_town_name,#ltown_detal,#fgtar,#lshow_pager,#lsum_only,#fbook,#fsector,#fid_region,#ftype_meter">Працівники РЕМ</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('abon_saldo_list')" id="babon_saldo_list_3" value="abon_saldo_list_3" highlight="#fperiod_str,#faddr_town_name,#ltown_detal,#fgtar,#lshow_pager,#lsum_only,#fbook,#fsector,#fid_region,#ftype_meter">Трифазні</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('abon_saldo_list')" id="babon_saldo_list_heat" value="abon_saldo_list_heat" highlight="#fperiod_str,#faddr_town_name,#ltown_detal,#fgtar,#lshow_pager,#lsum_only,#fbook,#fsector,#fid_region,#ftype_meter">Опалення</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('abon_saldo_list')" id="babon_saldo_list_dach" value="abon_saldo_list_dach" highlight="#fperiod_str,#faddr_town_name,#ltown_detal,#fgtar,#lshow_pager,#lsum_only,#fbook,#fsector,#fid_region,#ftype_meter">Дачі</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('abon_saldo_list')" id="babon_saldo_list_vip" value="abon_saldo_list_vip" highlight="#fperiod_str,#faddr_town_name,#ltown_detal,#fgtar,#lshow_pager,#lsum_only,#fbook,#fsector,#fid_region,#ftype_meter">Особ.контроль</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('abon_saldo_list')" id="babon_saldo_list_notlive" value="abon_saldo_list_notlive" highlight="#fperiod_str,#faddr_town_name,#ltown_detal,#fgtar,#lshow_pager,#lsum_only,#fbook,#fsector,#fid_region,#ftype_meter">Тимчасово непроживають</button> &nbsp;
                </div>
                
                <div class="pane"> Пільги
                    <button name="submitButton" type="submit" class ="btn btnRep" id="babon_lgt1" value="abon_lgt1" highlight="#fperiod_str,#faddr_town_name,#ltown_detal,#fgrp_lgt,#fgtar,#lshow_pager,#fbook,#fsector,#fid_region,#ffamily_cnt">Абоненти, що мають пільги</button>
                    <button name="submitButton" type="submit" class ="btn btnRep" id="babon_lgt_period" value="abon_lgt_period" highlight="#fperiod_str,#faddr_town_name,#ltown_detal,#fgrp_lgt,#fgtar,#lshow_pager,#fbook,#fsector,#fid_region,#ffamily_cnt">Період пільги</button>
                    <button name="submitButton" type="submit" class ="btn btnRep" id="babon_f2" value="abon_f2" highlight="#fperiod_str,#faddr_town_name,#ltown_detal,#fgrp_lgt,#lshow_pager,#lsum_only,#fbook,#fsector,#fid_region">Форма 2</button>
                    <button name="submitButton" type="submit" class ="btn btnRep" id="blgt_dod2" value="lgt_dod2" highlight="#fperiod_str,#faddr_town_name,#fbook,#fsector,#fid_region">Додаток №2</button>
                    <button name="submitButton" type="submit" class ="btn btnRep" id="blgt_dod3" value="lgt_dod3" highlight="#fperiod_str,#faddr_town_name,#fbook,#fsector,#fid_region">Додаток №3</button>
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('lgt_dod4')" id="blgt_dod4" value="lgt_dod4" highlight="#fperiod_str,#faddr_town_name,#fbook,#fsector,#fid_region">Додаток №4</button>
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('lgt_dod4')" id="blgt_dod4_tar" value="lgt_dod4_tar" highlight="#fperiod_str,#faddr_town_name,#fbook,#fsector,#fid_region">Додаток №4 по кат.</button>
                    <button name="submitButton" type="submit" class ="btn btnRep" id="blgt_f10" value="lgt_f10" highlight="#fperiod_str,#fbook,#fsector,#fid_region,#faddr_town_name">Форма №10</button>
                    <button name="submitButton" type="submit" class ="btn btnRep" id="blgt_dubl" value="lgt_dubl" highlight="#fperiod_str,#fid_region">Більше 1 пільги</button> 
                    <button name="submitButton" type="submit" class ="btn btnRep" id="blgt_dubl_inn" value="lgt_dubl_inn" highlight="#fperiod_str,#fid_region">Більше 1 пільги на ІНН</button> 
                    <button name="submitButton" type="submit" class ="btn btnRep" id="babon_lgt_children" value="abon_lgt_children" highlight="#fperiod_str,#faddr_town_name,#fbook,#fsector,#fid_region">Перевірка багатодітних</button> 
                    
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('abon_lgt1')" id="babon_lgtnullnumber" value="abon_lgt_nullnumber" highlight="#fperiod_str,#faddr_town_name,#ltown_detal,#fgrp_lgt,#fgtar,#lshow_pager,#fbook,#fsector,#fid_region">Без номера посв.</button>
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('abon_lgt1')" id="babon_lgt_check_doc" value="abon_lgt_check_doc" highlight="#fperiod_str,#faddr_town_name,#ltown_detal,#fgrp_lgt,#fgtar,#lshow_pager,#fbook,#fsector,#fid_region">Прострочені документи.</button>
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('abon_lgt1')" id="babon_lgt_check_family" value="abon_lgt_check_family" highlight="#fperiod_str,#faddr_town_name,#ltown_detal,#fgrp_lgt,#fgtar,#lshow_pager,#fbook,#fsector,#fid_region">Не вказані члени сім'ї</button>
                    
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bautocancellog" value="autocancellog" highlight="#fperiod_str,#fid_region">Автокоригування (діти)</button>
                    
                    <button name="submitButton" type="submit" class ="btn btnRep" id="blgt_month_summary" value="lgt_month_summary" highlight="#fperiod_str,#fid_region,#faddr_town_name">Пільги для ревізії</button>
                </div>
                <div class="pane"> Субсидії
                    <button name="submitButton" type="submit" class ="btn btnRep" id="babon_subs1" value="abon_subs1" highlight="#fperiod_str,#faddr_town_name,#ltown_detal,#lshow_pager,#fbook,#fsector,#fid_region,#fgtar">Сума субсидії</button>
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bsubs_files" value="subs_files" highlight="#fperiod_str,#fid_region">Підсумок по файлам</button>
                    
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bsubs_dt_list" value="subs_dt_list" highlight="#fperiod_str,#faddr_town_name,#fid_region">Субсидіанти, які не платять</button>
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bsubs_dt2month_list" value="subs_dt2month_list" highlight="#fperiod_str,#faddr_town_name,#fid_region">Субсидіанти-борг 2 міс.</button> <br/>
                    
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bsubs_recalc_lgt_minus" value="subs_recalc_lgt_minus" highlight="#fperiod_str,#faddr_town_name,#fid_region">Перерахунок - (пільги + )</button>
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bsubs_recalc_lgt_plas" value="subs_recalc_lgt_plas" highlight="#fperiod_str,#faddr_town_name,#fid_region">Перерахунок + (пільги - )</button>  <br/>
                    
                    <button name="submitButton" type="submit" class ="btn btnRep" id="babon_subs_heat_list" value="abon_subs_heat_list" highlight="#fperiod_str,#faddr_town_name,#ltown_detal,#lshow_pager,#fbook,#fsector,#fid_region,#fgtar">Суми невикористаної субс.</button>
                    <button name="submitButton" type="submit" class ="btn btnRep" id="babon_subs_heat_recalc" value="abon_subs_heat_recalc" highlight="#fperiod_str,#faddr_town_name,#ltown_detal,#lshow_pager,#fbook,#fsector,#fid_region,#fgtar">Розрахунок невикористаної субс (всі).</button> 
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('abon_subs_heat_recalc')" id="babon_subs_heat_recalc_only" value="abon_subs_heat_recalc_only" highlight="#fperiod_str,#faddr_town_name,#ltown_detal,#lshow_pager,#fbook,#fsector,#fid_region,#fgtar">Розрахунок невикористаної субс (не 0).</button>
                    <button name="submitButton" type="submit" class ="btn btnRep" id="babon_subs_heat" value="abon_subs_heat" highlight="#fdt_b,#fdt_e,#fperiod_str,#faddr_town_name,#fbook,#fcode,#fsector,#fid_region,#fgtar,#fsum_value">Субсидії з опаленням</button>
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bsubs_recalc_abon" value="subs_recalc_abon" highlight="#fperiod_str,#faddr_town_name,#ltown_detal,#lshow_pager,#fpaccnt_name,#fbook,#fcode,#fsector,#fid_region,#fgtar">Друк невикористаної по абон.</button> 
                    <button name="submitButton" type="submit" class ="btn btnRep" id="babon_subs_heat150_list" value="abon_subs_heat150_list" highlight="#fperiod_str,#faddr_town_name,#ltown_detal,#lshow_pager,#fbook,#fsector,#fid_region,#fgtar">Вартість 150кВтг.</button>
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('abon_subs1')" id="babon_subs_return" value="abon_subs_return" highlight="#fperiod_str,#faddr_town_name,#ltown_detal,#lshow_pager,#fbook,#fsector,#fid_region,#fgtar">Повернення субсидії(факт)</button>
                </div>

                <div class="pane">Зони
                    
                <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('abon_list')" id="bt_abon_list_3z"  value="abon_list_3z" highlight="#fdt_rep,#faddr_town_name,#fgtar,#ltown_detal,#fbook,#fsector,#lshow_pager,#fid_region,#fcntrl">Перелік 3-зонних</button> 
                <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('abon_list')" id="bt_abon_list_2z"  value="abon_list_2z" highlight="#fdt_rep,#faddr_town_name,#fgtar,#ltown_detal,#fbook,#fsector,#lshow_pager,#fid_region,#fcntrl">Перелік 2-зонних</button> 
                &nbsp;
                <button name="submitButton" type="submit" class ="btn btnRep" id="bt_zon3" value="zon3" highlight="#fperiod_str,#fid_region">Розрахунок по 3-зонним</button>
                <button name="submitButton" type="submit" class ="btn btnRep" id="bt_zon2" value="zon2" highlight="#fperiod_str,#fid_region">Розрахунок по 2-зонним</button>

                <button name="submitButton" type="submit" class ="btn btnRep" id="bt_zon3_abon" value="zon3_abon" highlight="#fperiod_str,#fid_region">Деталізація по 3-зонним</button>
                <button name="submitButton" type="submit" class ="btn btnRep" id="bt_zon2_abon" value="zon2_abon" highlight="#fperiod_str,#fid_region">Деталізація по 2-зонним</button>
                
                </div>
                <div class="pane">                
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bt_bill_list" value="bill_list" highlight="#fdt_b,#fdt_e,#lshow_pager,#lsum_only,#fbook,#fsum_value,#fsector,#fgtar,#fcntrl,#fid_region">Рахунки за період.</button>
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bt_pay_list" value="pay_list" highlight="#fdt_b,#fdt_e,#lshow_pager,#lsum_only,#fbook,#fcode,#fsector,#fid_region">Оплата за період.</button>
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bt_pay_list_archive" value="pay_list_archive" onclick="setTemplate('pay_list')" highlight="#fdt_b,#fdt_e,#lshow_pager,#lsum_only,#fbook,#fcode,#fsector,#fid_region">Оплата в архів</button>
                                        
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bdt_list" value="dt_list" highlight="#fdt_rep,#fperiod_str,#faddr_town_name,#ltown_detal,#fbook,#fsector,#fsum_value,#fdebet_month,#lshow_pager,#fid_region,#loldbill_only,#lrem_worker">Друк боржників</button>
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bkt_list" value="kt_list" highlight="#fdt_rep,#fperiod_str,#faddr_town_name,#ltown_detal,#fbook,#fsector,#fsum_value,#lshow_pager,#fid_region,#lrem_worker">Друк кредиторів</button>  
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('kt_list')" id="bkt_list_subs" value="kt_list_subs" highlight="#fdt_rep,#fperiod_str,#faddr_town_name,#ltown_detal,#fbook,#fsector,#fsum_value,#lshow_pager,#fid_region,#lrem_worker">Кредитори-субсидіанти</button>  
                    
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bpay_summary" value="pay_summary" highlight="#fperiod_str,#fid_region">Оплата по банкам</button>  
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bpay_decade" value="pay_decade" highlight="#fperiod_str,#fid_region,#fsector">Оплата по декадам</button>                      
                    
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bsaldo_years" value="saldo_years" highlight="#fperiod_str,#fid_region,#faddr_town_name">Сальдо по роках</button>
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bsaldo_years_abon" value="saldo_years_abon" highlight="#fperiod_str,#lshow_pager,#fbook,#fcode,#fsector,#lold_years,#fsum_value, #fsum_direction, #fyear_rep,#fid_region">Сальдо по роках абон.</button>
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bdebet_years_abon" value="debet_years_abon" highlight="#fperiod_str,#lshow_pager,#fbook,#fsector, #fgtar,#faddr_town_name, #fyear_rep,#fid_region">Борг по роках абон.</button>
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bdebet_years_month_abon" value="debet_years_month_abon" highlight="#fperiod_str,#lshow_pager,#fbook,#fsector, #fgtar,#faddr_town_name, #fyear_rep,#fid_region,#fdebet_month">Борг по роках та місяцях поточн.</button>
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bdebet_years_month_old_abon" value="debet_years_month_old_abon" highlight="#fperiod_str,#lshow_pager,#fbook,#fsector, #fgtar,#faddr_town_name, #fyear_rep,#fid_region">Борг по місяцях попер. років</button> <br/>
                    
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('dt_inventar')" id="bdt_inventar" value="dt_inventar" highlight="#fperiod_str,#lshow_pager,#fbook,#fsector, #fgtar,#faddr_town_name,#fid_region">Інвентаризація Дебет на кінець року</button>
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('dt_inventar')" id="bdt_inventar" value="dt_inventar_off" highlight="#fperiod_str,#lshow_pager,#fbook,#fsector, #fgtar,#faddr_town_name,#fid_region">Інвентаризація Дебет(відкл.) на кінець року</button>
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('kt_inventar')" id="bkt_inventar" value="kt_inventar" highlight="#fperiod_str,#lshow_pager,#fbook,#fsector, #fgtar,#faddr_town_name,#fid_region">Інвентаризація Кредит на кінець року</button>
                    
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bt_abon_plan_demand"  value="abon_plan_demand" highlight="#fperiod_str,#faddr_town_name,#ltown_detal, #fbook,#fcode,#fsector,#lshow_pager,#fid_region">Планове споживання</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('abon_plan_demand')" id="bt_abon_plan_demand_empty"  value="abon_plan_demand_empty" highlight="#fperiod_str,#faddr_town_name,#ltown_detal, #fbook,#fcode,#fsector,#lshow_pager,#fid_region">Нема планового спож.</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bt_avg_demand"  value="avg_demand" highlight="#fdt_b,#fdt_e,#faddr_town_name,#fbook,#fsector,#lshow_pager,#ftype_meter,#fgtar,#fid_region">Середньомісячне спож.</button> &nbsp;
                </div>                    
                <div class="pane">
                    Iнф. в розрiзi населенних пунктiв
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('nas_p_all')" id="bnas_p_all" value="nas_p_all" highlight="#fperiod_str,#fid_region">Повна</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('nas_p_all')" id="bnas_p_3" value="nas_p_3" highlight="#fperiod_str,#fid_region">По трифазним</button>&nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('nas_p_all')" id="bnas_p_p" value="nas_p_p" highlight="#fperiod_str,#fid_region">По ел.плитам</button>&nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('nas_p_all')" id="bnas_p_h" value="nas_p_h" highlight="#fperiod_str">По ел.опаленню</button>&nbsp; 
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('nas_p_all')" id="bnas_p_off" value="nas_p_off" highlight="#fperiod_str,#fid_region">Відключені</button>&nbsp;                    
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('nas_p_kur')" id="bnas_p_kur" value="nas_p_kur" highlight="#fperiod_str,#fid_region" >В розрізі дільниць</button>&nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('nas_p_kur')" id="bnas_p_kontrol" value="nas_p_kontrol" highlight="#fperiod_str,#fid_region" >В розрізі контролерів</button>&nbsp;

                </div>
                <div class="pane">
                    Iнформацiя в розрiзi ТП
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('ktp_all')" id="bktp_all" value="ktp_all" highlight="#fperiod_str,#faddr_town_name,#fid_region">Повна</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('ktp_all')" id="bktp_3" value="ktp_3" highlight="#fperiod_str,#faddr_town_name,#fid_region">По трифазним</button>&nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('ktp_all')" id="bktp_p" value="ktp_p" highlight="#fperiod_str,#faddr_town_name,#fid_region">По ел.плитам</button>&nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('ktp_all')" id="bktp_h" value="ktp_h" highlight="#fperiod_str,#faddr_town_name,#fid_region">По ел.опаленню</button>&nbsp; 
                </div>

                <div class="pane">
                    Попередження/відключення
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bwarning_calc" value="warning_calc" highlight="#fpaccnt_name,#fdt_rep,#fperiod_str,#fsum_value,#fsector,#fid_region,#fbook,#faddr_town_name" >Розрахунок попереджень</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bwarning_build" value="warning_build" highlight="#fpaccnt_name,#fdt_rep,#fperiod_str,#fsum_value,#fsector,#fid_region,#fbook,faddr_town_name" >Сформувати попередження</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bwarning_print" value="warning_print" highlight="#fpaccnt_name,#fperiod_str,#faddr_town_name,#fbook,#fsector,#fdt_rep,#fid_region,#faddr_town_name,#fsum_value">Друк попереджень</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bwarning_list" value="warning_list" highlight="#fperiod_str,#faddr_town_name,#fbook,#fsector,#lshow_pager,#fdt_rep,#fid_region,#faddr_town_name,#fsum_value">Друк реєстра попер.</button> &nbsp; 
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bwarning_post" value="warning_post" highlight="#fperiod_str,#faddr_town_name,#fbook,#fsector,#fdt_rep,#fid_region">Реєстр на відправку.</button> &nbsp; 
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bwarning_pay" value="warning_pay" highlight="#fsector,#fid_region,#fbook,#faddr_town_name" >Оплата попередженими</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('warning_pay')" id="bwarning_pay_ww" value="warning_pay_ww" highlight="#fsector,#fid_region,#fbook,#faddr_town_name" >Прострочені попередження</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bswitch_rep" value="switch_rep" highlight="#fdt_b,#fdt_e,#faddr_town_name,#fbook,#fsector,#fid_region">Звіт по відключенням</button> &nbsp; 
                    <button name="submitButton" type="submit" class ="btn btnRep" id="boff_pay" value="off_pay" highlight="#fsector,#fid_region,#fbook,#faddr_town_name" >Оплата відключеними</button> &nbsp;

                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('warning_list')" id="bwarning_del" value="warning_del" highlight="#fsector,#fid_region,#fbook" >Видалити нерознесені попередження</button> &nbsp; <br/>
                    
                    <button name="submitButton" type="submit" class ="btn btnRep" id="boff_print" value="task_print" highlight="#fperiod_str,#faddr_town_name,#fbook,#fsector,#fdt_rep,#fid_region,#faddr_town_name,#fidk_work,#fperson" >Друк завдання</button> &nbsp; 
                    <button name="submitButton" type="submit" class ="btn btnRep" id="boff_print_list" value="task_print_list" highlight="#fperiod_str,#faddr_town_name,#fbook,#fsector,#fdt_rep,#fid_region,#faddr_town_name,#fidk_work,#fperson" >Друк реєстру завдань</button> &nbsp;                     
                </div>

                <div class="pane">
                    Показання приладів обліку
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bindic_list_all" value="indic_list_all" highlight="#fperiod_str,#fdt_b,#fdt_e,#faddr_town_name,#fsector,#lshow_pager,#lsum_only,#ftype_meter,#fbook,#fgtar,#fsum_value,#fperson,#fid_operation,#fid_region,#fcntrl,#ltown_detal">Журнал споживання</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" id="babon_list_sector" value="abon_list_sector" highlight="#fdt_rep,#faddr_town_name,#fsector,#fperson,#lshow_pager,#ftype_meter,#fbook,#fid_region">По дільницям</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('abon_list_sector')" id="babon_indic_null" value="abon_indic_null" highlight="#fdt_rep,#fperiod_str,#faddr_town_name,#fsector,#fperson,#fbook,#lshow_pager,#fid_region">Абоненти без показників</button> &nbsp; 
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bcheck_indic_err" value="check_indic_err" highlight="#fdt_b,#fdt_e,#lshow_pager,#fbook,#fsector,#fid_region">Невідповідні показники  </button> &nbsp;  <br/>
                    
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bindic_summary" value="indic_summary" highlight="#fperiod_str,#fid_region,#fbook,#fsector ">Кількість занесених показань</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bindic_summary_insp" value="indic_summary_insp" highlight="#fperiod_str,#fid_region">Кількість показ.(по кур'єрам)</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bindic_summary_insp1" value="indic_summary_insp_new" highlight="#fperiod_str,#fid_region">Кількість показ.(по кур'єрам) НОВА!!!</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bindic_summary_insp2" value="indic_summary_insp_detail_new" highlight="#fperiod_str,#fid_region">Кількість показ.(по кур'єрам і дільницям) НОВА!!!</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bindic_summary_insp2" value="indic_summary_counter_new" highlight="#fperiod_str,#fid_region">Кількість лічильників(по місцям уст.) НОВА!!!</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('indic_summary_insp')" id="bindic_summary_insp_pack" value="indic_summary_insp_pack" highlight="#fperiod_str,#fid_region">Кількість показ.(по кур'єрам та відомостям)</button> &nbsp;
                    
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bindic_summary_small" value="indic_summary_small" highlight="#fperiod_str,#fbook,#fsector,#fid_region">Підсумок</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bind_pack_summary" value="ind_pack_summary" highlight="#fperiod_str,#fid_region">Підсумок по дільницям</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bind_dates_summary" value="ind_dates_summary" highlight="#fperiod_str,#fid_region">Підсумок по датам</button> &nbsp;
                    
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bind_blank" value="<?php echo "$ind_blank" ?>" highlight="#fperiod_str,#fbook,#fsector,#lshow_pager">Бланк зняття показань</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bindic_list_off" value="indic_list_off" highlight="#fperiod_str,#faddr_town_name,#fsector,#lshow_pager,#fbook,#fgtar,#fid_region">Споживання відключених</button> &nbsp;                    
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bt_abon_indiconly"  value="abon_indiconly" highlight="#fdt_b,#fdt_e,#faddr_town_name,#fgtar,#fbook,#lshow_pager,#fsector,#fid_operation,#fid_region">Мають показники тільки вказаного типу</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('indic_list_all')" id="bindic_list_many" value="indic_list_many" highlight="#fperiod_str,#faddr_town_name,#fsector,#lshow_pager,#lsum_only,#ftype_meter,#fbook,#fgtar,#fsum_value,#fperson,#fid_operation,#fid_region">Мають декілька показників</button> &nbsp;
                    
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bcheck_ind_plan_zero3" value="check_ind_plan_zero3" highlight="#fperiod_str,#faddr_town_name,#fsector,#lshow_pager,#fid_region">По плану (0 в попер.місяці)  </button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bcheck_ind_less" value="check_ind_less" highlight="#fperiod_str,#faddr_town_name,#fsector,#lshow_pager,#fid_region,#fid_operation">Поточні< попередніх </button> &nbsp; 
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bcheck_ind_calc_greater" value="check_ind_calc_greater" highlight="#fperiod_str,#faddr_town_name,#fsector,#lshow_pager,#fid_region">Донараховано > спожито </button> &nbsp; <br/>
                    
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('gek_demand')" id="bt_gek_demand"  value="gek_demand_bill" highlight="#fperiod_str">Споживання по ЖЕК(по рахункам)</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('gek_demand')" id="bt_gek_demand"  value="gek_demand" highlight="#fperiod_str">Споживання по ЖЕК(по показникам)</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bt_multif_demand"  value="multif_demand" highlight="#fperiod_str">Споживання багатоповерх.</button> &nbsp;
                </div>

                <div class="pane">
                    Прилади обліку
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('meter_change')" id="bmeter_change" value="meter_change" highlight="#fperiod_str,#fdt_b,#fdt_e,#faddr_town_name,#fbook,#fsector,#fid_region,#fgtar">Перелік замін</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('meter_change')" id="bmeter_change_zone" value="meter_change_zone" highlight="#fperiod_str,#fdt_b,#fdt_e,#faddr_town_name,#fbook,#fsector,#fid_region,#fgtar">Перелік замін (зони)</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bwork_count" value="work_count" highlight="#fperiod_str,#fdt_b,#fdt_e,#faddr_town_name,#fbook,#fsector,#fid_region">Кількість робіт</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bt_abon_meter_check"  value="abon_meter_check" highlight="#fdt_rep,#faddr_town_name,#fgtar,#ltown_detal, #fbook,#lshow_pager,#ftype_meter,#fsector,#fid_region">Потребують повірки</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bt_abon_meter_many"  value="abon_meter_many" highlight="#fdt_rep,#faddr_town_name,#fgtar,#ltown_detal, #fbook,#lshow_pager,#fsector,#fid_region">Більше 1 лічильника</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('abon_meter_many')" id="bt_abon_meter_manypaccnt"  value="abon_meter_manypaccnt" highlight="#fdt_rep,#faddr_town_name,#fgtar,#ltown_detal, #fbook,#lshow_pager,#fsector,#fid_region">Номер лічильника декілька разів</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bwork_stat_count" value="work_stat_count" highlight="#fperiod_str,#fdt_b,#fdt_e,#faddr_town_name,#fbook,#fsector,#fid_region">Неоглянуті абон(свод).</button> &nbsp;                    
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bt_abon_meter_control"  value="abon_meter_control" highlight="#fperiod_str,#fdt_b,#fdt_e,#faddr_town_name,#fgtar,#ltown_detal, #fbook,#lshow_pager,#ftype_meter,#fsector,#fid_region">Неоглянуті абон(список)</button> &nbsp;
                </div>
                <div class="pane">
                    Пломби
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bplomb_list_now" value="plomb_list_now" highlight="#fdt_rep,#faddr_town_name,#fperson,#fbook,#fsector,#fid_region,#lrem_worker">Перелік пломб на дату</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('plomb_list')" id="bplombs_in" value="plombs_in" highlight="#fdt_b,#fdt_e,#faddr_town_name,#fperson,#fbook,#fsector,#fid_region" >Встановлені за період</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('plomb_list')" id="bplombs_out" value="plombs_out" highlight="#fdt_b,#fdt_e,#faddr_town_name,#fperson,#fbook,#fsector,#fid_region">Зняті за період</button> &nbsp;                    
                </div>

                <div class="pane">
                    Коригування
                    <button name="submitButton" type="submit" class ="btn btnRep" id="btarif_abon_edit" value="tarif_abon_edit" highlight="#fdt_b,#fdt_e,#faddr_town_name,#fsector,#fgtar,#lshow_pager,#fbook,#fperson,#fid_region">Коригування тарифів</button> &nbsp;                    
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bindic_change" value="indic_change" highlight="#fperiod_str,#faddr_town_name,#fsector,#lshow_pager,#lsum_only,#ftype_meter,#fbook,#fgtar,#fperson,#fid_operation,#fid_region">Коригування показників</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('indic_change')" id="bindic_change_subs" value="indic_change_subs" highlight="#fperiod_str,#faddr_town_name,#fsector,#lshow_pager,#lsum_only,#ftype_meter,#fbook,#fgtar,#fperson,#fid_operation,#fid_region">Коригування показ.(субсідіанти)</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bbill_corr" value="bill_corr" highlight="#fperiod_str,#faddr_town_name,#fsector,#lshow_pager,#fbook,#fgtar,#fperson,#fid_region">Коригування рахунків</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('pay_list')" id="bt_corpay_list" value="corpay_list" highlight="#fdt_b,#fdt_e,#lshow_pager,#lsum_only,#fbook,#fcode,#fsector,#fid_region">Коригування оплати</button> 
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bpaymove_list" value="paymove_list" highlight="#fperiod_str,#fid_region">Перенесення оплати</button> <br/>
                    
                    <button name="submitButton" type="submit" class ="btn btnRep" id="blgm_abon_edit" value="lgm_abon_edit" highlight="#fperiod_str,#faddr_town_name,#lshow_pager,#fbook,#fsector,#fperson,#fid_region">Коригування даних пільги</button>
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('lgm_abon_edit')" id="blgm_abon_cancel" value="lgm_abon_cancel" highlight="#fperiod_str,#faddr_town_name,#lshow_pager,#fbook,#fsector,#fperson,#fid_region">Скасовані пільги</button>
                    
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('lgt_sum_corr')" id="blgt_sum_corr" value="lgt_sum_corr" highlight="#fperiod_str,#faddr_town_name,#fgrp_lgt,#fgtar,#lshow_pager,#fbook,#fsector,#fid_region">Коригування суми пільги</button>
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('lgt_sum_corr')" id="blgt_sum_abon_corr" value="lgt_sum_abon_corr" highlight="#fperiod_str,#faddr_town_name,#fgrp_lgt,#fgtar,#lshow_pager,#fbook,#fsector,#fid_region">Коригування пільги абон.</button>                    
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('lgt_sum_corr')" id="blgt_sum_corr_plus" value="lgt_sum_corr_plus" highlight="#fperiod_str,#faddr_town_name,#fgrp_lgt,#fgtar,#lshow_pager,#fbook,#fsector,#fid_region">Коригування пільги +</button>
                    <button name="submitButton" type="submit" class ="btn btnRep" onclick="setTemplate('lgt_sum_corr')" id="blgt_sum_corr_old" value="lgt_sum_corr_old" highlight="#fperiod_str,#faddr_town_name,#fgrp_lgt,#fgtar,#lshow_pager,#fbook,#fsector,#fid_region">Коригування пільги мин. років</button>
                    
                </div>

                <div class="pane"> 
                    Перевірки
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bt_demand_check" value="demand_check" highlight="#fperiod_str,#fid_region">Перевірка кВтг</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bt_saldo_check" value="saldo_check" highlight="#fperiod_str,#fid_region">Перевірка сальдо</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bt_dtkt_check" value="dtkt_check" highlight="#fperiod_str,#fid_region">Дт/Кт одночасно</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bt_lgtsubs_check" value="lgtsubs_check" highlight="#fperiod_str,#fid_region">Пільги та субсидії одночасно</button> &nbsp;
                    <button name="submitButton" type="submit" class ="btn btnRep" id="bt_zvitf2_check" value="zvitf2_check" highlight="#fperiod_str,#fid_region">Пільги - форма 2 та звіт</button> &nbsp;
                </div>
                
                <!-- <button name="b1" type="button" class ="btn" id="bt_test" value="test" >Загрузить</button> -->
            </div>
        </DIV>
    </FORM>
</DIV>

<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>

<div id="report_progress" style ="" > 
    <p> Идет формирование отчета... </p>
    
    <div id ="report_progress_indicator" style="position:absolute; top:50px; left:50%"> </div>
    <div id ="report_progress_time" style="position:absolute; height:20px;  top:80px; left:40%"> </div>
</div>
  
<form id="fadr_sel_params" name="adr_sel_params" target="adr_win" method="post" action="adr_tree_selector.php">
    <input type="hidden" name="select_mode" id="fadr_sel_params_select_mode" value="1" />
    <input type="hidden" name="address" id="fadr_sel_params_address" value="" />
    <input type="hidden" name="full_addr_mode" id="ffull_addr_mode" value="2" />
</form>    

<form id="fpaccnt_sel_params" name="paccnt_sel_params" target="paccnt_win" method="post" action="abon_en_main.php">
   <input type="hidden" name="select_mode" value="1" />
</form>

<form id="flgt_sel_params" name="lgt_sel_params" target="lgt_win" method="post" action="lgt_list.php">
   <input type="hidden" name="select_mode" value="1" />
</form>

<form id="fcntrl_sel_params" name="cntrl_sel_params" target="cntrl_win" method="post" action="staff_list.php">
    <input type="hidden" name="select_mode" value="1" />
    <input type="hidden" name="id_person" id="fcntrl_sel_params_id_cntrl" value="0" />
</form>

<form id="ftarif_sel_params" name="tarif_sel_params" target="tar_win" method="post" action="tarif_list.php">
    <input type="hidden" name="select_mode" value="1" />
</form>

<div id="grid_selsector" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 100000;" >   
    <table id="sectors_sel_table" style="margin:1px;"></table>
    <div id="sectors_sel_tablePager"></div>
</div>       

<div id="grid_seltarif" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 100000;" >   
    <table id="dov_tarif_table" style="margin:1px;"></table>
    <div id="dov_tarif_tablePager"></div>
</div>    

<div id="grid_selmeter" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 100000;" >   
    <table id="dov_meters_table" style="margin:1px;"></table>
    <div id="dov_meters_tablePager"></div>
</div>    

<div id="grid_selabon" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 100000;" >
        <table id="abon_en_sel_table" style="margin:1px;"></table>
        <div id="abon_en_sel_tablePager"></div>
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

<div id="pcontrolers_counters_table" style="display:none; position:absolute; margin:1px;left:13.5%;
     background: #FFFFCC; borderWidth:1px;z-index: 100000;" >   
    <table id="controlers_counters_table" style="margin:1px;"></table>
    <div id="controlers_counters_tablePager"></div>
</div> 


<div id="grid_plancache" style="display:none; position:absolute; margin:1px;left:33%;
     background: #FFFFCC; borderWidth:1px;z-index: 100000;" >   
     <table id="plan_cache_table" style="margin:1px;"></table>
     <div id="plan_cache_tablePager"></div>
</div> 

<div id="grid_planview" style="display:none; position:absolute; margin:1px;left:33%;
     background: #FFFFCC; borderWidth:1px;z-index: 100000;" >   
     <table id="plan_view_table" style="margin:1px;"></table>
     <div id="plan_view_tablePager"></div>
</div>  

    <div id="dialog-confirm" title="Предупреждение" style="display:none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text">Текст </p></p>
    </div>


<iframe id="frame_hidden" src="" style="display:none"></iframe>

</body>
</html>


