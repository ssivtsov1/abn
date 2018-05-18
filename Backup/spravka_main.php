<?php
header('Content-type: text/html; charset=utf-8');

require_once 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();
//error_reporting(0);
$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];

$nam = "АЕ-Справки";

start_mpage($nam);
head_addrpage();


$Query=" select fun_mmgg() as p_mmgg, to_char(fun_mmgg(), 'DD.MM.YYYY') as mmgg;";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$p_mmgg = $row['p_mmgg'];
$mmgg = $row['mmgg'];

$Query=" select p.id, p.represent_name 
 from syi_user as u join prs_persons as p on (p.id = u.id_person)
 where u.id = $session_user ";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$id_person = $row['id'];
$name_person = $row['represent_name'];


$Query=" select coalesce(max(num_sp),0)+1 as next_num  from rep_spravka_tbl
            where date_trunc('year', mmgg::date) = date_trunc('year','$p_mmgg'::date ) ;"; 

$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$next_num = $row['next_num'];


//$lmeters=DbTableSelList($Link,'eqk_meter_tbl','id','name');
//$lphase=DbTableSelList($Link,'eqk_phase_tbl','id','name');

//$lgi_budjet=DbTableSelList($Link,'lgi_budjet_tbl','id','name');
//$lgi_calc= DbTableSelList($Link,'lgi_calc_header_tbl','id','name');

$staff_dep= DbTableSelList($Link,'prs_department','id','name');
$town_hidden=CheckTownInAddrHidden($Link);
//$r_off = CheckLevel($Link,'відключення',$session_user);

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
print('<script type="text/javascript" src="spravka_main.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="abon_en_main_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="staff_list_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="spravka_cache_sel.js?version='.$app_version.'"></script> ');

$ltype= DbTableSelList($Link,'rep_sprav_types_tbl','id','name');

?>


<script type="text/javascript">

