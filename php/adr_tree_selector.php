<?php

header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();
error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];
session_write_close();


if ((isset($_POST['select_mode']))&&($_POST['select_mode']!='0'))
{ 
   $selmode = $_POST['select_mode']; 
   $nmp1 = "АЕ-Адреса вибір";
}
else
{
   $selmode = 0; 
   $nmp1 = "АЕ-Адреса";
    
}    
$id_class ='';
if ((isset($_POST['address']))&&($_POST['address']!==''))
{
 $address_obj = $_POST['address'];
 
 $addr_array =  explode(",", trim($address_obj,"()"));
 $id_class = $addr_array[0];
}   
else
{
  $address_obj ="";
}    

if ((isset($_POST['full_addr_mode']))&&($_POST['full_addr_mode']!==''))
{
 $full_addr_mode = $_POST['full_addr_mode'];
}   
else
{
  $full_addr_mode ="";
}    

$lkindselect = DbTableSelect($Link," (select id,name from adk_class_tbl order by ident, id) as ss ",'id','name');

$SQL = "select addr_district from syi_resinfo_tbl where id_department = syi_resid_fun();  ";

$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$id_district = $row['addr_district'];

$SQL = "select value_ident from syi_sysvars_tbl where ident = 'id_region'; ";

$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$id_region = $row['value_ident'];

if ($id_class!='')
{
 $SQL = "select CASE WHEN(select trim( trailing  '0' from ident )::varchar from adi_class_tbl where id = $id_class) like 
 (select trim( trailing  '0' from ident )||'%'::varchar from adi_class_tbl where id =   $id_district) THEN  $id_district
                    WHEN(select trim( trailing  '0' from ident )::varchar from adi_class_tbl where id = $id_class) like 
 (select trim( trailing  '0' from ident )||'%'::varchar from adi_class_tbl where id =   $id_region) THEN $id_region
 ELSE 0 END as id_root ;";

 $result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link) );
 $row = pg_fetch_array($result);
 $id_root = $row['id_root'];

}
else
{
  $id_root = $id_district;
}
 

if (CheckLevel($Link,'довідник-адреси',$session_user)==3)
   $r_edit = 1;
else
   $r_edit = 0;


start_mpage($nmp1); //заголовок
head_addrpage();  // хедер, подключение библиотек


print('<link rel="stylesheet" type="text/css" href="css/ded_styles.css" /> ');
    
print('<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script> ');
print('<script type="text/javascript" src="js/jquery.form.js"></script>');
print('<script type="text/javascript" src="js/jquery.validate.min.js?version='.$app_version.'" ></script>');
print('<script type="text/javascript" src="js/jquery.ui.datepicker-uk.js"></script> ');
print('<script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script> ');

print('<script src="./js/tree/jquery.cookie.js" type="text/javascript"></script>');
print('<link href="./js/tree/src/skin/ui.dynatree.css" rel="stylesheet" type="text/css">');
print('<script src="./js/tree/src/jquery.dynatree.js" type="text/javascript"></script>');
print('<script src="js/tree/contextmenu/jquery.contextMenu-custom.js" type="text/javascript"></script>');
print('<link href="js/tree/contextmenu/jquery.contextMenu.css" rel="stylesheet" type="text/css" >');


print('<link rel="stylesheet" type="text/css" href="css/layout-default-latest.css" /> ');
print('<script type="text/javascript" src="js/jquery.layout.min-latest.js"></SCRIPT>');

print('<script type="text/javascript" src="adr_tree_selector.js?version='.$app_version.'"></script> ');

?>

<script type="text/javascript">
    var selmode = <?php echo "$selmode" ?>;        
    var address_obj = '<?php echo "$address_obj"?>';    
    var full_addr_mode = '<?php echo "$full_addr_mode"?>';    

    var id_ukraine = 0; // 10
    var id_region = '<?php echo "$id_region"?>';    
    var id_district = '<?php echo "$id_district"?>';    
    var id_root = '<?php echo "$id_root"?>';    
    
    var r_edit = '<?php echo "$r_edit"?>';    

</script>

<style type="text/css">
  
  ul.dynatree-container span.pref {color: blue; margin-right:5px;}
  ul.dynatree-container span.postf {color: blue; margin-left:5px;}
  ul.dynatree-container span.o_nam {color:purple ; margin-left:5px;}
  
  #pAddres { padding:1px;font-size: 14px;}
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

