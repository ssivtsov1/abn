<?php

header('Content-type: text/html; charset=utf-8');

require_once 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();
//error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];

$nm1 = "АЕ-Показники СМАРТ";
 
start_mpage($nm1);
head_addrpage();

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
print('<script type="text/javascript" src="abon_en_smart.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="dov_smart_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="abon_en_main_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="js/spin.min.js"></script> ');
print('<script type="text/javascript" src="check_session.js?version='.$app_version.'"></script> ');

$lstatus=      DbTableSelList($Link,'ind_smart_load_state_tbl','id','name');
$lstatusselect=DbTableSelect($Link,'ind_smart_load_state_tbl','id','name');
$town_hidden=CheckTownInAddrHidden($Link);
$lindicst = 'null: ;0:Поточні;1:Старий період;2:Не знайдено';

$r_edit = CheckLevel($Link,'показники-смарт',$session_user);
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
var lindicst = <?php echo "'$lindicst'" ?>; 
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
   <a href="javascript:void(0)" id="show_peoples">Исполнители</a>

</DIV>  

    <DIV id="pmain_content">

        <div id="pHeaders_table" style="padding:2px; margin:3px;">    
            
            
            <div class="ui-corner-all ui-state-default" id="pActionBar">
                <form id="fLoad" method="post" action="abon_en_smart_load_edit.php">
                    <label>Період
                        <input name="mmgg" type="text" size="15" class="dtpicker" id ="fmmgg" value= "" />
                    </label>
                    <button type="button" class ="btn btnSmall" id="bt_sel">Вибрати</button>		    
                        
                    <input type="hidden" name="oper" id="foper" value="" />            
                        
                    &nbsp;&nbsp;&nbsp;
                        
                    <label> Завантажити файл
                        <input id ="fsmart_file" type="file" size="70" name="smart_file"/>
                    </label>    
                    <button name="submitButton" type="submit" class ="btn" id="bt_load" value="load" >Завантажити</button>
                    <button name="submitButton" type="submit" class ="btn" id="bt_apply" value="apply" >Застосувати</button> 
                        
                    <label style="display:none;color:blue;" id="load_in_progress">Файл завантажується...</label >
                        
                </form>
            </div>
            
            <table id="headers_table" > </table>
            <div   id="headers_tablePager" ></div>
                
        </div>
        
        <div id="pIndic_table" style="padding:2px; margin:3px;">    
            <table id="indic_table" > </table>
            <div   id="indic_tablePager" ></div>
        </div>    
       
    </div>    

    
        
    <div id="dialog_editHeader" style="display:none; overflow:visible; padding:1px; ">        
        <form id="fHeaderEdit" name="fLgtParam"  target=""  method="post" action="abon_en_smart_headers_edit.php" >
            <div class="pane" style="padding:1px">
                <input type="hidden" name="id" id = "fid" size="10" value= ""  />                 
                <input type="hidden" name="oper" id="foper" value="" />            
                    
                <div class="pane"  >
                    <p>            
                        <label> Файл
                            <input name="name_file" type="text" id = "fname_file" size="50" value= "" data_old_value = "" readonly/>
                        </label> 
                    </p>
                        
                    <p>
                        <input name="id_house" type="hidden" id = "fid_house" size="10" value= "" data_old_value = ""/>    
                        <label> Озн.
                            <input name="ident" type="text" id = "fident" style="width: 75px;" size="10" value= "" data_old_value = "" readonly />
                        </label> 
                        <label> Адреса
                            <input name="addr_str" type="text" id = "faddr_str" style="width: 410px;" value= "" data_old_value = "" readonly />            
                        </label>                 
                        <label> Книга
                            <input name="book" type="text" id = "fbook" style="width: 60px;" value= "" data_old_value = "" readonly />            
                        </label>                 
                        <button type="button" class ="btnSel" id="btHouseSel"></button>
                    </p>            

                </div>   
                    
                <div class="pane"  >    
                    <p>
                        <label> Дата показників
                            <input name="date_ind" type="text" size="13" class="dtpicker" id ="fdate_ind" value= "" data_old_value = "" />
                        </label>

                        <label> Період
                            <input name="mmgg" type="text" size="13" class="dtpicker" id ="fmmgg" value= "" data_old_value = "" />
                        </label>

                        <label> Дата завантаження
                            <input name="dt" type="text" size="18" id ="fdt" value= "" data_old_value = "" readonly />
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
                
            <div class ="pane" id="pHeaderParam_buttons"  >
                
                <button name="submitButton" type="submit" class ="btn" id="bt_edit" value="edit" >Записати</button>
                <button name="resetButton" type="button" class ="btn" id="bt_reset" value="reset" >Відмінити</button>         
                    
            </div>
        </form>
    </div>    

    
    <div id="dialog_indicform" style="display:none; overflow:visible; padding:1px; ">        
        <form id="fIndicEdit" name="fLgtParam"  target=""  method="post" action="abon_en_smart_indic_edit.php" >
            <div class="pane" style="padding:1px">
                <input type="hidden" name="id" id = "fid" size="10" value= ""  />                 
                <input type="hidden" name="oper" id="foper" value="" />            
                    
                <div class="pane"  >
                        
                    <p>
                        <input name="id_paccnt" type="hidden" id = "fid_paccnt" size="10" value= "" data_old_value = ""/>    
                        <input name="bookcode" type="hidden" id = "fbookcode" size="10" value= "" data_old_value = ""/>    
                        <label>Книга/рахунок &nbsp &nbsp
                            <input name="book" type="text" id = "fbook" size="5" value= "" data_old_value = ""/>
                        </label> 
                        <label> /
                            <input name="code" type="text" id = "fcode" size="10" value= "" data_old_value = ""/>            
                        </label>                 
                        <button type="button" class ="btn btnSmall" id="btPaccntFind">+</button>                       
                    </p>                            
                    <p>                                
                        <input  name="address" type="text" id = "faddress" size="70" value= "" data_old_value = "" readonly />
                        <button type="button" class ="btnSel" id="btPaccntSel"></button>
                    </p>    
                    <p>                                
                        <label>Зона А
                            <input name="ind_a" type="text" id = "find_a" size="15" value= "" data_old_value = "" readonly />
                        </label> 
                        <label>B
                            <input name="ind_b" type="text" id = "find_b" size="15" value= "" data_old_value = "" readonly />
                        </label> 
                        <label>C
                            <input name="ind_c" type="text" id = "find_c" size="15" value= "" data_old_value = "" readonly />
                        </label> 
                        <label>D
                            <input name="ind_d" type="text" id = "find_d" size="15" value= "" data_old_value = "" readonly />
                        </label> 
                     </p>

                </div>   
                    
                <div class="pane"  >    

                     <label>Поточні пок.
                         <input name="indic" type="text" id = "findic" size="20" value= "" data_old_value = ""/>
                      </label> 

                     <label>Дата показників
                         <input name="ind_date" type="text" size="13" class="dtpicker" id ="find_date" value= "" data_old_value = "" />
                     </label>
                        
                </div>    
                
            </div>
                
            <div class ="pane" id="pIndParam_buttons"  >
                
                <button name="submitButton" type="submit" class ="btn" id="bt_edit" value="edit" >Записати</button>
                <button name="resetButton" type="button" class ="btn" id="bt_reset" value="reset" >Відмінити</button>         
                    
            </div>
        </form>
    </div>    
    
    
    <div id="grid_selsmart" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 100000;" >   
         <table id="dov_smart_table" style="margin:1px;"></table>
         <div id="dov_smart_tablePager"></div>
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
  

<div id="calc_progress" style ="" > 
    <p> Йде завантаження і обробка... </p>
    
    <div id ="progress_indicator" style="position:absolute; top:50px; left:50%"> </div>
    <div id ="progress_time" style="position:absolute; height:20px;  top:80px; left:40%"> </div>
</div>

<div id="dialog-confirm" title="Друк журналу?" style="display:none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text"> Виберіть варіант друку </p></p>
</div>

<div id="grid_selabon" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 100000;" >
             <table id="abon_en_sel_table" style="margin:1px;"></table>
             <div id="abon_en_sel_tablePager"></div>
</div>

<form id="fpaccnt_sel_params" name="paccnt_sel_params" target="paccnt_win" method="post" action="abon_en_main.php">
   <input type="hidden" name="select_mode" value="1" />
</form>

<form id="flgt_sel_params" name="lgt_sel_params" target="lgt_win" method="post" action="lgt_list.php">
   <input type="hidden" name="select_mode" value="1" />
</form>

<?php

end_mpage();
?>


