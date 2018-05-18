<?php

header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';

session_name("session_kaa");
session_start();

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];

$nmp1 = "АЕ-Календар";


start_mpage($nmp1); //заголовок
head_addrpage();  // хедер, подключение библиотек

print('<link rel="stylesheet" type="text/css" href="css/ded_styles.css" /> ');
print('<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script> ');
print('<script type="text/javascript" src="js/jquery.ui.datepicker-uk.js"></script> ');
print('<script type="text/javascript" src="js/date-uk-UA.js"></script> ');
print('<script type="text/javascript" src="js/jquery.form.js"></script>');
print('<script type="text/javascript" src="calendar.js"></script>');
print('<link rel="stylesheet" type="text/css" href="css/layout-default-latest.css" /> ');
print('<script type="text/javascript" src="js/jquery.layout.min-latest.js"></SCRIPT>');

echo "
<script type='text/javascript'>

</script>
";
?> 
<style type='text/css'> 

div.ui-datepicker{
 font-size:20px;
}

.calendar_holiday a{
   color: red !important;
}

.ui-datepicker-week-end a{
   background-color : lawngreen  !important;
   background-image : none !important;
}

#calendar_div { text-align: center;}
#pActionBar { text-align: center;}

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

<DIV id="pmain_center"> 
    <div id="calendar_div">
      <div id="calendar" style="margin: 10px; display: inline-block;">   </div>    
    </DIV>        
    <form id="fCalendarEdit" method="post" action="calendar_edit.php">
      <input name="date" type="hidden" id="fdate" value="" />
      <div class="pane ui-corner-all" id="pActionBar">
          <button name="submitButton" type="submit" class ="btn" id="bt_set" value="set" >Вихідний</button>
          <button name="submitButton" type="submit" class ="btn" id="bt_fill" value="fill">Заповнити</button>
      </div>   
    </form> 
</DIV>    
<?php

print('<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>');

end_mpage();
?>