<?php

header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();
error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];

if ((isset($_POST['select_mode']))&&($_POST['select_mode']=='1'))
{
   $selmode = 1; 
   $nmp1 = "АЕ-Групи пільг вибір";
}
else
{
   $selmode = 0; 
   $nmp1 = "АЕ-Групи пільг";
    
}    


$gn1 = "lgt_category";

$gn1_table = $gn1."_table";
$gn1_pager = $gn1."_tablePager";

$lgi_doc=     DbTableSelList($Link,'lgi_document_tbl','id','name');
$lgi_docselect=DbTableSelect($Link,'lgi_document_tbl','id','name');

$lcategoryselect = DbTableSelect($Link," (select id,name from lgi_kategor_tbl order by lvl, ident) as ss ",'id','name');

$r_edit = CheckLevel($Link,'довідник-групи пільг',$session_user);

start_mpage($nmp1); //заголовок
head_addrpage();  // хедер, подключение библиотек


print('<link rel="stylesheet" type="text/css" href="css/ded_styles.css" /> ');
    
print('<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script> ');
print('<script type="text/javascript" src="js/jquery.form.js"></script>');
print('<script type="text/javascript" src="js/jquery.validate.min.js?version='.$app_version.'" ></script>');
print('<script type="text/javascript" src="js/jquery.ui.datepicker-uk.js"></script> ');
print('<script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script> ');

print('<link rel="stylesheet" type="text/css" href="css/layout-default-latest.css" /> ');
print('<script type="text/javascript" src="js/jquery.layout.min-latest.js"></SCRIPT>');

print('<script type="text/javascript" src="lgt_category.js?version='.$app_version.'"></script> ');


echo <<<SCRYPT
<script type="text/javascript">
    var selmode = $selmode;        
    var r_edit = $r_edit;
</script>
SCRYPT;

//middle_mpage(); // верхнее меню
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
</DIV>

<div id="grid_dform">    
<table id="<?php echo $gn1_table ?>" > </table>
<div id="<?php echo $gn1_pager ?>" ></div>
</div>    



        <div id="dialog_editform" style="display:none; overflow:visible; padding:1px; ">        
        <form id="fLgtCategoryEdit" name="fLgtCategoryEdit" method="post" action="lgt_category_edit.php" >
          <div class="pane"  >
            <input name="id" type="hidden" id="fid" value="" />
            <input name="oper" type="hidden" id="foper" value="" />            
            <input name="isLeaf" type="hidden" id="fisLeaf" value="" />            
            <div class="pane"  >    

                <p>            
                    <label>Категорія
                        <input name="name" type="text" size="60" id ="fname" value= "" data_old_value = "" />
                    </label>

                    <label>Код
                        <input name="ident" type="text" size="20" id ="fident" value= "" data_old_value = "" />
                    </label>
                </p>
                <p>
                    <label> Належить до групи
                        <select name="id_parent" size="1" id="fid_parent" value= "" data_old_value = "" >
                            <?php echo "$lcategoryselect" ?>;
                        </select>                    
                    </label> 
                </p>

                <p>
                    <label> Документ
                        <select name="id_document" size="1" id="fid_document" value= "" data_old_value = "" >
                            <?php echo "$lgi_docselect" ?>;
                        </select>                    
                    </label> 
                </p>
                
            </div>    
            <div class="pane"  >    
                <p>
                    <label>Дата початку
                        <input name="dt_b" type="text" size="15" class="dtpicker" id ="flgtdt_b" value= "" data_old_value = "" />
                    </label>
                    <label>Дата закінчення
                        <input name="dt_e" type="text" size="15" class="dtpicker" id ="fdt_e" value= "" data_old_value = "" />
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


<?php
print('<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>');


end_mpage();
?>