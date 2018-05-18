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
   $nmp1 = "АЕ-Користувачі вибір";
   
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
   $nmp1 = "АЕ-Користувачі та групи користувачів";
    
}    

if (CheckLevel($Link,'адмін-користувачі',$session_user)==3)
   $r_edit = 1;
else
   $r_edit = 0;


$Query=" select sys_fill_full_lvl();";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );

//$lgi_doc=     DbTableSelList($Link,'lgi_document_tbl','id','name');
//$lgi_docselect=DbTableSelect($Link,'lgi_document_tbl','id','name');

$lgrpselect = DbTableSelect($Link," (select id,name from syi_user where flag_type =1 order by id) as ss ",'id','name');
$laccesslist=DbTableSelList($Link,'syi_access_lvl','id','name');

$staff_dep= DbTableSelList($Link,'prs_department','id','name');

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

print('<script type="text/javascript" src="staff_list_sel.js?version='.$app_version.'"></script> ');
print('<script type="text/javascript" src="user_list.js?version='.$app_version.'"></script> ');


echo <<<SCRYPT
<script type="text/javascript">
    var selmode = $selmode;     
    var id_person = $id_person;   
    var laccesslist = $laccesslist;
    var r_edit = $r_edit;
    var staff_dep = $staff_dep; 
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
    
    <div id="pGroupTable" style="margin:2px;padding:2px; ">    
        <table id="group_table" > </table>
        <div id="group_tablePager"> </div>
    </div>    

<div id="pPersonsTable" style="margin:2px;padding:2px; ">    
    <table id="persons_table" > </table>
    <div id="persons_tablePager"> </div>
</div>    
</div>        


        <div id="dialog_editgrpform" style="display:none; overflow:visible; padding:1px; ">        
        <form id="fGroupEdit" name="fGroupEdit" method="post" action="user_list_edit.php" >
          <div class="pane"  >
            <input name="id" type="hidden" id="fid" value="" />
            <input name="oper" type="hidden" id="foper" value="" />            
            <input name="flag_type" type="hidden" id="fflag_type" value="1" />            
            <div class="pane"  >    

                <p>            
                    <label>Група
                        <input name="name" type="text" size="40" id ="fname" value= "" data_old_value = "" />
                    </label>
                </p>
                <p>
                
            </div>    
        </div>    
        <div class="pane" >
                <button name="submitButton" type="submit" class ="btn" id="bt_edit" value="edit" >Записати</button>
                <button name="submitButton" type="submit" class ="btn" id="bt_add" value="add" style="display:none;">Записати новий</button>                
                <button name="resetButton" type="button" class ="btn" id="bt_reset" value="bt_reset" >Відмінити</button>         
        </div>
            
      </FORM>
            
      </div>                
    
    
   
        <div id="dialog_editpersonform" style="display:none; overflow:visible; padding:1px; ">        
        <form id="fPersonEdit" name="fPersonEdit" method="post" action="user_list_edit.php" >
          <div class="pane"  >
            <input name="id" type="hidden" id="fid" value="" />
            <input name="oper" type="hidden" id="foper" value="" />    
            <input name="flag_type" type="hidden" id="fflag_type" value="0" />            
            <div class="pane"  >    

                <p>            
                    <label>Користувач
                        <input name="name" type="text" size="40" id ="fname" value= "" data_old_value = "" />
                    </label>
                </p>
                
                <p>
                    <label> Група
                        <select name="id_parent" size="1" id="fid_parent" value= "" data_old_value = "" >
                            <?php echo "$lgrpselect" ?>;
                        </select>                    
                    </label> 
                </p>
                
                
                <p>  
                    <input name="id_person" type="hidden" id = "fid_person" size="10" value= "" data_old_value = ""/>    
                    <label>Працівник
                       <input name="represent_name" type="text" id = "frepresent_name" size="60" value= "" data_old_value = ""/>
                    </label> 
                    <button type="button" class ="btnSel" id="btPersonSel"></button>     
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

    
       <div id="dialog_setpasswd" style="display:none; overflow:visible; padding:1px; ">        
        <form id="fPasswd" name="fPasswd" method="post" action="user_list_passwd_edit.php" >
          <div class="pane"  >
            <input name="id" type="hidden" id="fid" value="" />
            <input name="oper" type="hidden" id="foper" value="" />    
           
                <p>            
                    <label style="display: inline-block; width: 400px;"> Новий пароль: 
                    <input type="Password" style="width:200px;float:right;" name="passwd" id = "fpasswd1" value= ""  />
                    </label>
                </p>
                <p>            
                    <label style="display: inline-block; width: 400px;"> Ще раз, будь ласка: 
                    <input type="Password" style="width:200px;float:right;" name="passwd2" id = "fpasswd2" value= ""  />
                    </label>
                </p>
                <p>            
                    <div id ="error_zone" style="color:blue;">
                    </div>
                </p>
        </div>    
      </FORM>
            
      </div>  
    
    
    <div id="dialog-confirm" title="" style="display:none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text"> </p></p>
    </div>

    <div id="dialog-enviroment" title="" style="display:none; margin:1px; padding:1px; " >
        <div id="env_table" style="padding:1px; margin:1px;">    
         <table id="enviroment_table" style="margin:1px;"></table>
         <div id="enviroment_tablePager"></div>
        </div>    
    </div>
    
    <div id="grid_selperson" style="display:none; position:absolute; margin:1px; background: #FFFFCC; borderWidth:1px;z-index: 100000;" > 
         <table id="person_sel_table" style="margin:1px;"></table>
         <div id="person_sel_tablePager"></div>
    </div>        
    
    <form id="fperson_sel_params" name="person_sel_params" target="cntrl_win" method="post" action="staff_list.php">
        <input type="hidden" name="select_mode" value="1" />
        <input type="hidden" name="id_person" id="fperson_sel_params_id_person" value="0" />
    </form>

<?php
print('<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>');


end_mpage();
?> 