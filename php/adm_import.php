<?php

header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];

$nmp1 = "АЕ-Розрахунки та імпорт даних";


$cp1 = "Завантаження данних";
$gn1 = "dov_errlist";
$gn1_table = $gn1."_table";
$gn1_pager = $gn1."_tablePager";

start_mpage($nmp1); //заголовок
head_addrpage();  // хедер, подключение библиотек

$Query=" select to_char(fun_mmgg(), 'DD.MM.YYYY') as mmgg;";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$mmgg = $row['mmgg'];


$result = pg_query($Link, "select value_ident::int as value from syi_sysvars_tbl where ident='id_res'; ");
$row = pg_fetch_array($result);
$res_code =  $row['value'];

$r_close = CheckLevel($Link,'Закриття місяця',$session_user);
$lregionselect=DbTableSelect($Link,'cli_region_tbl','id','name'); 

print('<link rel="stylesheet" type="text/css" href="css/ded_styles.css" /> ');
    
print('<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script> ');
print('<script type="text/javascript" src="js/jquery.form.js"></script>');
print('<script type="text/javascript" src="js/jquery.validate.min.js?version='.$app_version.'" ></script>');

print('<script type="text/javascript" src="adm_import.js?version='.$app_version.'"></script>'); 
print('<script type="text/javascript" src="js/spin.min.js"></script> ');
print('<link rel="stylesheet" type="text/css" href="css/layout-default-latest.css" /> ');
print('<script type="text/javascript" src="js/jquery.layout.min-latest.js"></SCRIPT>');
print('<script type="text/javascript" src="js/jquery.ui.datepicker-uk.js"></script> ');
?> 

<style type="text/css"> 

  #bt_close_month   {color: #FFF !important; background: url("images/ui-bg_glass_45_c095a4_1x400.png") repeat-x scroll 50% 50% #C095A4 !important;}
  #bt_open_month   {color: #FFF !important; background: url("images/ui-bg_glass_45_0078ae_1x400.png") repeat-x scroll 50% 50% #CC0000 !important;}  
  #bt_calc_bill   {color: #FFF !important; background: url("images/ui-bg_glass_45_0078ae_1x400.png") repeat-x scroll 50% 50% #0078AE !important;}
  
</style>   

<script type="text/javascript">

  var mmgg = <?php echo "'$mmgg'" ?>;
  var res_code = <?php echo "$res_code" ?>;
  var r_close = <?php echo "$r_close" ?>;
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

