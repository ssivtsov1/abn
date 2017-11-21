<?php

header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start(); 
error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];

$nmp1 = "АЕ-Показники з інтернет-кабінету";
  
start_mpage($nmp1); //заголовок
head_addrpage();  // хедер, подключение библиотек

$Query=" select to_char(fun_mmgg(), 'DD.MM.YYYY') as mmgg;";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$mmgg = $row['mmgg'];

$lid_status = 'null: ;0:Нові;1:Прийняті;2:Не прийняті-дата;3:Не прийн.-спожив;4:Відхилені опер.'; 

//$lzones=DbTableSelList($Link,'eqk_zone_tbl','id','nm');
//$lzoneselect=DbTableSelect($Link,'eqk_zone_tbl','id','nm');

//$lindicoper =DbTableSelList($Link,'cli_indic_type_tbl','id','name');
//$lindicoperselect =DbTableSelect($Link,'cli_indic_type_tbl','id','name');
//$staff_dep= DbTableSelList($Link,'prs_department','id','name');

$r_edit = CheckLevel($Link,'показники з інтернет кабінету',$session_user);
//$r_edit = 3;

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
print('<script type="text/javascript" src="ind_cabinet.js?version='.$app_version.'"></script> ');
//print('<script type="text/javascript" src="runner_sectors_sel.js?version='.$app_version.'"></script> ');
//print('<script type="text/javascript" src="staff_list_sel.js?version='.$app_version.'"></script> ');

print('<script type="text/javascript" src="check_session.js?version='.$app_version.'"></script> ');

echo <<<SCRYPT
<script type="text/javascript">

    var mmgg = '{$mmgg}';        
    var r_edit = $r_edit; 
    var lid_status = '{$lid_status}'; 
    var lid_operation = 'null: ;100:Кабінет;0:Call-центр IVR;1:Call-центр опер.;2:Call-центр SMS.;99:Оплата';
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
        <!--  <form id="fLoad" method="post" action="ind_cabinet_load_edit.php"> -->
            <input type="hidden" name="oper" id="foper" value="" />            
            <input type="hidden" name="alldata" id = "falldata" size="10" value= ""  />
             
            <label>Період
                <input name="mmgg" type="text" size="15" class="dtpicker" id ="fmmgg" value= "" />
            </label>
            &nbsp;&nbsp;
            <button type="button" class ="btn btnSmall" id="bt_sel">Вибрати</button>
            &nbsp;&nbsp;&nbsp;&nbsp;            
            
            <button type="button" class ="btn" id="bt_load" value="load" >Прийняти</button>            
            <button type="button" class ="btn" id="bt_cancel" value="cancel" >Відхилити</button>                        
        <!-- </form> -->
        </div>

        <div id="pIndic_table" style="padding:2px; margin:3px;">    
            <table id="indic_table" > </table>
            <div   id="indic_tablePager" ></div>
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

<form id="freps_params" name="reps_params" method="post" action="rep_main_build_html.php" target="_blank">
        
 <input type="hidden" name="template_name" id="ftemplate_name" value="cabinet_indic_list" />      
 <input type="hidden" name="oper" id="foper" value="cabinet_indic_list" />      
 <input type="hidden" name="period_str" id = "fperiod_str" size="30" value= ""  />
 
 <input type="hidden" name="dt_b" size="10"  id ="fdt_b" value= "" />
 <input type="hidden" name="to_xls" id="fxls" value="0" />  
 
 <input type="hidden" name="grid_params" id="fgrid_params" value="" />  
</form>     
    
<?php

print('<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>');


end_mpage();
?>