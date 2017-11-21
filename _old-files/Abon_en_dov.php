<?php

header('Content-type: text/html; charset=utf-8');

require 'Abon_en_func.php';
session_name("session_kaa");
session_start();
error_reporting(0);

$nm1 = "Абон-енерго (Довідники)";
start_mpage($nm1);
head2_mpage();
middle_mpage();

print("<ul>\n");
print("<br> \n");
print("<li><a href=\"dov_posts.php\">Посади</a></li>\n");
print("<li><a href=\"staff_list.php\">Працівники</a></li>\n");
print("<li><a href=\"runner_sectors.php\">Дільниці</a></li>\n");
print("<li><a href=\"dov_obladnan.php\">Обладнання</a></li>\n");
//print("<li><a href=\"dov_addr.php\">Адреси</a>\n");
print("<li><a href=\"adr_tree_selector.php\">Класифікатор адрес</a>\n");
print("</li>\n");

print("<li><a href=\"tarif_list.php\">Тарифи</a></li>\n");
print("<li><a href=\"lgt_list.php\">Пільги</a>\n");
print("<li><a href=\"lgt_category.php\">Групи пільг</a>\n");
print("<li><a href=\"lgt_calc.php\">Способи розрахунку пільг</a>\n");
print("</li>\n");
print("</li>\n");
print("<li><a href=\"Dov_list1.php\">Опалювальні періоди</a></li>\n");
print("<li><a href=\"dov_banks.php\">Банки та установи</a></li>\n");

print("<li><a href=\"res_params.php\">Параметри РЕМ</a></li>\n");
print("</ul>\n");

end_mpage();
?>