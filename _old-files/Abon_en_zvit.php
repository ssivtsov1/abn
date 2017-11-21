<?php
ob_start();
header('Content-type: text/html; charset=utf-8');

echo '<html><head><title>Абон-енерго (Звіти)</title>';

include_once 'Abon_en_func.php';
include_once 'abon_ded_func.php';

head_addrpage();  // хедер, подключение библиотек

/*
print('<link rel="stylesheet" type="text/css" href="css/ded_styles.css" /> ');

print('<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script> ');
print('<script type="text/javascript" src="js/jquery.form.js"></script>');


print('<link rel="stylesheet" type="text/css" href="css/layout-default-latest.css" /> ');
print('<script type="text/javascript" src="js/jquery.layout.min-latest.js"></SCRIPT>');
*/


middle_mpage(); // верхнее меню

echo '<br><br>Пусто';

?>

</body>
</html>
