<?php
ob_start();
header('Content-type: text/html; charset=utf-8');

include_once 'abon_en_func.php';
include_once 'abon_ded_func.php';


head_addrpage();  // хедер, подключение библиотек


print('<link rel="stylesheet" type="text/css" href="css/ded_styles.css" /> ');

print('<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script> ');
print('<script type="text/javascript" src="js/jquery.form.js"></script>');
print('<script type="text/javascript" src="js/jquery.validate.min.js?version='.$app_version.'" ></script>');
print('<script type="text/javascript" src="js/jquery.ui.datepicker-uk.js"></script> ');
print('<script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script> ');

print('<link rel="stylesheet" type="text/css" href="css/layout-default-latest.css" /> ');
print('<script type="text/javascript" src="js/jquery.layout.min-latest.js"></SCRIPT>');



middle_mpage(); // верхнее меню

echo '<br><br><br>
  <a href="backup_save.php?a=1">Создать</a> резервную копию. И сохранить локально (может занять 10+ секунд).<br>
  <a href="backup_save.php?a=2">Создать</a> резервную копию базы данных на сервере.<br>
  <a href="backup_get.php">Восстановить</a> базу данных из локального файла.<br>
  <a href="adm_ftp_test.php">Подключение по FTP</a><br><br>
  <a href="adm_sync.php">Синхронизация справочников</a><br>
  <a href="adm_sync_central.php">Админка синхронизация</a><br>
  ';

?>

</head>
<body>

</div>

</body>
</html>
