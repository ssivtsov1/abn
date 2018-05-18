<?php

header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];

if ((isset($_POST['select_mode']))&&($_POST['select_mode']=='1'))
{
   $selmode = 1; 
   $nmp1 = "АЕ-банки - вибір";
   
   $mfo= 0;          
   if (isset($_POST['mfo']))
   {
      $mfo= $_POST['mfo'];
   }
       
}
else
{
   $mfo= 0;              
   $selmode = 0; 
   $nmp1 = "АЕ-банки";
}    

$cp1 = "Банки та установи";
$gn1 = "dov_bank";
$gn1_table = $gn1."_table";
$gn1_pager = $gn1."_tablePager";

start_mpage($nmp1); //заголовок
head_addrpage();  // хедер, подключение библиотек

$r_edit = CheckLevel($Link,'довідник-банки',$session_user);

print('<link rel="stylesheet" type="text/css" href="css/ded_styles.css" /> ');
    
print('<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script> ');
print('<script type="text/javascript" src="dov_banks.js"></script>');
print('<link rel="stylesheet" type="text/css" href="css/layout-default-latest.css" /> ');
print('<script type="text/javascript" src="js/jquery.layout.min-latest.js"></SCRIPT>');

echo "
<script type='text/javascript'>
    var selmode = $selmode;
    var mfo = $mfo; 
    var r_edit = $r_edit;
</script>
";
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
    
<?php

print('<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>');

end_mpage();
?>