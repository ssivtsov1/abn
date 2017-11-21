<?php

header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';

session_name("session_kaa");
session_start();

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];

$nmp1 = "АЕ-Журнал помилок";


$cp1 = "Банки та установи";
$gn1 = "dov_errlist";
$gn1_table = $gn1."_table";
$gn1_pager = $gn1."_tablePager";

start_mpage($nmp1); //заголовок
head_addrpage();  // хедер, подключение библиотек

print('<link rel="stylesheet" type="text/css" href="css/ded_styles.css" /> ');
    
print('<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script> ');
print('<script type="text/javascript" src="dov_errlist.js"></script>');
print('<link rel="stylesheet" type="text/css" href="css/layout-default-latest.css" /> ');
print('<script type="text/javascript" src="js/jquery.layout.min-latest.js"></SCRIPT>');

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
    
<div id="dialog-confirm" title="Удалить учет?" style="display:none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text">Учет будет удален. Продолжить? </p></p>
</div>
    
<?php

print('<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>');

end_mpage();
?>