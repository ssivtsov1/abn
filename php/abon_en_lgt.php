<?php

header('Content-type: text/html; charset=utf-8');

require_once 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();
//error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];

$nm1 = "АЕ-Пільги абонентів - інформація з УПСЗН";

start_mpage($nm1);
head_addrpage();
/*
$id_paccnt =0;
if ((isset($_POST['id_paccnt']))&&($_POST['id_paccnt']!=''))    
{
  $id_paccnt = $_POST['id_paccnt']; 
}

if ((isset($_POST['mmgg']))&&($_POST['mmgg']!=''))    
{
  $mmgg = $_POST['mmgg'];  
}
else
 */ 
{
  $Query=" select to_char(fun_mmgg(), 'DD.MM.YYYY') as mmgg;";
  $result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
  $row = pg_fetch_array($result);
  $mmgg = $row['mmgg'];
}

print('<link rel="stylesheet" type="text/css" href="css/ded_styles.css" /> ');
print('<link rel="stylesheet" type="text/css" href="css/layout-default-latest.css" /> ');
    
print('<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script> ');
print('<script type="text/javascript" src="js/jquery.form.js"></script>');
print('<script type="text/javascript" src="js/jquery.validate.min.js?version='.$app_version.'" ></script>');
print('<script type="text/javascript" src="js/jquery.ui.datepicker-uk.js"></script> ');
print('<script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script> ');
print('<script type="text/javascript" src="js/jquery.layout.min-latest.js"></SCRIPT>');
print('<script type="text/javascript" src="js/date-uk-UA.js"></script> ');
print('<script type="text/javascript" src="abon_en_lgt.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="lgt_files_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="js/spin.min.js"></script> ');
print('<script type="text/javascript" src="abon_en_main_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="lgt_list_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="check_session.js?version='.$app_version.'"></script> ');

$lstatus=      DbTableSelList($Link,'lgi_load_state_tbl','id','name');
$lstatusselect=DbTableSelect($Link,'lgi_load_state_tbl','id','name');

$lgi_budjet=DbTableSelList($Link,'lgi_budjet_tbl','id','name');
$lgi_calc= DbTableSelList($Link,'lgi_calc_header_tbl','id','name');
$lregion=      DbTableSelList($Link,'cli_region_tbl','id','name');
$lregionselect=DbTableSelect($Link,'cli_region_tbl','id','name'); 
$r_edit = CheckLevel($Link,'пільги-завантаження',$session_user);
$town_hidden=CheckTownInAddrHidden($Link);

?>
<style type="text/css"> 
#pmain_content {padding:3}

.tabPanel {padding:1px; margin:1px; }
.ui-tabs .ui-tabs-panel {padding:1px;}

.ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default {  color: #111111; }
#pFooterBar {font-size:12px; }
#pActionBar { position:relative; height:23px; border-width:1px; width:"100%"; padding:4px; margin: 1px;}    
</style>   


<script type="text/javascript">

var mmgg = <?php echo "'$mmgg'" ?>;
var lstatus = <?php echo "$lstatus" ?>;
var lgi_calc = <?php echo "$lgi_calc" ?>; 
var lgi_budjet = <?php echo "$lgi_budjet" ?>; 
var lregion = <?php echo "$lregion" ?>;
var r_edit = <?php echo "$r_edit" ?>;  
var town_hidden = <?php echo "$town_hidden" ?>;  
 
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

        <div class="ui-corner-all ui-state-default" id="pActionBar">
            <form id="fLoad" method="post" action="abon_en_lgt_load_edit.php">
                <!--<label>Період
                    <input name="mmgg" type="text" size="15" class="dtpicker" id ="fmmgg" value= "" />
                </label>
                <button type="button" class ="btn btnSmall" id="bt_sel">Вибрати</button>		    
            -->
                <input type="hidden" name="oper" id="foper" value="" />            
                <input type="hidden" name="id_file" id = "fid_file" size="10" value= ""  />
                <label>Файл з УПСЗН
                    <input style ="width:200px" name="name_file" type="text" id = "fname_file"  value= "" data_old_value = "" readonly  />
                </label> &nbsp;
                <button type="button" class ="btnSel" id="show_files"></button>
                
                &nbsp;&nbsp;&nbsp;
                
                <label> Новий
                       <input id="flgt_file" type="file" size="70" name="lgt_file"/>
                </label>   
                
                &nbsp;&nbsp;

                <label>Регіон
                     <select name="id_region" size="1" id = "fid_region"  value= "" data_old_value = "">
                         <?php echo "$lregionselect" ?><option value="999">-Невідомий-</option>;
                     </select>                    
                </label> 
                
                <button name="submitButton" type="submit" class ="btn" id="bt_load" value="load" >Завантажити</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_apply" value="apply" >Застосувати</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_delete" value="delete" >Видалити</button>
                
                <label style="display:none;color:blue;" id="load_in_progress">Файл завантажується...</label >
                
            </form>
        </div>
        

        <div id="lgt_list" > 
           <table id="lgt_table" style="margin:1px;"></table>
           <div id="lgt_tablePager"></div>
        </div>
        
        <div id="grid_selfile" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 100000;" >   
            <table id="loaded_files_table" style="margin:1px;"></table>
            <div id="loaded_files_tablePager"></div>
        </div>    
        
    </DIV>

        
    <div id="dialog_editLgt" style="display:none; overflow:visible; padding:1px; ">        
        <form id="fLgtEdit" name="fLgtParam"  target=""  method="post" action="abon_en_lgt_edit.php" >
            <div id="pLgtParam_left" class="pane" style="padding:1px">
                <input type="hidden" name="id" id = "fid" size="10" value= ""  />                 
                <input type="hidden" name="oper" id="foper" value="" />            
                    
                <div class="pane"  >
                    <p>            
                        <input name="id_lgt" type="hidden" id = "fid_lgt" size="10" value= "" data_old_value = ""/>    
                        <label> Пільга
                            <input name="code_lgt" type="text" id = "fcode_lgt" size="5" value= "" data_old_value = "" readonly/>
                            <input name="name_lgt" type="text" id = "fname_lgt" size="70" value= "" data_old_value = "" readonly />
                        </label> 
                        <button type="button" class ="btnSel" id="btLgtSel"></button>      
                    </p>
                    <p>    
                        <label> Адреса: вул. 
                            <input name="street" type="text" id = "fstreet" size="20" value= "" data_old_value = "" readonly/>
                            буд.
                            <input name="house" type="text" id = "fhouse" size="7" value= "" data_old_value = "" readonly/>
                            корп.
                            <input name="korp" type="text" id = "fkorp" size="7" value= "" data_old_value = "" readonly/>
                            кв.
                            <input name="flat" type="text" id = "fflat" size="7" value= "" data_old_value = "" readonly/>
                        </label> 
                    
                    </p>
                    <p>
                        <input name="id_paccnt" type="hidden" id = "fid_paccnt" size="10" value= "" data_old_value = ""/>    
                        <label>Книга/рахунок &nbsp &nbsp
                            <input name="book" type="text" id = "fbook" size="5" value= "" data_old_value = ""/>
                        </label> 
                        <label> /
                            <input name="code" type="text" id = "fcode" size="10" value= "" data_old_value = ""/>            
                        </label>                 
                        <button type="button" class ="btn btnSmall" id="btPaccntFind">+</button>                       
                        <input  name="addr" type="text" id = "fpaccnt_name" size="50" value= "" data_old_value = "" readonly />
                        <button type="button" class ="btnSel" id="btPaccntSel"></button>
                    </p>            
                        
                        
                    <p>            
                        <label> Особа, якій надана пільга
                            <input name="fio_lgt" type="text" id = "ffio_lgt" size="50" value= "" data_old_value = ""/>
                        </label> 
                    </p>                            
                    <p>                                
                        <label>Ідент. номер
                            <input name="ident_cod_l" type="text" id = "fident_cod_l" size="15" value= "" data_old_value = ""/>
                        </label> 
                            
                        <label>Номер пасп.
                            <input name="n_doc" type="text" id = "fn_doc" size="20" value= "" data_old_value = ""/>
                        </label> 
                            
                    </p>
                </div>   
                    
                <div class="pane"  >    
                    <p>
                        <label>Дата початку
                            <input name="date_b" type="text" size="13" class="dtpicker" id ="fdate_b" value= "" data_old_value = "" />
                        </label>
                        <label>Дата закінчення
                            <input name="date_e" type="text" size="13" class="dtpicker" id ="fdate_e" value= "" data_old_value = "" />
                        </label>
                            
                    </p>
                        
                </div>    

                <div class="pane"  >    
                    <p>
                    <label>Статус 
                        <select name="status" size="1" id = "fstatus"  value= "" data_old_value = "">
                            <?php echo "$lstatusselect" ?>;
                        </select>                    
                    </label> 
                            
                    </p>
                        
                </div>    
                
            </div>
                
            <div class ="pane" id="pLgtParam_buttons"  >
                
                <button name="submitButton" type="submit" class ="btn" id="bt_edit" value="edit" >Записати</button>
                <button name="resetButton" type="button" class ="btn" id="bt_reset" value="reset" >Відмінити</button>         
                    
                <button type="button" class ="btn" id="btPaccntOpen">Відкрити картку</button>
            </div>
        </form>
    </div>    

    
    
<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>


<form id="freps_params" name="reps_params" method="post" action="rep_main_build_html.php" target="_blank">
        
 <input type="hidden" name="template_name" id="ftemplate_name" value="lgtload_list" />      
 <input type="hidden" name="oper" id="foper" value="lgtload_list" />      
 <input type="hidden" name="period_str" id = "fperiod_str" size="30" value= ""  />
 
 <input type="hidden" name="dt_b" size="10"  id ="fdt_b" value= "" />
 <input type="hidden" name="to_xls" id="fxls" value="0" />  
 
 <input type="hidden" name="grid_params" id="fgrid_params" value="" />  
 <input type="hidden" name="filename" id="ffilename" value="" />  
 <input type="hidden" name="id_file" id="fid_file" value="" />  
</form> 
 

<div id="grid_selabon" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 100000;" >
             <table id="abon_en_sel_table" style="margin:1px;"></table>
             <div id="abon_en_sel_tablePager"></div>
</div>

<div id="grid_sellgt" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 100000;" > 
         <table id="lgt_sel_table" style="margin:1px;"></table>
         <div id="lgt_sel_tablePager"></div>
</div>    


<div id="calc_progress" style ="" > 
    <p> Йде завантаження і обробка... </p>
    
    <div id ="progress_indicator" style="position:absolute; top:50px; left:50%"> </div>
    <div id ="progress_time" style="position:absolute; height:20px;  top:80px; left:40%"> </div>
</div>

<div id="dialog-confirm" title="Друк журналу?" style="display:none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text"> Виберіть варіант друку </p></p>
</div>


<form id="fpaccnt_sel_params" name="paccnt_sel_params" target="paccnt_win" method="post" action="abon_en_main.php">
   <input type="hidden" name="select_mode" value="1" />
</form>

<form id="flgt_sel_params" name="lgt_sel_params" target="lgt_win" method="post" action="lgt_list.php">
   <input type="hidden" name="select_mode" value="1" />
</form>

<DIV style="display:none;">
    <form id="fpaccnt_params" name="paccnt_params" method="post" action="abon_en_paccnt.php">
        <input type="text" name="mode" id="pmode" value="1" />
        <input type="text" name="id_paccnt" id="pid_paccnt" value="0" />         
        <input type="text" name="paccnt_info" id="ppaccnt_info" value="" />    
        <input type="text" name="paccnt_book" id="ppaccnt_book" value="" />                  
        <input type="text" name="paccnt_code" id="ppaccnt_code" value="" />                  
        <input type="text" name="paccnt_name" id="ppaccnt_name" value="" />                  
      
    </form>
</DIV>

<?php

end_mpage();
?>


