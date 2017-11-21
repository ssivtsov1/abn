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
                to_char(fun_mmgg(), 'YYYY-MM-DD') as pmmgg,
                to_char(fun_mmgg()::date+'1 month - 1 day'::interval, 'DD.MM.YYYY') as mmgg_next;";

$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$mmgg = $row['mmgg'];
$pmmgg = $row['pmmgg'];
$mmgg_next = $row['mmgg_next'];


$enter_mode=0;

$Query="  select value_ident::int from syi_sysvars_tbl where ident='ind_enter_mode' ;";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
if ($result) 
{
 while($row = pg_fetch_array($result)) 
  {
    $enter_mode = $row['value_ident'];  
  }
}

$lioper=DbTableSelList($Link,'indic_opration','id','name');
$lioperselect=DbTableSelect($Link,'indic_opration','id','name');

$lzones=DbTableSelList($Link,'eqk_zone_tbl','id','nm');
$lzoneselect=DbTableSelect($Link,'eqk_zone_tbl','id','nm');

$lindicoper =DbTableSelList($Link,'cli_indic_type_tbl','id','name');
$lindicoperselect =DbTableSelect($Link,'cli_indic_type_tbl','id','name');


$id_pack = sql_field_val('id_pack','int');
$id_sector = sql_field_val('id_sector', 'int');
$sector = sql_field_val('sector', 'str');
$id_runner = sql_field_val('id_runner', 'int'); 
$runner = $_POST['runner'];
$id_ioper = sql_field_val('id_ioper', 'int');
$num_pack = sql_field_val('num_pack', 'str');
$dt_pack = sql_field_val('dt_pack', 'str');

$pid_paccnt = sql_field_val('id_paccnt', 'int');

if ($pid_paccnt!="null")
{
    $Query=" select t.* ,to_char(t.dt_pack, 'DD.MM.YYYY') as dt_pack_txt,
        s.name as sector, s.code, p.represent_name as runner
        from prs_runner_paccnt as rp
        join ind_pack_header as t on (t.id_sector = rp.id_sector and t.work_period = '$pmmgg' )
        join prs_runner_sectors as s on (s.id = t.id_sector)
        left join prs_persons as p on (p.id = t.id_runner) 
        where rp.id_paccnt = $pid_paccnt   limit 1; ";

    $result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
    $rows = pg_num_rows($result);
    if ($rows>0)
    {
      $row = pg_fetch_array($result);
      $id_pack = $row['id_pack'];
      $id_sector = $row['id_sector'];
      $sector = $row['sector'];
      $id_runner = $row['id_runner'];
      $runner = $row['runner'];
      $num_pack = $row['num_pack'];
      $dt_pack = $row['dt_pack_txt'];
    }
    else
    {
      $sector = '----';
      die("Відомость за поточний період для дільниці, до якої включено даного абонента, ще не створена!");
    }
    
}

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

print('<script type="text/javascript" src="ind_packs_input.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="ind_abon_info_data.js?version='.$app_version.'"></script> ');

echo <<<SCRYPT
<script type="text/javascript">
    //var lioper = $lioper;
    var lzones = $lzones;
    var id_pack = $id_pack;
    var lindicoper =$lindicoper;
    var mmgg = '{$mmgg}';                
    var mmgg_next = '{$mmgg_next}';
    var enter_mode = $enter_mode;
    var pid_paccnt = $pid_paccnt;    
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
        <p> <b> Показники лічильників  </b> 
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
               <label id="last_ind_text" style="display: inline-block; width: 500px; text-align: right; font-size: 15px; color:blue; ">  </label>
        </p>    
        <p>  Відомість № <b> <?php echo "$num_pack" ?> </b>від <b><?php echo "$dt_pack" ?> </b>
        
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
               <label id="plan_ind_text" style="display: inline-block; width: 450px; text-align: right; font-size: 15px; color:blue; ">  </label>
        
        </p>            
        <p>  Дільниця: <b> <?php echo "$sector" ?></b></p>            
        <p>  Кур'єр/контролер: <b> <?php echo "$runner" ?> </b> 
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <label>Тип показників 
                  <select name="id_operation_def" size="1" id="fid_operation_def" value= ""  >
                       <?php echo "$lindicoperselect" ?>;
                  </select>                    
            </label>      
        
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
                &nbsp;&nbsp;
                <button name="RefreshButton" type="button" class ="btn" id="bt_refresh" value="refresh" >Оновити відомість</button>                
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
    
     <div id="grid_abonindinfo" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 100000;" >   
             <table id="indic_info_table" style="margin:1px;"></table>
             <div id="indic_info_tablePager"></div>
     </div>    
    

<?php

print('<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>');


end_mpage();
?>