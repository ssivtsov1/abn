<?php

header('Content-type: text/html; charset=utf-8');

require_once 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();
//error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];

$nm1 = "АЕ-Субсидія абонентів";

start_mpage($nm1);
head_addrpage();

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
{
  $Query=" select to_char(fun_mmgg(), 'DD.MM.YYYY') as mmgg;";
  $result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
  $row = pg_fetch_array($result);
  $mmgg = $row['mmgg'];
}

$Query=" select count(*) as cnt from cli_region_tbl ;";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$region_cnt = $row['cnt'];

$Query=" select syi_resid_fun() as id_dep ;";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$id_dep = $row['id_dep'];

if (($region_cnt>1)&&($id_dep!=310))
    $region_required=1;
else
    $region_required=0;

print('<link rel="stylesheet" type="text/css" href="css/ded_styles.css" /> ');
print('<link rel="stylesheet" type="text/css" href="css/layout-default-latest.css" /> ');
    
print('<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script> ');
print('<script type="text/javascript" src="js/jquery.form.js"></script>');
print('<script type="text/javascript" src="js/jquery.validate.min.js?version='.$app_version.'" ></script>');
print('<script type="text/javascript" src="js/jquery.ui.datepicker-uk.js"></script> ');
print('<script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script> ');
print('<script type="text/javascript" src="js/jquery.layout.min-latest.js"></SCRIPT>');
print('<script type="text/javascript" src="js/date-uk-UA.js"></script> ');
print('<script type="text/javascript" src="abon_en_subs.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="js/spin.min.js"></script> ');
print('<script type="text/javascript" src="check_session.js?version='.$app_version.'"></script> ');

$lregionselect=DbTableSelect($Link,'cli_region_tbl','id','name'); 
$r_load = CheckLevel($Link,'субсідія-завантаження',$session_user);
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
var id_paccnt = <?php echo "'$id_paccnt'" ?>;
var r_load = <?php echo "$r_load" ?>; 
var region_required = <?php echo "$region_required" ?>; 
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
            <form id="fSubsLoad" method="post" action="abon_en_subs_load_edit.php">
                <label>Період
                    <input name="mmgg" type="text" size="15" class="dtpicker" id ="fmmgg" value= "" />
                </label>
                <button type="button" class ="btn btnSmall" id="bt_sel">Вибрати</button>		    
            
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                
                <label> Файл субсидії
                       <input type="file" size="70" name="subs_file"/>
                </label>    
                <button name="submitButton" type="submit" class ="btn" id="bt_load" value="load" >Завантажити</button>
                <label style="display:none;color:blue;" id="load_in_progress">Файл завантажується...</label >
                
                &nbsp;&nbsp; &nbsp;  

                <label>Регіон
                     <select name="id_region" size="1" id = "fid_region"  value= "" data_old_value = "">
                         <?php echo "$lregionselect" ?><option value="999">-Невідомий-</option>;
                     </select>                    
                </label> 
                
            </form>
        </div>
        
        
       <div id="tab_subs" > 
          <table id="subs_table" style="margin:1px;"></table> 
          <div id="subs_tablePager"></div>
           
          <div class="ui-corner-all ui-state-default" id="pFooterBar">
           <label>Всього : обов'язковий платіж
                <input name="fcnt_all" type="text" size="10"  id ="fsum_ob_pay" value= "" readonly/>
           </label>
            &nbsp;&nbsp;
           <label> сума всього 
                <input name="fsum_all" type="text" size="10"  id ="fsum_subs_all" value= "" readonly/>
           </label>
            &nbsp;&nbsp;
           <label> сума за місяць
                <input name="fsum_all" type="text" size="10"  id ="fsum_subs_month" value= "" readonly/>
           </label>
            &nbsp;&nbsp;
           <label> перерахунок
                <input name="fsum_all" type="text" size="10"  id ="fsum_recalc" value= "" readonly/>
           </label>

          </div>
           
       </div>

    </DIV>

    
<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>


<form id="freps_params" name="reps_params" method="post" action="rep_main_build_html.php" target="_blank">
        
 <input type="hidden" name="template_name" id="ftemplate_name" value="subs_list" />      
 <input type="hidden" name="oper" id="foper" value="subs_list" />      
 <input type="hidden" name="period_str" id = "fperiod_str" size="30" value= ""  />
 
 <input type="hidden" name="dt_b" size="10"  id ="fdt_b" value= "" />
 <input type="hidden" name="to_xls" id="fxls" value="0" />   
 
 <input type="hidden" name="grid_params" id="fgrid_params" value="" />  
 <input type="hidden" name="id_region"   id="fid_region"  value= "" />
 
</form> 
 

<div id="calc_progress" style ="" > 
    <p> Йде завантаження і обробка... </p>
    
    <div id ="progress_indicator" style="position:absolute; top:50px; left:50%"> </div>
    <div id ="progress_time" style="position:absolute; height:20px;  top:80px; left:40%"> </div>
</div>

<div id="dialog-confirm" title="Друк журналу" style="display:none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text"> Виберіть варіант друку </p></p>
</div>

<?php

end_mpage();
?>


