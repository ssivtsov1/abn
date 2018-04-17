<?php

header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

//session_name("session_kaa");
//session_start();
error_reporting(0);

//$Link = get_database_link($_SESSION);
//$session_user = $_SESSION['ses_usr_id'];


start_mpage("Абон-енерго - Реєстрація"); //заголовок
head_addrpage();  // хедер, подключение библиотек

print('<link rel="stylesheet" type="text/css" href="css/ded_styles.css" /> ');
print('<link rel="stylesheet" type="text/css" href="css/layout-default-latest.css" /> ');
print('<script type="text/javascript" src="js/jquery.layout.min-latest.js"></SCRIPT>');
print('<script type="text/javascript" src="js/jquery.form.js"></script>');
print('<script type="text/javascript" src="js/jquery.validate.min.js?version='.$app_version.'" ></script>');
print('<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script> ');
//print('<script type="text/javascript" src="dov_meters.js"></script> ');

//global $app_maint_cstr;    
//echo $app_maint_cstr;
$sys_link = pg_connect($app_maint_cstr) or die(" Sys Connection Error: " . pg_last_error($sys_link));
$lbaseselect=DbTableSelect($sys_link,'nm_db','nm_db','nm_txt');


$default_base='';
if (!isset($_COOKIE['fizabon_app_base']))
{
    $default_base = $_COOKIE['fizabon_app_base'];
}
else
{
    $SQL = "select * from nm_db where def = true; ";
    
    $result = pg_query($sys_link,$SQL);

    if ($result) 
    {
       $row = pg_fetch_array($result);
       $default_base= $row["nm_db"];
    }
    
}
//echo $default_base;

 if ($default_base!='') 
 {
    $cstr="host=$app_host dbname=$default_base user=$app_user password=$app_pass"; 
    
    //echo $cstr;
    $link = pg_connect($cstr); //or die("DB Connection Error: " . pg_last_error($link));
    if (!$link)
    {
        unset($_COOKIE['fizabon_app_base']);
        setcookie('fizabon_app_base', "", time()-3600);
        die("DB Connection Error: " . pg_last_error($link));
    }
    $luserselect=DbTableSelect($link,'(select id,name from syi_user where flag_type=0) as ss ','id','name'); 
    
    $default_usr = 0;
    if (isset($_COOKIE['fizabon_app_user']))
    {
        $default_usr = $_COOKIE['fizabon_app_user'];
    }
        
 }
 else
 {
     $luserselect="";
 }

?>

<style type="text/css"> 

    body { background:rgb(224,224,224); margin:0 auto; padding:0;}
    #login {position:absolute; width:400px; height:148px;  top:40%;  left:50%;  margin:-74px 0 0 -200px;}

</style>   