<div class="pane" id="pmain_center" >
    
    <div style="padding:4px;" class="pane" id="pTreeHeader" >
        <button type="button" class ="btn" id="btnAddr_lev1" >Країна</button>
        <button type="button" class ="btn" id="btnAddr_lev2" >Область</button>
        <button type="button" class ="btn" id="btnAddr_lev3" >Район</button>
        
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

       <label>Пошук
            <input type="text" style="width: 200px;" id ="fsearch" value= ""  />
       </label>
       <button type="button" class ="btn" id="btn_search" >Пошук</button>
       
       <label> 
             <input type="checkbox" name="localsearch" id="flocalsearch" value="1" />
             з вибраного місця
       </label>
       
        
    </div>

    <div class="pane" id="pTreePanel" >

    </div>
    
    <div class="pane" id="pAddres" >

        <div class="pane" >        
           <label id ="lSelected_addr" style="width: 500px;" > Виберіть адресу </label>
           
           <label style="float:right; color:darkslategray" id ="current_addr_id"> </label>
        </div>    
        <form id="fAddressEdit" name="fAddressEdit" method="post" action="" >
          <div class="pane" id="pHouse" >
            <input name="id_class" type="hidden" id="fid_class" value="" />

                <p>            
                    <label>Індекс
                        <input name="indx" type="text" size="8" id ="findx" value= "" data_old_value = "" />
                    </label>

                    <label> &nbsp;&nbsp; Будинок
                        <input name="house" type="text" size="7" id ="fhouse" value= "" data_old_value = "" />
                    </label>
                    <label> /
                        <input name="slash" type="text" size="5" id ="fslash" value= "" data_old_value = "" />
                    </label>

                    <label>&nbsp;&nbsp;Корпус
                        <input name="korp" type="text" size="5" id ="fkorp" value= "" data_old_value = "" />
                    </label>

                    <label>&nbsp;&nbsp;Кв.
                        <input name="flat" type="text" size="6" id ="fflat" value= "" data_old_value = "" />
                    </label>
                    <label> /
                        <input name="f_slash" type="text" size="5" id ="ff_slash" value= "" data_old_value = "" />
                    </label>
                    
                </p>
                <p>
                    <label>Примітка
                        <input name="note" type="text" size="60" id ="fnote" value= "" data_old_value = "" />
                    </label>
                </p>
                
            </div>    
            <div class="pane" >
                <button name="submitButton" type="button" class ="btn" id="bt_ok" value="edit" > Ок </button>
                <button name="resetButton" type="button" class ="btn" id="bt_reset" value="bt_reset" >Відмінити</button>         
            </div>
            
      </FORM>
                    
        
    </div>
    
</div>


        <div id="dialog_editform" style="display:none; overflow:visible; padding:1px; ">        
        <form id="fClassificatorEdit" name="fClassificatorEdit" method="post" action="adr_tree_selector_edit.php" >
          <div class="pane"  >
            <input name="id_parent" type="hidden" id="fid_parent" value="" />
            <input name="oper" type="hidden" id="foper" value="" />            
            <div class="pane"  >    
                <p id ="lparent_name">            
                    
                </p>                    
                <p>            
                    <label>Назва
                        <input name="name" type="text" size="60" id ="fname" value= "" data_old_value = "" />
                    </label>
                    <label> Код
                        <input name="id" type="text" size="10" id="fid" value="" readonly />
                    </label>                        
                </p>
                <p>
                    <label>Повна назва
                        <input name="name_full" type="text" size="80" id ="fname_full" value= "" data_old_value = "" />
                    </label> 
                </p>
                <p>
                    <label>Попередня назва
                        <input name="name_old" type="text" size="80" id ="fname_old" value= "" data_old_value = "" />
                    </label> 
                </p>
                
                <p>
                    <label> Тип
                        <select name="idk_class" size="1" id="fidk_class" value= "" data_old_value = "" >
                            <?php echo "$lkindselect" ?>;
                        </select>                    
                    </label> 
                </p>

                <p>            
                    <label>Ідентифікатор
                        <input name="ident" type="text" size="20" id ="fident" value= "" data_old_value = "" />
                    </label>

                    <label> Поштовий індекс
                        <input name="indx" type="text" size="20" id ="findx" value= "" data_old_value = "" />
                    </label>

                </p>
                
            </div>    
        </div>    
        <div class="pane" >
                <button name="submitButton" type="submit" class ="btn" id="bt_edit" value="edit" >Записати</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_add" value="add" style="display:none;">Записати новий</button>                
                <button name="resetButton" type="button" class ="btn" id="bt_reset" value="bt_reset" >Відмінити</button>         
        </div>
            
      </FORM>
            
      </div>                
    
    <div id="dialog-confirm" title="" style="display:none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text"> </p></p>
    </div>
    
  <ul id="myMenu" class="contextMenu">
    <li class="addchild"><a href="#contAdd">Добавить</a></li>
    <li class="edit"><a href="#contEdit">Изменить</a></li>
    <li class="delete"><a href="#contDelete">Удалить</a></li>
    <li class="quit separator"><a href="#contCancel">Отменить</a></li>
  </ul>

<?php
print('<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>');


end_mpage();
?>