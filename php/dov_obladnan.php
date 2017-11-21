<?php
header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
session_name("session_kaa");
session_start();
error_reporting(0);

$nm1 = "АЕ-Довідники";
start_mpage($nm1);
head2_mpage();
middle_mpage();

echo "<br><br><a href=\"abon_en_dov\">Назад</a>
<ul id=\"dov_2\"><li><a href=\"dov_meters.php\">Лічильники </a></li>
<li><a href=\"dov_compi.php\">Вимірювальні трансформатори </a></li>
<li><a href=\"dov_corde.php\">Провода </a></li>
<li><a href=\"dov_cable.php\">Кабеля </a></li>
<li><a href=\"dov_compensator.php\">Трансформаторы </a></li>
<li><a href=\"dov_switch.php\">Комутаційне обладнання </a></li>
<li><a href=\"dov_fuse.php\">Запобіжники </a></li>
<br>
<li><a href=\"dov_fider.php\">Фідери </a></li>
<li><a href=\"dov_tp.php\">Трансформаторні підстанції </a></li>

</ul>
";

//<li><a href=\"abon_eqp1.php\"> Точки обліку абонентів (устарело)</a></li>
end_mpage();
?>