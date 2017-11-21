<?php

header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();
error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];
/*
if (isset($_SESSION['id_sess'])) {
  $ids = $_SESSION['id_sess'];

  $res_par = sel_par($ids);
  $row_par = pg_fetch_array($res_par);
  $cons = $row_par['con_str1'];
  //$Q = $row_par['qu'];
  $Link = pg_connect($cons);
}
*/
$nmp1 = "АЕ-Схема обладнання абонентів";

if ((isset($_POST['id_paccnt']))&&($_POST['id_paccnt']!=''))    
{
  $id_acc = $_POST['id_paccnt']; 
}
//$id_acc = 1;
//$id_client = 1;

start_mpage($nmp1); //заголовок
head_addrpage();  // хедер, подключение библиотек

print('<link rel="stylesheet" type="text/css" href="css/layout-default-latest.css" /> ');
print('<link rel="stylesheet" type="text/css" href="css/ded_styles.css" /> ');

print('<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script> ');
print('<script type="text/javascript" src="js/jquery.ui.datepicker-uk.js"></script> ');
print('<script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script> ');
//print('<script type="text/javascript" src="dov_meters_sel.js"></script> ');
//print('<script type="text/javascript" src="dov_compi_sel.js"></script> ');
print('<script type="text/javascript" src="js/jquery.form.js"></script>');
print('<script type="text/javascript" src="js/jquery.validate.min.js?version='.$app_version.'" ></script>');
print('<script src="./js/tree/jquery.cookie.js" type="text/javascript"></script>');
//print('<link href="./js/tree/src/skin/ui.dynatree.css" rel="stylesheet" type="text/css">');
//print('<script src="./js/tree/src/jquery.dynatree.js" type="text/javascript"></script>');

print('<script type="text/javascript" src="js/jquery.layout.min-latest.js"></SCRIPT>');

print('<script type="text/javascript" src="dov_meters_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="dov_compi_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="dov_cable_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="dov_corde_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="dov_switch_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="dov_fuse_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="dov_compensator_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="dov_tp_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="abon_en_main_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="eqp_tree.js?version='.$app_version.'"></script> ');

$lmeters=DbTableSelList($Link,'eqk_meter_tbl','id','name');
$lphase=DbTableSelList($Link,'eqk_phase_tbl','id','name');
$lmateral=DbTableSelList($Link,'eqk_materals_tbl','id','name');
$lswgrp=DbTableSelList($Link,'eqk_switchs_gr_tbl','id','name');
$lvolt=DbTableSelList($Link,'eqk_voltage_tbl','id','voltage');

$lvoltage=DbTableSelect($Link,'eqk_voltage_tbl','id','voltage');
$lpillar=DbTableSelect($Link,'eqi_pillar_tbl','id','name');

//$lzones=DbTableSelList($Link,'eqk_zone_tbl','id','nm');
//$lzoneselect=DbTableSelect($Link,'eqk_zone_tbl','id','nm');

$lmeterplaceselect =DbTableSelect($Link,'eqk_meter_places_tbl','id','name');

$town_hidden=CheckTownInAddrHidden($Link);

/*
<style type="text/css"> 

	html, body {
		width:		100%-4;
		height:		100%-4;					
		padding:	2;
		margin:		2;
		overflow:	auto; 
	}

	#layout_container {
		background:	#999;
		min-height:	800px;
		min-width:	600px;
		position:	absolute;
		top:		35px;	
		bottom:		3px;	
		left:		5px;
		right:		5px;
                overflow:       visible;
	}

        .pane {	display: none; }
       
</style>   
*/   
?>