<script type="text/javascript">
    var default_base = '<?php echo "$default_base" ?>';
    var default_usr  = '<?php echo "$default_usr" ?>';
    
    jQuery(function(){ 
    
        $("#flogin").find("#fname_base").attr('value',default_base);
        $("#flogin").find("#fid_base").attr('value',default_base);
        $("#flogin").find("#fid_user").attr('value',default_usr);
        

        $("#flogin").find("#fname_base").bind('input propertychange', function() {
            jQuery('#error_message').html("");  
            jQuery('#error_message').hide();
        });

        $("#flogin").find("#fid_user").change(function() {
            jQuery('#error_message').html("");  
            jQuery('#error_message').hide();
        });

        $("#flogin").find("#fpasswd").bind('input propertychange', function() {
            jQuery('#error_message').html("");  
            jQuery('#error_message').hide();
        });

       $("#flogin").find("#btShowBaseName").click( function(){ 

        $("#flogin").find("#pname_base").toggle();
       });

       $("#flogin").find("#btRefreshUsers").click( function(){ 
          RefreshUserSelect();  
       });


        $("#flogin").find("#fid_base").change(function() {

          $("#flogin").find("#fname_base").attr('value',$("#flogin").find("#fid_base").val());
          RefreshUserSelect();  
        });
        
        jQuery("#flogin :input").addClass("ui-widget-content ui-corner-all");
        jQuery(".btn").button();
        jQuery(".btnBase").button({icons: {primary:'ui-icon-circle-triangle-s'}});
        jQuery(".btnUsr").button({icons: {primary:'ui-icon-person'}});
        
         var form_options = { 
            dataType:"json",
            beforeSubmit: FormBeforeSubmit, // функция, вызываемая перед передачей 
            success: FormSubmitResponse // функция, вызываемая при получении ответа
          };

         ajaxForm = $("#flogin").ajaxForm(form_options);


         var valid_options = { 

		rules: {
			name_base: "required",
                        id_user: "required"
		},
		messages: {
			name_base: "Вкажіть базу",
                        id_user: "Вкажіть користувача"
		}
         };

         validator = $("#flogin").validate(valid_options);
            
         function RefreshUserSelect()
         {
            jQuery('#error_message').html("");  
            jQuery('#error_message').hide();

            new_base = $("#flogin").find("#fname_base").val();  
            if(new_base!="")
            { 
            
                var request = $.ajax({
                url: "login_user_sel_data.php",
                data: {base: new_base},
                type: "POST",
                dataType: "html"
                });

                request.done(function(data ) {
                    //alert(data);
                    //$("#flogin").find("#fname_base").attr('value',new_base);
                    $("#flogin").find("#fid_user").html(data);
                });
                request.fail(function(data ) {
                   jQuery('#error_message').html("Не вдалося одержати перелік користувачів!");
                   jQuery('#error_message').show();
                });

            }
             
         };
         // обработчик, который вызываетя перед отправкой формы
         function FormBeforeSubmit(formData, jqForm, options) { 
                
                submit_form = jqForm;
                
               if(!submit_form.validate().form()) 
                    {return false;}
                else {   return true;   }
                
         } ;
        
         function FormSubmitResponse(responseText, statusText)
         {
             errorInfo = responseText;
             form_edit_lock=0;

             if (errorInfo.errcode==0) {
             return [true,errorInfo.errstr]
             }; 
             

             if (errorInfo.errcode==-1) {
                 
               location.href = "abon_en_main.php";  
               return [true,errorInfo.errstr]};              
             
             if (errorInfo.errcode==1)  {

               jQuery('#error_message').html("");  
               jQuery('#error_message').html(errorInfo.errstr);
               jQuery('#error_message').show();

               return [true,errorInfo.errstr]};               
        
             if (errorInfo.errcode==2) {
               jQuery('#error_message').html("");  
               jQuery('#error_message').html(errorInfo.errstr);
               jQuery('#error_message').show();
               return [false,errorInfo.errstr]};   
           
         };
        
        
    });
 
</script>


</head>
<body >

    <DIV class="ui-corner-all pane" id="login"> 
        <form id="flogin" name="flogin" method="post" action="login_exec.php" >
            Реєстрація: 
            <p>
            <div style="display: inline-block; width: 400px;" >
            <button style="float:right" type="button" class ="btnBase btnSel" id="btShowBaseName" title = "Відкрити ім'я бази"> </button>                     
                База : 
                <select style="width:160px; float:right" name="id_base" size="1" id="fid_base" value= "" >
                    <?php echo "$lbaseselect" ?>;
                </select>             
            </div>                        
            </p>    
            <div id ="pname_base" style="display:none;">
            <p>
            <div style="display: inline-block; width: 400px;" >
                <button style="float:right" type="button" class ="btnUsr btnSel" id="btRefreshUsers" title = "Користувачі"> </button>                     
                Ім'я бази : 
                <input style="width:160px; float:right" name="name_base" type="text" id = "fname_base"  value= "" />
            </div>                         
            </p>
            </div>
            <p>                
            <label style="display: inline-block; width: 400px;" >            
                Ім'я користувача:
                <select style="width:200px; float:right" name="id_user" size="1" id="fid_user" value= "" >
                    <?php echo "$luserselect" ?>;
                </select>                    
            </label>
            </p>        
            <p>    
            <label style="display: inline-block; width: 400px;" >                
                Пароль: 
                <input type="Password" style="width:200px; float:right" name="passwd" id = "fpasswd" value= ""  />
            </label>         
            </p>    
            <p>        
             <div id="error_message" style="display:none; color:red;">    
             
             </div>
            </p>        
           <div class="pane ui-corner-all ui-state-default" style ="height:28px" >
                <button style="float:right" name="submitButton" type="submit" class ="btn" id="bt_login" value="login" >Підключитися</button>
            </div>
            
        </form>
    </DIV>
    
 
    
<?php

//print('<div id="message_zone" style ="padding: 5px;color: blue;" > ------------------- </div>');

//print('<a href="javascript:void(0)" id="ls1">show debug window</a> <br> ');
//print('<a href="javascript:void(0)" id="ls2">hide debug window</a>');

end_mpage();
?>
