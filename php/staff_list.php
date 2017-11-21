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
   $nmp1 = "АЕ-Працівники та підрозділи вибір";
   
   $id_person= 0;          
   if (isset($_POST['id_person']))
   {
      $id_person= $_POST['id_person'];
   }
       
}
else
{
   $id_person= 0;              
   $selmode = 0; 
   $nmp1 = "АЕ-Працівники та підрозділи";
    
}    



//$lgi_doc=     DbTableSelList($Link,'lgi_document_tbl','id','name');
//$lgi_docselect=DbTableSelect($Link,'lgi_document_tbl','id','name');

$ldepselect = DbTableSelect($Link," (select id,name from prs_department order by lvl, name) as ss ",'id','name');

$r_edit = CheckLevel($Link,'довідник-працівники',$session_user);

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

print('<script type="text/javascript" src="staff_list.js?version='.$app_version.'"></script> ');


echo <<<SCRYPT
<script type="text/javascript">
    var selmode = $selmode;     
    var id_person = $id_person;        
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

<DIV id="pmain_center" style="margin:2px;padding:2px; ">
    
    <div id="pDepartmentTable" style="margin:2px;padding:2px; ">    
        <table id="dep_tree_table" > </table>
        <div id="dep_tree_tablePager"> </div>
    </div>    

<div id="pPersonsTable" style="margin:2px;padding:2px;">    
    <table id="persons_table" > </table>
    <div id="persons_tablePager"> </div>
</div>    
</div>        


        <div id="dialog_editdepform" style="display:none; overflow:visible; padding:1px; ">        
        <form id="fDepartmentEdit" name="fDepartmentEdit" method="post" action="staff_list_dep_edit.php" >
          <div class="pane"  >
            <input name="id" type="hidden" id="fid" value="" />
            <input name="oper" type="hidden" id="foper" value="" />            
            <input name="isLeaf" type="hidden" id="fisLeaf" value="" />            
            <div class="pane"  >    

                <p>            
                    <label>Підрозділ
                        <input name="name" type="text" size="40" id ="fname" value= "" data_old_value = "" />
                    </label>
                </p>
                <p>
                    <label>Повна назва
                        <input name="full_name" type="text" size="80" id ="ffull_name" value= "" data_old_value = "" />
                    </label>
                </p>
                <p>
                    <label> Входить до підрозділу
                        <select name="id_parent_department" size="1" id="fid_parent" value= "" data_old_value = "" >
                            <?php echo "$ldepselect" ?>;
                        </select>                    
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
    
    
        <div id="dialog_editpersonform" style="display:none; overflow:visible; padding: 1px; ">        
        <form id="fPersonEdit" name="fPersonEdit" method="post" action="staff_list_person_edit.php" >
          <div class="pane"  >
            <input name="id" type="hidden" id="fid" value="" />
            <input name="oper" type="hidden" id="foper" value="" />            
            <div class="pane"  >    

                <p>            
                    <label>Працівник
                        <input name="represent_name" type="text" size="60" id ="frepresent_name" value= "" data_old_value = "" />
                    </label>
                </p>
                <p>
                    <label>Прізвище
                        <input name="soname" type="text" size="20" id ="fsoname" value= "" data_old_value = "" />
                    </label>

                    <label>Ім'я
                        <input name="name" type="text" size="20" id ="fname" value= "" data_old_value = "" />
                    </label>

                    <label>По батькові
                        <input name="father_name" type="text" size="20" id ="ffather_name" value= "" data_old_value = "" />
                    </label>
                    
                </p>
                
                <p>
                    <label> Підрозділ
                        <select name="id_department" size="1" id="fid_department" value= "" data_old_value = "" >
                            <?php echo "$ldepselect" ?>;
                        </select>                    
                    </label> 
                </p>
                
                
                <p>            
                    <input name="id_post" type="hidden" id = "fid_post" size="10" value= "" data_old_value = ""/>    
                    <label >Посада 
                        <input name="name_post" type="text" id = "fname_post" size="60" value= "" data_old_value = ""/>
                    </label> 
                    <button type="button" class ="btnSel" id="btPostSel"></button>       
                </p>

                <p>  
                    <input name="id_abon" type="hidden" id = "fid_abon" size="10" value= "" data_old_value = ""/>    
                    <label>Абонент  
                       <input name="name_abon" type="text" id = "fname_abon" size="60" value= "" data_old_value = ""/>
                    </label> 
                    <button type="button" class ="btnSel" id="btAbonSel"></button>     
                </p>
                <p>                  
                    <input type="hidden"   name="is_active"  value="No" />                                            
                    <label>
                        <input type="checkbox" name="is_active" id="fis_active" value="Yes" data_old_checked = ""/>
                        Працює
                    </label>
                    <input type="hidden"   name="is_runner"  value="No" />                                            
                    <label>
                        <input type="checkbox" name="is_runner" id="fis_runner" value="Yes" data_old_checked = "" />
                        Кур'єр
                    </label>     
                    
                    <label >Телефон 
                        <input name="phone" type="text" id = "fphone" size="20" value= "" data_old_value = ""/>
                    </label> 
                    
                </p>
            </div>    
            <div class="pane"  >    
                <p>
                    <label>Прийнятий на роботу
                        <input name="date_start" type="text" size="15" class="dtpicker" id ="date_start" value= "" data_old_value = "" />
                    </label>
                    <label>Звільнений з роботи
                        <input name="date_end" type="text" size="15" class="dtpicker" id ="fdate_end" value= "" data_old_value = "" />
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
    
    <form id="fabon_sel_params" name="abon_sel_params" target="abon_win" method="post" action="dov_abon.php">
         <input type="hidden" name="select_mode" value="1" />
    </form>

    <form id="fpost_sel_params" name="post_sel_params" target="posts_win" method="post" action="dov_posts.php">
         <input type="hidden" name="select_mode" value="1" />
    </form>


<?php
print('<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>');


end_mpage();
?>