var mmgg = <?php echo "'$mmgg'" ?>;
var staff_dep = <?php echo "$staff_dep" ?>; 
var next_num = <?php echo "$next_num" ?>; 
var town_hidden = <?php echo "$town_hidden" ?>;  
var ltype = <?php echo "$ltype" ?>; 
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
    
    <form id="freps_params" name="reps_params" method="post" action="spravka_main_build_html.php" target="_blank">
        
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
                <input name="dt_b" type="text" size="10" class="dtpicker" id ="fdt_b" value= "" />
                По     
                <input name="dt_e" type="text" size="10" class="dtpicker" id ="fdt_e" value= "" />                
                &nbsp;&nbsp;                
                <label><input type="checkbox" name="to_xls" id="fxls" value="1" /> Звіт в EXCEL</label> 
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
                <label>Книга/особовий рахунок &nbsp &nbsp
                    <input name="book" type="text" id = "fbook" size="5" value= "" data_old_value = ""/>
                </label> 
                <label> /
                    <input name="code" type="text" id = "fcode" size="10" value= "" data_old_value = ""/>            
                </label> &nbsp &nbsp
                <button type="button" class ="btn btnSmall" id="btPaccntAdd">+ до списку</button> &nbsp &nbsp
                <button type="button" class ="btn btnSmall" id="btPaccntOpen">Відкрити список</button>
                <button type="button" class ="btn btnSmall" id="btPaccntDtChange">Змінити дати у списку </button>
                <br/>
                <input  name="paccnt_name" type="text" id = "fpaccnt_name" size="50" value= "" data_old_value = "" readonly />
                <input  name="paccnt_addr" type="text" id = "fpaccnt_addr" size="50" value= "" data_old_value = "" readonly />
                <button type="button" class ="btnSel" id="btPaccntSel"></button>
                <button type="button" class ="btnClear" id="btPaccntClear"></button>

            </p>            
            <div id="pFullParam" class="pane" >
            <p>
                <label>Номер запиту &nbsp &nbsp
                    <input name="num_input" type="text" size="10" id ="fnum_input" value= "" />
                </label> 

                <label>Дата запиту &nbsp &nbsp
                    <input name="dt_input" type="text" size="10" class="dtpicker" id ="fdt_input" value= "" />
                </label> 
            </p>
            
            <p>            
                <label>Кількість членів родини &nbsp &nbsp
                    <input name="people_count" type="text" size="10" id ="fpeople_count" value= "" />
                </label> 
                (вказувати тільки в разі наявності запиту з інформацією про кількість членів родини!)
            </p>
            
            <p>            
                <label>Опалювальна площа &nbsp &nbsp
                    <input name="heat_area" type="text" size="10" id ="fheat_area" value= "" />
                </label> 
                (вказувати тільки в разі наявності запиту з інформацією про опалювальну площу!)
            </p>                

            <p>            
                <label>Соціальний норматив користування послугами з електропостачання для субсидії&nbsp &nbsp
                    <input name="social_norm" type="text" size="10" id ="fsocial_norm" value= "" /> 
                </label> 
                (вказувати тільки в разі наявності запиту!)
            </p>                
            
            <p>                        
              <label><input type="checkbox" name="hotw" id="fhotw" value="1" /> 
               Наявність гарячої води  </label> 
            </p>            
            <p>                        
              <label><input type="checkbox" name="hotw_gas" id="fhotw_gas" value="1" /> 
               Наявність газового водонагрівача  </label> 
            </p>            
            <p>                        
              <label><input type="checkbox" name="coldw" id="fcoldw" value="1" /> 
               Наявність холодної води  </label> 
            </p>            

            <p>                        
              <label><input type="checkbox" name="plita" id="fplita" value="1" /> 
               стаціонарна електроплита  </label> 
            </p>            
            
            <p>                        
              <label><input type="checkbox" name="show_dte" id="fshow_dte" value="1" /> 
               Друк боргу на кінець місяця  </label> 
            </p>            
            </div>
            <p>            
                <input name="id_person" type="hidden" id = "fid_person" size="10" value= "<?php echo "$id_person" ?>" data_old_value = ""/>                             
                <label>Оператор
                  <input name="person" type="text" id = "fperson" size="40" value= "<?php echo "$name_person" ?>" data_old_value = ""/>
                </label> 
                <button type="button" class ="btnSel" id="btPersonSel"></button>      
                <button type="button" class ="btnClear" id="btPersonClear"></button>                       
                &nbsp;&nbsp;&nbsp;    
                <button type="button" class ="btn btnSmall" id="toggle_param">Всі параметри</button>                                
            </p>

            <p>                        
              <label><input type="checkbox" name="show_norm" id="fshow_norm" value="1" checked /> 
               Показувати пільгову норму споживання </label> 
            </p>            
            
            <p>                        
              <label><input type="checkbox" name="write_protocol" id="fwrite_protocol" value="1" /> 
               Писати в журнал </label> 
            </p>            
            <p style="color:blue; background-color: white " id="old_sprav_info">  </p>
            
            <input type="hidden" name="grid_params" id="fgrid_params" value="" />  
            <input type="hidden" name="report_caption" id="freport_caption" value="" />  
            <input type="hidden" name="archive_print" id="fgrid_params" value="0" />  
        </DIV>

        <DIV id="preps_buttons">

            <div class="ui-corner-all ui-state-default" id="pActionBar">
                <div class="pane">
                    <table>
                        <tr>
                            <td> 
                                Довідки
                            </td>
                            <td>
                                Довідки списком
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <button name="submitButton" type="submit" class ="btn btnRep" id="bt_spraw1"  value="sprav1" highlight="#fdt_rep,#fbook,#fcode">Для районів області(без кВтг)</button>
                            </td>
                            <td>
                                <button name="submitButton" type="submit" class ="btn btnRep" id="bt_spraw1_list"  onclick="setTemplate('sprav1')" value="sprav1_list" highlight="#fdt_rep,#fbook,#fcode">Для районів області (без кВтг)</button>    
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <button name="submitButton" type="submit" class ="btn btnRep" id="bt_spraw1new"  value="sprav1new" highlight="#fdt_rep,#fbook,#fcode">Для Деснянського/Новозаводського района (без кВтг)</button>
                            </td>
                            <td>
                                <button name="submitButton" type="submit" class ="btn btnRep" id="bt_spraw1new_list" onclick="setTemplate('sprav1new')" value="sprav1new_list" highlight="#fdt_rep,#fbook,#fcode">Для Деснянського/Новозаводського района (без кВтг)</button>    
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <button name="submitButton" type="submit" class ="btn btnRep" id="bt_sprav6"  value="sprav6" highlight="#fdt_rep,#fbook,#fcode">По місяцям (кВтг+тариф)</button>
                            </td>
                            <td>
                                <button name="submitButton" type="submit" class ="btn btnRep" id="bt_sprav6_list"  onclick="setTemplate('sprav6')" value="sprav6_list" highlight="#fdt_rep,#fbook,#fcode">По місяцям (кВтг+тариф)</button>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <button name="submitButton" type="submit" class ="btn btnRep" id="bt_sprav12"  value="sprav12" highlight="#fdt_rep,#fbook,#fcode">По місяцям (кВтг+оплата)</button>
                            </td>
                            <td>
                                <button name="submitButton" type="submit" class ="btn btnRep" id="bt_sprav12_list"  onclick="setTemplate('sprav12')" value="sprav12_list" highlight="#fdt_rep,#fbook,#fcode">По місяцям (кВтг+оплата)</button>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <button name="submitButton" type="submit" class ="btn btnRep" id="bt_spravlgt"  value="spravlgt" highlight="#fdt_rep,#fbook,#fcode">Компенсація пільги</button>
                            </td>
                            <td>
                                &nbsp;
                            </td>
                        </tr>
                        
                    </table>
                    
                </div>     
               
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

<div id="dialog-confirm" title="Друк переліку" style="display:none;z-index: 1001;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text"> Виберіть варіант друку </p></p>
</div>

<form id="fadr_sel_params" name="adr_sel_params" target="adr_win" method="post" action="adr_tree_selector.php">
    <input type="hidden" name="select_mode" id="fadr_sel_params_select_mode" value="1" />
    <input type="hidden" name="address" id="fadr_sel_params_address" value="" />
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

<div id="grid_spravkacache" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 1000;" >   
     <table id="spravka_cache_table" style="margin:1px;"></table>
     <div id="spravka_cache_tablePager"></div>
</div>       



<iframe id="frame_hidden" src="" style="display:none"></iframe>

</body>
</html>