<div id="pmain_center">    

        <div class="ui-corner-all ui-state-default" id="pActionBar">
            <label> Поточний робочий період
                <input name="mmgg" type="text" size="15" class="dtpicker" id ="fmmgg" value= "" />
            </label>
        </div>

    
        <div class="pane" id="pActionBar3">
                
                <label> Закриття місяця </label>    
                <button name="submitButton" type="button" class ="btn" id="bt_close_month"  >Закрити</button>
                <button name="submitButton" type="button" class ="btn" id="bt_open_month"  >Відкрити</button>
        </div>
    
        <div class="pane" id="pActionBar2">
                
                <label> Розрахунки </label>    
                <button name="submitButton" type="button" class ="btn" id="bt_saldo"  >Перерахувати сальдо</button>
                <button name="submitButton" type="button" class ="btn" id="bt_calc_corr"  >Розрахувати корегування</button>
                <button name="submitButton" type="button" class ="btn" id="bt_calc_plan"  >Показники по плану</button>
                <button name="submitButton" type="button" class ="btn" id="bt_calc_bill"  >Розрахувати рахунки</button>
        </div>
    
    
        <div class="pane" id="pActionBar1" style="display:none;">
            <form id="fLoad" method="post" action="adm_import_load_edit.php">
                
                <label> Завантажити файл данних
                       <input type="file" size="70" name="data_file"/>
                </label>    
                <button name="submitButton" type="submit" class ="btn" id="bt_load" value="load" >Завантажити</button>
                <label style="display:none;color:blue;" id="load_in_progress">Файл завантажується...</label >
                
            </form>
        </div>
    
        <div class="pane" id="pActionBar4">
                
                <label> Перевірити пільги  </label>    
                <button name="submitButton" type="button" class ="btn" id="bt_lgt_close"  >Перевірити</button> <br/>
                <p style="color:blueviolet;">
                    Не забувайте перевіряти терміни пільг перед формуванням рахунків!
                </p>
        </div>
    
        <div class="pane" id="pActionBar5">
            <form id="fGet" method="post" action="adm_import_getfile_data.php">
                Зформувати файл  : 
                <input name="oper" type="hidden" id="foper" value="get_pay" />            
                <label>Дата/месяц
                      <input name="dt_file" type="text" style="width: 80px;"  class="dtpicker" id ="fdt_file" value= "" data_old_value = "" />
                </label>
                <br/>
                Оплати для колл-центра
                <button name="submitButton" type="submit" class ="btn" id="bt_pay" value="get_pay" >Зформувати</button>
                <br/>
                Сальдо для банку
                <button name="submitButton" type="submit" class ="btn" id="bt_bank" value="get_bank" >Приватбанк</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_portmone" value="get_portmone" >Портмоне</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_familybank" value="get_familybank" >Банк Фамільний</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_familybank" value="get_allbank" >Уніврсальна вигрузка для банків</button>
                
                <label style="display:none;color:blue;" id="work_in_progress">Зачекайте...</label >

                <br/>
                Файли місячної звітності
                <div id="ff_all">                                                
                <button name="submitButton" type="submit" class ="btn" id="bt_zvit" value="get_zvit" >Звіт по реалізації</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_zon" value="get_zon" >По зонним</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_f10" value="get_f10" >Форма 10</button> <br/>
                </div>
                
                <div class ="pane" id="f10_neg">                 
                Форма 10 Ніжин РЕМ:
                <button name="submitButton" type="submit" class ="btn" id="bt_f10_neg_m" value="get_f10_neg_m" >Місто Ніжин</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_f10_neg_s" value="get_f10_neg_s" >Ніжинський район</button>
                </div>

                <div class ="pane" id="f10_pril">
                Форма 10 Прилуки РЕМ:
                <button name="submitButton" type="submit" class ="btn" id="bt_f10_pril_m" value="get_f10_pril_m" >Місто Прилуки</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_f10_pril_s" value="get_f10_pril_s" >Прилуцький район</button>
                </div>
                
                <div class="pane" id="ff_cher">                                
                Чернігів РЕМ: <br/>
                <button name="submitButton" type="submit" class ="btn" id="bt_zvit_cher_res" value="get_zvit_cher_res" >Звіт по реалізації</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_zon_cher_res" value="get_zon_cher_res" >По зонним</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_f10_cher_res" value="get_f10_cher_res" >Форма 10</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_call_poks_cher_res" value="get_call_poks_cher_res" >Для Call-центру(Poks)</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_win_poks_cher_res" value="get_win_poks_cher_res" >Poks для аналізу</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_call_zip_cher_res" value="get_call_center_cher_res" >Для Call-центру(архів)</button>
                </div>
                
                <div class="pane" id="ff_slav">                                
                Славутич: <br/>
                <button name="submitButton" type="submit" class ="btn" id="bt_zvit_slav" value="get_zvit_slav" >Звіт по реалізації</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_zon_slav" value="get_zon_slav" >По зонним</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_f10_slav"  value="get_f10_slav" >Форма 10</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_call_poks_slav" value="get_call_poks_slav" >Для Call-центру(Poks)</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_win_poks_slav" value="get_win_poks_slav" >Poks для аналізу</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_call_zip_slav" value="get_call_center_slav" >Для Call-центру(архів)</button>
                </div>
                
                <div class="pane" id="ff_mena">                                
                Мена: <br/>
                <button name="submitButton" type="submit" class ="btn" id="bt_zvit_mena" value="get_zvit_mena" >Звіт по реалізації</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_zon_mena" value="get_zon_mena" >По зонним</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_f10_mena" value="get_f10_mena" >Форма 10</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_call_poks_mena" value="get_call_poks_mena" >Для Call-центру(Poks)</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_win_poks_mena" value="get_win_poks_mena" >Poks для аналізу</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_call_zip_mena" value="get_call_center_mena" >Для Call-центру(архів)</button>
                </div>
                
                <div class="pane" id="ff_sosn">                                
                Сосниця: <br/>
                <button name="submitButton" type="submit" class ="btn" id="bt_zvit_sosn" value="get_zvit_sosn" >Звіт по реалізації</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_zon_sosn" value="get_zon_sosn" >По зонним</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_f10_sosn"  value="get_f10_sosn" >Форма 10</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_call_poks_sosn" value="get_call_poks_sosn" >Для Call-центру(Poks)</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_win_poks_sosn" value="get_win_poks_sosn" >Poks для аналізу</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_call_zip_sosn" value="get_call_center_sosn" >Для Call-центру(архів)</button>
                </div>

                
                <button name="submitButton" type="submit" class ="btn" id="bt_poks" value="get_poks" >Для Енерго(Poks)</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_call_poks" value="get_call_poks" >Для Call-центру(Poks)</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_win_poks" value="get_win_poks" >Poks для аналізу</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_periods" value="get_periods" >"Поименник" боржників</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_call_zip" value="get_call_center" >Для Call-центру(архів)</button>
                <label style="display:none;color:blue;" id="work_in_progress">Зачекайте...</label >
                
            </form>
        </div>

        <div class="pane" id="pActionBar6">
            <form id="fGetDBF" method="post" action="rep_f2_dbf.php">
                Зформувати dbf форми 2 : 
                <input name="oper" type="hidden" id="foper" value="dbf" />
                <input name="region" type="hidden" id="fregion" value="" />
                <label>Місяць
                      <input name="mmgg" type="text" style="width: 80px;"  class="dtpicker" id ="fmmgg_dbf" value= "" data_old_value = "" />
                </label> 
                
                <button name="submitButton" type="submit" class ="btn" id="btn_all" value="all" >Повний</button> <br/>

                <div id="f2_mena">
                Мена РЕМ:
                <button name="submitButton" type="submit" class ="btn" id="btn_mena" value="mena" >Менський район</button>
                <button name="submitButton" type="submit" class ="btn" id="btn_sosn" value="sosn" >Сосницький район</button>
                </div>
                
                <div id="f2_krk">
                Корюківка РЕМ:
                <button name="submitButton" type="submit" class ="btn" id="btn_krk" value="krk" >Корюківський район</button>
                <button name="submitButton" type="submit" class ="btn" id="btn_sosn" value="sosn" >Сосницький район</button>
                </div>
                
                <div id="f2_krp">
                Короп РЕМ:
                <button name="submitButton" type="submit" class ="btn" id="btn_krp" value="krp" >Коропський район</button>
                <button name="submitButton" type="submit" class ="btn" id="btn_sosn" value="sosn" >Сосницький район</button>
                </div>
                
                <div id="f2_brz">
                Борзна РЕМ:
                <button name="submitButton" type="submit" class ="btn" id="btn_brz" value="brz" >Борзнянський район</button>
                <button name="submitButton" type="submit" class ="btn" id="btn_sosn" value="sosn" >Сосницький район</button>
                </div>
                
                <div id="f2_mem">
                Чернігів МЕМ:
                <button name="submitButton" type="submit" class ="btn" id="btn_desn" value="desn" >Деснянський</button>
                <button name="submitButton" type="submit" class ="btn" id="btn_nowz" value="nowoz" >Новозаводський</button>
                <button name="submitButton" type="submit" class ="btn" id="btn_cher" value="cher" >Чернігівський</button> <br/>
                </div>
                <div id="f2_neg">                 
                Ніжин РЕМ:
                <button name="submitButton" type="submit" class ="btn" id="btn_neg_m" value="neg_m" >Місто Ніжин</button>
                <button name="submitButton" type="submit" class ="btn" id="btn_neg_s" value="neg_s" >Ніжинський район</button>
                </div>
                <div id="f2_pril">                 
                Прилуки РЕМ:
                <button name="submitButton" type="submit" class ="btn" id="btn_pril_m" value="pril_m" >Місто Прилуки</button>
                <button name="submitButton" type="submit" class ="btn" id="btn_pril_s" value="pril_s" >Прилуцький район</button>
                </div>

                <div id="f2_ns">                 
                Новгород Сіверський:
                <button name="submitButton" type="submit" class ="btn" id="btn_ns_m" value="ns_m" >Місто Новгород Сіверський</button>
                <button name="submitButton" type="submit" class ="btn" id="btn_ns_s" value="ns_s" >Новгород Сіверський район</button>
                </div>
                
                <div id="f2_cher">                                
                Чернігів РЕМ:
                <button name="submitButton" type="submit" class ="btn" id="btn_cher_res" value="cher_res" >Чернігівський район</button>
                <button name="submitButton" type="submit" class ="btn" id="btn_nowz2"    value="cher_nowoz" >Новозаводський</button>                
                <button name="submitButton" type="submit" class ="btn" id="btn_slav"     value="slav" >Славутич</button>
                </div>
                <label style="display:none;color:blue;" id="work_in_progress">Зачекайте...</label >
                
            </form>
        </div>

        <div class="pane" id="pActionBar7">
            <form id="fGetDBFBank" method="post" action="rep_oschad_dbf.php">
                Зформувати dbf для Ощадбанка : 
                <input name="oper" type="hidden" id="foper" value="dbf" /> 
                <label>Місяць
                      <input name="mmgg" type="text" style="width: 80px;"  class="dtpicker" id ="fmmgg2_dbf" value= "" data_old_value = "" />
                </label> 
                
                <button name="submitButton" type="submit" class ="btn" id="btn_desn" value="dbf" >Формувати</button>
                
                <label style="display:none;color:blue;" id="work_in_progress">Зачекайте...</label >
                
            </form>
        </div>

    
        <div class="pane" id="pActionBar8">
            <form id="fGetDBFUpszn" method="post" action="rep_upszn_dbf.php">
                 Електронний обмін УПСЗН: 
                <input name="oper" type="hidden" id="foper" value="dbf" /> 
                <!--
                <label>Місяць
                      <input name="mmgg" type="text" style="width: 80px;"  class="dtpicker" id ="fmmgg2_dbf" value= "" data_old_value = "" />
                </label> 
                --> 
                <label> Файл A...dbf
                       <input type="file" size="70" name="a_file"/>
                </label>    
                
                <label> Файл AF...dbf
                       <input type="file" size="70" name="af_file"/>
                </label>    

                <label> Файл TARIF12.dbf
                       <input type="file" size="70" name="tarif_file"/>
                </label>    
                
                <label>Регіон
                     <select name="id_region" size="1" id = "fid_region"  value= "" data_old_value = "">
                         <?php echo "$lregionselect" ?>
                     </select>                    
                </label>                 
                
                <button name="submitButton" type="submit" class ="btn" id="btn_desn" value="dbf" >Формувати</button>
                
                <label style="display:none;color:blue;" id="work_in_progress">Зачекайте...</label >
                
            </form>
        </div>
    
        <!--style="display:none;"--> 
        <div class="pane" id="pActionBar9" style="display:none;"  >
            
            <form id="fGetXml" method="post" action="rep_xml.php">
                Зформувати XML для педедачі показників : 
                <input name="oper" type="hidden" id="foper" value="xml" /> 
                <label>Місяць
                      <input name="mmgg" type="text" style="width: 80px;"  class="dtpicker" id ="fmmgg4_dbf" value= "" data_old_value = "" />
                </label> 
                
                <button name="submitButton" type="submit" class ="btn" id="btn_xml" value="xml" >Формувати</button>
                
            </form>
        </div>

    
</div>    
    
<div id="calc_progress" style ="" > 
    <p> Йде обробка даних... </p>
    
    <div id ="progress_indicator" style="position:absolute; top:50px; left:50%"> </div>
    <div id ="progress_time" style="position:absolute; height:20px;  top:80px; left:40%"> </div>
</div>    
    
<div id="plgt_oper_table"  title="" style="display:none; margin:1px; padding:1px;" >    
            <table id="lgt_oper_table" > </table>
            <div   id="lgt_oper_tablePager" ></div>
</div>    
    
    
<div id="dialog-confirm" title="Удалить учет?" style="display:none;">
	<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text">Учет будет удален. Продолжить? </p>
</div>

<div id="dialog-open-confirm" title="Відкрити закритий місяць?" style="display:none;">
	<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text">Щоб відкрити останній закритий період,вкажіть код та причину відкриття! </p>
        <br/>
        <label> Код:<input name="open_code" type="text" size="15"  id ="fopen_code"/></label>
        <br/>
        <label> Причина:<input name="open_reason" type="text" size="40"  id ="fopen_reason"/></label>
</div>
    
<iframe id="frame_hidden" src="" style="display:none"></iframe>    
<?php

print('<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>');

end_mpage();
?>