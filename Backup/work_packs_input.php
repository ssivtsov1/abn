<?php

header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();
error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];


$nmp1 = "АЕ-Рознесення показань";
  
start_mpage($nmp1); //заголовок
head_addrpage();  // хедер, подключение библиотек

$Query=" select to_char(fun_mmgg(), 'DD.MM.YYYY') as mmgg, 
     to_char(fun_mmgg()::date+'1 month - 1 day'::interval, 'DD.MM.YYYY') as mmgg_next;";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$mmgg = $row['mmgg'];
$mmgg_next = $row['mmgg_next'];

$lioper=DbTableSelList($Link,'indic_opration','id','name');
$lioperselect=DbTableSelect($Link,'indic_opration','id','name');

$lzones=DbTableSelList($Link,'eqk_zone_tbl','id','nm');
$lzoneselect=DbTableSelect($Link,'eqk_zone_tbl','id','nm');

$lindicoper =DbTableSelList($Link,'cli_indic_type_tbl','id','name');
$lindicoperselect =DbTableSelect($Link,'cli_indic_type_tbl','id','name');

$id_pack = sql_field_val('id','int');

//$id_sector = sql_field_val('id_sector', 'int');
//$sector = sql_field_val('sector', 'str');
$id_runner = sql_field_val('id_position', 'int'); 
$runner = $_POST['position'];

//$id_ioper = sql_field_val('id_ioper', 'int');
  
//$num_pack = sql_field_val('num_pack', 'str');
$dt_work = sql_field_val('dt_work', 'str');


print('<link rel="stylesheet" type="text/css" href="css/ded_styles.css" /> ');
print('<link rel="stylesheet" type="text/css" href="css/layout-default-latest.css" /> ');
print('<script type="text/javascript" src="js/jquery.layout.min-latest.js"></SCRIPT>');
print('<script type="text/javascript" src="js/jquery.form.js"></script>');
print('<script type="text/javascript" src="js/jquery.validate.min.js?version='.$app_version.'" ></script>');
print('<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script> ');
print('<script type="text/javascript" src="js/jquery.ui.datepicker-uk.js"></script> ');
print('<script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script> ');
print('<script type="text/javascript" src="js/str_pad.js"></script>');
print('<script type="text/javascript" src="js/date-uk-UA.js"></script> ');

print('<script type="text/javascript" src="work_packs_input.js?version='.$app_version.'"></script> ');

echo <<<SCRYPT
<script type="text/javascript">
    //var lioper = $lioper;
    var lzones = $lzones;
    var id_pack = $id_pack;
    var lindicoper =$lindicoper;
    var mmgg = '{$mmgg}';                
    var mmgg_next = '{$mmgg_next}';   
    var dt_work = '{$dt_work}';   
</script>
SCRYPT;

?>

</head>
<body >


    <DIV id="pmain_footer">
        <a href="javascript:void(0)" id="debug_ls1">show debug window</a> 
        <a href="javascript:void(0)" id="debug_ls2">hide debug window</a> 
        <a href="javascript:void(0)" id="debug_ls3">clear debug window</a>
    </DIV>

    <DIV id="pmain_center" style="padding:2px; margin:3px;">    
        <div id="pheader" style="padding:2px; margin:3px;">  
        <div class="pane" >    
        <p> <b> Показники лічильників контрольного огляду </b></p>    
        <p> від <b><?php echo "$dt_work" ?> </b></p>            
        <p>  Контролер: <b> <?php echo "$runner" ?> </b> 
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        </p>    
        
        <p>            
            <label>Дата зняття показників
                <input name="dt_indic" type="text" size="12" value="" id="fdt_indic" class="dtpicker" data_old_value = "" />
            </label>
            
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <label> Пошук (книга/рах)
                <input name="book" type="text" size="7" value="" id="find_book"  /> /
            </label>
            <input name="code" type="text" size="7" value="" id="find_code"  />
            
        </p>
        
        </div>    
        </div>     

        <div id="pIndic_table" style="padding:2px; margin:3px;">    
            <table id="indic_table" > </table>
            <div   id="indic_tablePager" ></div>
        </div>    

        <div id="pBottom" style="padding:2px; margin:3px;">    
                <button name="OkButton" type="button" class ="btn" id="bt_save" value="add" >Записати</button>                     
                <button name="CancelButton" type="button" class ="btn" id="bt_close" value="reset" >Закрити</button>         
        </div>    

    </div>        
    

      <div id="dialog-confirm" title="" style="display:none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text"> </p></p>
      </div>

    <div id="dialog-changedate" title="Дата редагування" style="display:none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text">Вкажіть дату редагування </p></p>
        <br>
        <input name="date_change" type="text" size="20" class="dtpicker" id ="fdate_change"/>
    </div>

<?php

print('<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>');


end_mpage();
?>