<style type="text/css"> 
.pane {	margin:3px;  background-color: #CCCCCC; border-color: #444444; border-style: solid; border-width:1px; padding: 5px; }

/*#pActionBar { position:relative; height:28px; border-width:1; width:expression(document.body.clientWidth > 800 ? "100%" : "800px"); }*/
/*#pActionBar { background: url("images/ui-bg_glass_75_dadada_1x400.png") repeat-x scroll 50% 50% rgb(218, 218, 218); }*/
/*#pFooterBar { position:relative; height:20px; border-width:1;}*/
/*#pPanel1 {position:relative; height:600; border-width:1;}*/
/*#pTreePanel {position:relative; height:550; width:300px; float:left;border-width:1;}*/
/*#pPanel2 {float:left;height:550; width:700px; border-width:1;}*/
#pCommonParamPanel {margin:3px; border-color: #777777; border-style: solid; border-width:1px; padding: 5px;}
#pMeterParam, #pLineAParam, #pLineCParam, #pSwitchParam, #pCompensParam,#pFuseParam  {margin:3px; background-color: #DDDDDD; border-color: #777777; border-style: solid; border-width:1px; padding: 5px;}

/*#tab_history {position:relative; float:top} */

#types_list {
            display:none; 
            position:absolute;
            left:0;
            margin:0 0 0 -1px;
            padding:0;
            list-style:none;
            border-color: #777777; border-style: solid; border-width:1px;
            background:#fff;
            z-index:100000;
}

#types_list li {
        display:block;
        width:300px;
        background:#ccc;
        position:relative;
        z-index:600;
        margin:0 1px;
        border-top:1px solid #fff;
}


#types_list a {
        display:block; 
        height:20px;
        padding: 2px 5px;
           
        /*font-weight:600;             */
        text-decoration:none;
        text-align:center;
        color:#333;
       
}
        
#types_list a:hover {
        text-decoration:underline; 
        color:#00f;
}        
    
     
.ui-dynatree-disabled ul.dynatree-container
{
	opacity: 1;
	background-color:#e0e0e0 ;
}

.ui-tabs .ui-tabs-panel { display: block; border-width: 0; padding:1px; background: none; }
#pmain_header {padding:1px}

#leqptype {
   

}
</style>   


<script type="text/javascript">
    
var lkind_meter = <?php echo "$lmeters" ?>;
var lphase = <?php echo "$lphase" ?>;
var lmateral = <?php echo "$lmateral" ?>;
var lswgrp = <?php echo "$lswgrp" ?>;
var id_acc = <?php echo "$id_acc" ?>;

var lvolt = <?php echo "$lvolt" ?>;

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

<div class="pane ui-corner-all" id="pActionBar">
    <!--<button type="button" class ="btn" id ="bt_newtree">Нова гілка схеми</button> -->
    <button type="button" class ="btn" id="bt_new">Нове обладнання</button>		
   
        <ul id ="types_list">
            <!--<li data-key="12" data-name="Точки обліку"><a href="#">Точки обліку</a></li>-->
            <li data-key="2" data-name="Трансформатори силові"> <a href="#">Трансформатори силові</a></li>
            <li data-key="6" data-name="Лінії кабельні"> <a href="#">Лінії кабельні</a></li>
            <li data-key="7" data-name="Линії повітряні"> <a href="#">Линії повітряні</a></li>
            <!--<li data-key="3" data-name="Комутаційне обладнання"> <a href="#">Комутаційне обладнання</a></li>-->
            <!--<li data-key="5" data-name="Запобіжники"> <a href="#">Запобіжники</a></li> -->
        </ul>
    <button type="button" class ="btn" id ="bt_del">Видалити</button>		
    <button type="button" class ="btn" id ="bt_refresh">Reload</button>		    
    <button type="button" class ="btn" id ="bt_ok">Ok</button>		    
    <button type="button" class ="btn" id ="bt_cancel">Cancel</button>		    
    
