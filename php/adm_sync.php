<?php


include_once 'abon_en_func.php';
include_once 'abon_ded_func.php';

$gn1 = "adm_sync";

$gn1_table = $gn1."_table";
$gn1_pager = $gn1."_tablePager";

head_addrpage();  // хедер, подключение библиотек


print('<link rel="stylesheet" type="text/css" href="css/ded_styles.css" /> ');
    
print('<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script> ');
print('<script type="text/javascript" src="js/jquery.form.js"></script>');
print('<script type="text/javascript" src="js/jquery.validate.min.js?version='.$app_version.'" ></script>');
print('<script type="text/javascript" src="js/jquery.ui.datepicker-uk.js"></script> ');
print('<script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script> ');

print('<link rel="stylesheet" type="text/css" href="css/layout-default-latest.css" /> ');
print('<script type="text/javascript" src="js/jquery.layout.min-latest.js"></SCRIPT>');

print('<script type="text/javascript" src="adm_sync.js"></script> ');

//middle_mpage(); // верхнее меню
?>

</head>
<body>
<a href="backup.php">Назад</a>
<div id="grid_dform">    
<table id="<?php echo $gn1_table ?>" > </table>
<div id="<?php echo $gn1_pager ?>" ></div>
</div>
  
</body>
</html>