</div>

    <div class="pane" id="pTreePanel" >
            <table id="equipment_table" style="margin:1px;"></table>
            <div id="equipment_tablePager"></div>
    </div>

    <div class="pane" id="pPanel2" >
        
	<ul id="tabButtons">
		<li><a href="#tab_params">Обладнання</a></li>
                <li><a href="#tab_abon">Підключені абоненти</a></li>
		<li><a href="#tab_history">Історія змін</a></li>
	</ul>             
            
        <div id="tab_params" class="tabPanel ui-widget-content ui-tabs-hide" >        

        <form id="fCommonParam" method="post" action="eqp_tree_editmain.php" style="display:none;">
          <div class="pane" id="pCommonParamPanel" >
            <input name="id" type="hidden" id="fid" value="" />
            <input name="operation" type="hidden" value="0" />    
            <input name="change_date" type="hidden" value="" />    
            <input name="id_paccnt" type="hidden" id = "fid_paccnt" value = "" /> 
            <input name="id_client" type="hidden" id = "fid_client" value = "" /> 
            <input name="id_tree" type="hidden" id = "fid_tree" value = "" />             
            <input name="code_eqp_e" type="hidden" id = "fcode_eqp_e" value = "" />                         
            
            <input name="type_eqp" type="hidden" id = "ftype_eqp" value = "" /> 
            <b><p id="leqptype" > Оборудование </p> </b><br>
            
            <label>Назва 
            <input name="name_eqp" type="text" id = "fname_eqp" size="40" value= "" data_old_value = ""/>
            </label> 

            <label>Заводський № 
            <input name="num_eqp" type="text" id = "fnum_eqp" size="20" value= "" data_old_value = ""/>
            </label>  <br>

            <input name="addr" type="hidden" id = "faddr" size="10" value= "" data_old_value = ""/>    
            <label>Адреса 
            <input name="addr_str" type="text" id = "faddr_str" size="70" value= "" data_old_value = ""/>
            </label> 
            <button type="button" class ="btnSel" id="btAddrSel"></button>      
            <br>
            <label>Дата встановлення
            <input name="dt_install" type="text" size="20" value="" id="fdt_install" class="dtpicker" data_old_value = "" />
            </label>
            <label>Дата останньої заміни
            <input name="dt_change" type="text" size="20" value="" id="fdt_change" class="dtpicker" data_old_value = "" />
            </label> 
            <br>
            
            <label>
            <input type="checkbox" name="loss_power" id="floss_power" data_old_checked = "" />
            Розрахунок втрат</label>
            <label>
            <input type="checkbox" name="is_owner" id="fis_owner" data_old_checked = "" />
            Належить абоненту </label>
            <br>
            
            <label>Порядковий номер
            <input name="lvl" type="text" id = "flvl" size="5" value= "" data_old_value = ""/>
            </label>  <br>
            
        </div>    
    
            <input type="hidden" name="id_mp" id = "fid_mp" size="10" value= "" data_old_value = ""  /> 

            <div id ="pLineCParam" style="display:none;" >

              <div style="margin:3px;  border-color: #444444; border-style: solid; border-width:1px; padding: 5px; " >
                <p>
                <input type="hidden" name="id_type_cable" id = "fid_type_cable" size="10" value= "" data_old_value = "" /> 
                <label>Тип кабелю
                <input name="type_cable" type="text" id = "ftype_cable" size="40" value= "" data_old_value = "" readonly />
                </label> 
                <button type="button" class ="btnSel" id="show_cablelist"></button>
                <p> 
                <label> Протяжність, м
                <input name="length_cable" type="text" size="10" id = "flength_cable" value= "" data_old_value = "" />
                </label>
                </p>
                <p> 
                <label> Напруга, кВ
                    <select name="id_voltage_cable" size="1" id="fid_voltage_cable" value= "" data_old_value = "" >
                        <?php echo "$lvoltage" ?>;
                    </select>                    
                </label>
                </p>
                
              </div>                
            </div>                
            
            <div id ="pLineAParam" style="display:none;" >            
              <div style="margin:3px;  border-color: #444444; border-style: solid; border-width:1px; padding: 5px; " >
                <p>
                <input type="hidden" name="id_type_corde" id = "fid_type_corde" size="10" value= "" data_old_value = "" /> 
                <label>Тип проводів
                <input name="type_corde" type="text" id = "ftype_corde" size="40" value= "" data_old_value = "" readonly />
                </label> 
                <button type="button" class ="btnSel" id="show_cordelist"></button>
                
                <p> 
                <label> Протяжність, м
                <input name="length_corde" type="text" size="10" id = "flength_corde" value= "" data_old_value = "" />
                </label>
                </p>
                <p> 
                <label> Напруга, кВ
                    <select name="id_voltage_corde" size="1" id="fid_voltage_corde" value= "" data_old_value = "" >
                        <?php echo "$lvoltage" ?>;                        
                    </select>                    
                </label>
                </p>
                <p> 
                <label> Тип опори
                    <select name="id_pillar" size="1" id="fid_pillar" value= "" data_old_value = "" >
                        <?php echo "$lpillar" ?>;                        
                    </select>                    
                </label>
                </p>
                
              </div>                
            </div>                
          <!--  
            <div id ="pSwitchParam" style="display:none;" >            
                <div style="margin:3px;  border-color: #444444; border-style: solid; border-width:1px; padding: 5px; " >
                <p>
                <input type="hidden" name="id_type_switch" id = "fid_type_switch" size="10" value= "" data_old_value = "" /> 
                <label>Тип обладнання
                <input name="type_switch" type="text" id = "ftype_switch" size="40" value= "" data_old_value = ""  readonly />
                </label> 
                <button type="button" class ="btnSel" id="show_switchlist"></button>

                </div>                
            </div>                
          -->
            <div id ="pCompensParam" style="display:none;" >            
                <div style="margin:3px;  border-color: #444444; border-style: solid; border-width:1px; padding: 5px; " >
                <p>
                <input type="hidden" name="id_type_compens" id = "fid_type_compens" size="10" value= "" data_old_value = "" /> 
                <label>Тип трансформатора
                <input name="type_compens" type="text" id = "ftype_compens" size="40" value= "" data_old_value = "" readonly />
                </label> 
                <button type="button" class ="btnSel" id="show_compenslist"></button>

                </div>                
            </div>                
            <!--
            <div id ="pFuseParam" style="display:none;" >            
                <div style="margin:3px;  border-color: #444444; border-style: solid; border-width:1px; padding: 5px; " >
                <p>
                <input type="hidden" name="id_type_fuse" id = "fid_type_fuse" size="10" value= "" data_old_value = "" /> 
                <label>Тип запобіжника
                <input name="type_fuse" type="text" id = "ftype_fuse" size="40" value= "" data_old_value = "" readonly />
                </label> 
                <button type="button" class ="btnSel" id="show_fuselist"></button>

                </div>                
            </div>                
            -->
        <div class="pane" >
                <button name="submitButton" type="submit" class ="btn" id="bt_edit" value="bt_edit" >Записати</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_add" value="bt_add" style="display:none;">Записати новий</button>                
                <button name="resetButton" type="button" class ="btn" id="bt_reset" value="bt_reset" >Відмінити</button>         
                <button name="resetButton" type="button" class ="btn" id="bt_canceladd" value="bt_canceladd" style="display:none;" >Відмінити+</button>                         
        </div>
            
        </form>
        </div>
        
        <div id="tab_abon" class="tabPanel ui-widget-content ui-tabs-hide" >        
           <table id="abon_table" style="margin:1px;"></table>
           <div id="abon_tablePager"></div>
            
        </div>
        
        <div id="tab_history" class="tabPanel ui-widget-content ui-tabs-hide">                    
 
           <table id="hist1_table" style="margin:1px;"></table>
           <div id="hist1_tablePager"></div>

           <div id="hist2compens_div" style="display:none;"> 
             <table id="hist2compens_table" style="margin:1px;"></table>
             <div id="hist2compens_tablePager"></div>
           </div>
           <!--
           <div id="hist2switch_div" style="display:none;"> 
            <table id="hist2switch_table" style="margin:1px;"></table>
            <div id="hist2switch_tablePager"></div>
           </div>
           -->
           <div id="hist2linea_div" style="display:none;"> 
            <table id="hist2linea_table" style="margin:1px;"></table>
            <div id="hist2linea_tablePager"></div>
           </div>
           
           <div id="hist2linec_div" style="display:none;"> 
            <table id="hist2linec_table" style="margin:1px;"></table>
            <div id="hist2linec_tablePager"></div>
           </div>            
           <!--
           <div id="hist2meter_div" style="display:none;"> 
            <table id="hist2meter_table" style="margin:1px;"></table>
            <div id="hist2meter_tablePager"></div>
           </div>            
           -->
           <!--
           <div id="hist2fuse_div" style="display:none;"> 
            <table id="hist2fuse_table" style="margin:1px;"></table>
            <div id="hist2fuse_tablePager"></div>
           </div>
           
           <table id="hist3_table" style="margin:1px;"></table>
           <div id="hist3_tablePager"></div>
           -->
        </div>        
        
        </div>

            
            
    <div id="dialog-confirm" title="Удалить учет?" style="display:none;">
        <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text">Учет будет удален. Продолжить? </p></p>
    </div>
    
    
    <div id="dialog-changedate" title="Дата редагування" style="display:none;">
        <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text">Вкажіть дату редагування </p></p>
        <br>
        <input name="date_change" type="text" size="20" class="dtpicker" id ="fdate_change"/>
    </div>
    
    <div id="grid_selmeter" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1;z-index: 100000;" >   
        <table id="dov_meters_table" style="margin:1px;"></table>
        <div id="dov_meters_tablePager"></div>
    </div>    
    
    <div id="grid_selci" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1;z-index: 100000;" >   
        <table id="dov_compi_table" style="margin:1px;"></table>
        <div id="dov_compi_tablePager"></div>
    </div>    
    
    <div id="grid_selcable" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1;z-index: 100000;" >   
        <table id="dov_cable_table" style="margin:1px;"></table>
        <div id="dov_cable_tablePager"></div>
    </div>    
    
    <div id="grid_selcorde" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1;z-index: 100000;" >   
        <table id="dov_corde_table" style="margin:1px;"></table>
        <div id="dov_corde_tablePager"></div>
    </div>    
    
    <div id="grid_selswitch" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1;z-index: 100000;" >   
        <table id="dov_switch_table" style="margin:1px;"></table>
        <div id="dov_switch_tablePager"></div>
    </div>    
    
    <div id="grid_selcompens" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1;z-index: 100000;" >   
        <table id="dov_compensator_table" style="margin:1px;"></table>
        <div id="dov_compensator_tablePager"></div>
    </div>    

    <div id="grid_selfuse" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1;z-index: 100000;" >   
        <table id="dov_fuse_table" style="margin:1px;"></table>
        <div id="dov_fuse_tablePager"></div>
    </div>    

    <div id="grid_seltp" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1 ;z-index: 100000;" >   
        <table id="dov_tp_table" style="margin:1px;"></table>
        <div id="dov_tp_tablePager"></div>
    </div>    
    <!--
    <div id="dialog-newmeterzone" title="Нова зона" style="display:none;">
        <form>
            <p>    
                <label style="display: inline-block; width: 250px;" > Зона
                    <select style="float:right" name="id_zone" size="1" id="fid_zone" value= "" data_old_value = "" >
                        <?php //echo "$lzoneselect" ?>;                        
                    </select>                    
                </label>
            </p>   
            <p>           
                <label> Дата встановлення
                    <input  name="date_install" id ="fdate_install" type="text" size="19" class="dtpicker" />
                </label>    
            </p>           
        </form>
    </div>
    -->
    <div id="paccnt_meters_list" title ="Виберіть лічильник" style="padding:1px; display:none;" > 
        <table id="paccnt_meters_table" style="margin:1px;"></table>
        <div id="paccnt_meters_tablePager"></div>
    </div>

    <div id="grid_selabon" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 100000;" >
             <table id="abon_en_sel_table" style="margin:1px;"></table>
             <div id="abon_en_sel_tablePager"></div>
    </div>    
    
    
<DIV id ="pFooterBar" class="header-footer ui-corner-all" style="padding: 3px 5px 5px; text-align: center; margin-top: 1ex;">
			
</DIV>

<DIV>
    
    <form id="fadr_sel_params" name="adr_sel_params" target="adr_win" method="post" action="adr_tree_selector.php">
        <input type="hidden" name="select_mode" value="1" />
        <input type="hidden" name="address" id="fadr_sel_params_address" value="" />
    </form>
    
</DIV>
    
</DIV>    
<?php
print('<div id="message_zone" style ="padding: 5px;color: blue;" > ------------------- </div>');

print('<a href="javascript:void(0)" id="debug_ls1">show debug window</a> <br> ');
print('<a href="javascript:void(0)" id="debug_ls2">hide debug window</a> <br> ');
print('<a href="javascript:void(0)" id="debug_ls3">clear debug window</a>');

end_mpage();


?>
