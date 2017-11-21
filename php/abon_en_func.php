<?php
//header('Content-type: text/html; charset=utf-8');

include_once 'app_config.php';

$app_version='20171019';

function show_inf($ids) {

  //$c_str = "host=10.71.1.94 dbname=sys_en user=local password=postgres";
  global $app_maint_cstr;  
  $c_str=$app_maint_cstr;
  //echo $c_str;
  $lnk = pg_connect($c_str);

  $Q = "Select * from dbusr_var where id_sess=$ids";
  $res = pg_query($lnk, $Q);
  $row = pg_fetch_array($res);
  $Q0 = $row['qu'];
  $idusr = $row['id_user'];
  $cons = $row['con_str1'];

  $Q1 = "Select nm_utxt from nm_user where id_user=" . $idusr;
  $res1 = pg_query($lnk, $Q1);
  $row1 = pg_fetch_array($res1);
  $nmusr = $row1['nm_utxt'];


  $lnk1 = pg_connect($cons);
  $res0 = pg_query($lnk1, $Q0);
  $Q2 = "Select work_period from T_Base_login";
  $res2 = pg_query($lnk1, $Q2);
  $row2 = pg_fetch_array($res2);
  $w_p = $row2['work_period'];


  print("<br>Дата: " . date('d-m-Y', strtotime($w_p)) . " \n");
/////////////	echo date('d-m-Y');
  print("   Час: \n");
  print("<span id=\"hours\"></span>\n");
  print("<script type=\"text/javascript\">\n");
  print("obj_hours=document.getElementById(\"hours\");\n");
  print("function wr_hours()\n");
  print("{\n");
  print("time=new Date();\n"); //new Date()
  print("time_sec=time.getSeconds();\n");
  print("time_min=time.getMinutes();\n");
  print("time_hours=time.getHours();\n");
  print("time_wr=((time_hours<10)?\"0\":\"\")+time_hours;\n");
  print("time_wr+=\":\";\n");
  print("time_wr+=((time_min<10)?\"0\":\"\")+time_min;\n");
  print("time_wr+=\":\";\n");
  print("time_wr+=((time_sec<10)?\"0\":\"\")+time_sec;\n");
  print("obj_hours.innerHTML=time_wr;\n");
  print("}\n");
  print("wr_hours();\n");
  print("setInterval(\"wr_hours();\",1000);\n"); //wr_hours();
  print("</script>\n");
  print( " Пользователь: " . $nmusr . " \n");
  print("<br> \n");
}

function log_s_pgsql($nm_page) { // процедура подключения к базе данных sys_en
  global $app_maint_cstr;    
  $c_str = $app_maint_cstr;//"host=10.71.1.94 dbname=sys_en user=local password=postgres";
  //echo $c_str;
  $lnk = pg_connect($c_str);

  if ($nm_page == "login") {
    $QR = "select * from nm_db;";
    $res_qr = pg_query($lnk, $QR);
    $QR1 = "select * from nm_user;";
    $res_qr1 = pg_query($lnk, $QR1);
    $DT = array("lnks" => $lnk, "nml" => $res_qr, "nmu" => $res_qr1);
  }

  if (empty($lnk)) {
    Return (null);
  } else {

    Return ($DT);
  }
}

function sel_par($ids) { // процедура подключения к базе данных sys_en
  global $app_maint_cstr;    
  $c_str = $app_maint_cstr ; //"host=10.71.1.94 dbname=sys_en user=local password=postgres";
  //echo $c_str;
  $lnk = pg_connect($c_str);

  $QR = "select dbusr_var.*,s_var.qm_pi,s_var.qm_paccnt,s_var.qm_pcalc,s_var.work_p" .
          " from dbusr_var inner join s_var on dbusr_var.id_sess=s_var.id_sess where dbusr_var.id_sess=" . $ids;
  $res_qr = pg_query($lnk, $QR);

  if ($res_qr) {
    Return ($res_qr);
  } else {
    echo pg_last_error($lnk);
    Return (null);
  }
}

function del_par($ids) { // процедура подключения к базе данных
  global $app_maint_cstr;    
  $c_str = $app_maint_cstr ; //"host=10.71.1.94 dbname=sys_en user=local password=postgres";
  //$c_str="host=localhost dbname=sys_en user=admin";
  //echo $c_str;
  $lnk = pg_connect($c_str);

  $QR = "delete from dbusr_var where id_sess=" . $ids;
  $res_qr = pg_query($lnk, $QR);
  /*
    $QR1="delete from s_var where id_sess=".$ids;
    $res_qr1=pg_query($lnk,$QR1);
   */
  if ($res_qr) {
    Return (1);
  } else {
    echo pg_last_error($lnk);
    Return (0);
  }
}

//////////////////////////// функции для страницы регистрации

function startPage() { //печать заголовка html-страницы подключения
  //print("<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n");
  //print("<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"ua\">\n");
  
  print('<html><head><meta http-eguiv="Content-Type" content="text/html; charset=utf-8">');
//  print("<meta name=\"author\"  content=\"Mondrus Katya\">\n");
  print("<title>АЕ-Реєстрація)</title>\n");
  print("<link rel=\"stylesheet\" type=\"text/css\" href=\"css/start_page_only.css\" /> \n ");
  print(" <style type=\"text/css\">\n");
  print("   SELECT {\n");
  print("    width: 150px; \n");
  print("   }\n");
  print("  </style>\n");
  print("</head>\n");
  print("<body >\n");
}

function endPage() { //печать завершающих тэгов html-страницы подключения
  print("</body>\n");
  print("</html>\n");
}

function start_lform() { //печать html-формы на подключение к базе
  print("<div id=\"login\"> \n");
  print("<form ALIGN=left name=\"linform\" method=\"post\" >\n");
}

function middle_lform($dt_sys) { //Содержимое формы на подключение
  $res = $dt_sys['nml'];
  $res1 = $dt_sys['nmu'];
  print("<br> <b> Реєстрація: </b> <br><br> \n");
  print("<table id=\"login_tbl\"> \n");
  print("<tr> \n");
  print("<td> Ім'я бази       : </td>\n");
  print("<td> \n");
  print("<SELECT NAME=\"db_name\" > \n");
  //print("<OPTION  VALUE=\"\">\n");  // зачем постить пустую строку?
  while ($row1 = pg_fetch_array($res)) {
    print("<OPTION \n");
    print(" VALUE=\"" . $row1['id_db'] . "\">" . $row1['nm_txt'] . " \n "); ////$row1['nm_db']
  }
  print("</SELECT>\n");
//        print("<input type=\"Text\" name=\"db_name\"> \n");
  print("</td> </tr>\n");
  print("<tr> \n");
  print("<td> Ім'я користувача: </td> \n ");
  print("<td> \n");
  print("<SELECT NAME=\"user_name\" > \n");
  // print("<OPTION  VALUE=\"\">\n");
  while ($row2 = pg_fetch_array($res1)) {
    print("<OPTION \n");
    print(" VALUE=\"" . $row2['id_user'] . "\">" . $row2['nm_utxt'] . " \n "); /////$row2['nm_user']
  }
  print("</SELECT>\n");
  print("</td> </tr>\n");

/// <td> <input type=\"Text\" name=\"user_name\"> </td> </tr>\n");
  print("<tr> \n");
  print('<td>Пароль: </td> <td> <input type="Password" name="passwd" value="postgres"> </td> </tr>');  // PASSWORD value="postgres"
  print("</table> \n");
  print('<br> <br> <input type="Submit" name="Connect" value="Підключитися">');
  //print('<input type="Reset" name="Blank" value="Очистити"> <br> <br>');
}

function middle_errlform() { //Содержимое формы с сообщением об ошибке
  print("<br> Помилка реєстрації (перевірте параметри) !<br> \n");
  print("<br>\n");
  print("<br>  <br> \n");
  print("<a href=\"abon_en_login.php\">Повторити реєстрацію</a> <br>\n");
  // <input type=\"Submit\" name=\"Exit_m\" value=\"Повторити реєстрацію\"> </a> <br>\n");
  print("<br>  <br> \n");
}

function end_lform() { //завершающие тэги формы на подключение 
  print("</form>\n");
  print("</div>");
}

function log_pgsql($con_str) { // процедура подключение к базе данных
  error_reporting(0);

  $lnk = pg_connect($con_str);

  if (empty($lnk)) {

    header("Location: abon_en_errlog.php");
    Return ("O");
  } else {

    header("Location: abon_en_main.php");
    Return ($lnk);
  }
}

////////////////////////////////// функции для страницы регистрации
//////////////////////////////////// функции основного меню и главной страницы

function start_mpage($nm_page) { //печать заголовка главной html-страницы содержащей основное меню
  print('<!DOCTYPE html>');
  //print("<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\"> \n");
    
  ////////print('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
  print('<html><head><meta http-eguiv="Content-Type" content="text/html; charset=utf-8">');
  //print("<meta name=\"author\"  content=\"Mondrus Katya\">\n");
  //print("<title>" . iconv("windows-1251", "utf-8", $nm_page) . "</title>\n");
  print("<title>$nm_page</title>\n");
}

function start_mpage_cache($nm_page) { //печать заголовка главной html-страницы содержащей основное меню
  print('<!DOCTYPE html>');
  print('<html manifest="cache.manifest"><head><meta http-eguiv="Content-Type" content="text/html; charset=utf-8">');
  print("<title>$nm_page</title>\n");
}

function sel_size($wd) {
  print(" <style type=\"text/css\">\n");
  print("   SELECT {\n");
  print("    width: " . $wd . "px; \n");
  print("   }\n");
  print("  </style>\n");

  print("</head>\n");
}


function grid_mpage() {

  print("<div id=\"grid_form\">\n");
  print("<table id=\"le_table\" onclick='$.jqGrid('getGridParam','selrow')'></table>\n");
  print("<div id=\"le_tablePager\"></div>\n");
  print("</div>\n");
}

//////////////////заголовок главной страницы только для меню
function head2_mpage() {
  print("<link rel=\"stylesheet\" type=\"text/css\" href=\"css/style_m.css\" /> \n ");
  print("<script src=\"js/jquery-1.7.1.js\" type=\"text/javascript\"></script> \n");
  print("<script src=\"js/mmenu.js\" type=\"text/javascript\"></script> \n");
  print("<script type=\"text/javascript\" src=\"js/jquery.maskedinput.js\"></script>\n");
  print("<script src=\"js/jquery.dropdownPlain.js\" type=\"text/javascript\"></script> \n");
  //echo '<script type="text/javascript" language="javascript" src="js/jquery.dropdownPlain.js"></script>';
  
  echo '<link rel="stylesheet" href="css/style_menu.css" type="text/css" media="screen, projection"/>';
}

// =========================================================================================================================================
// ============================== MENU =====================================================================================================
function main_menu() {
  $session_user_id = $_SESSION['ses_usr_id'];  
  session_write_close();
  global $Link;
  
  $Query="select u1.name as user_name 
   from  syi_user as u1 where u1.id = $session_user_id;";
  $result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
  $row = pg_fetch_array($result);
  $user = $row['user_name'];
  
 if($_SESSION['base_name']=='Днепропетровск')
  echo '<div id="page-wrap" class ="no-print">
      <ul class="dropdown">
      
        <li><a href="abon_en_main.php">Абоненти</a></li>
<!--
        <li><a href="#">Розрахунки</a>
          <ul class="sub_menu">
            
            <li><a href="../dbf/upload_privat.php">Загрузка оплат</a></li>
            
          </ul>
        </li>
-->        
        <li><a href="#">Журнали</a>
          <ul class="sub_menu">
            <li><a href="ind_packs.php">Показники</a></li>
            <li><a href="abon_en_bills.php">Рахунки</a></li>
            <li><a href="payment_operations.php">Оплата</a></li>
            <li><a href="abon_en_switch_list.php">Попередження/відключення</a></li>
            <li><a href="abon_en_works_list.php">Роботи(заміни, перевірки)</a></li>
            <li><a href="work_packs.php">Списки контрольних оглядів</a></li>
            <li><a href="abon_en_subs.php">Списки на субсидію</a></li>
            <li><a href="abon_en_subs_check.php">Звірка даних по субсидії</a></li>            
            <li><a href="abon_en_lgt_dop.php">Додаткові пільги</a></li>
            <li><a href="abon_en_badindic_list.php">Показники з помилками</a></li>
            <li><a href="abon_en_bankpay.php">Завантажені оплати</a></li>
            <li><a href="abon_en_lgt.php">Завантажені пільги</a></li>
            <li><a href="abon_en_smart.php">Завантажені показники СМАРТ</a></li> 
            <li><a href="ind_cabinet.php">Показники з інтернет-каб. та Call-центру</a></li> 
            <li><a href="ind_cabinet_bad.php">Брак Call-центру</a></li> 
            <li><a href="dovid_list.php">Журнал довідок</a></li> 
            <li><a href="abon_en_lgt_manual_list.php">Керування пільгами</a></li> 
            <li><a href="abon_en_task_list.php">Журнал завдань</a></li> 
          </ul>
        </li>


        <li><a href="#">Довідники</a>
          <ul class="sub_menu">

            <li><a href="tarif_list.php">Тарифи</a></li>
            <li><a href="lgt_list.php">Пільги</a></li>
            <li><a href="lgt_category.php">Групи пільг</a></li>
            <li><a href="lgt_calc.php">Способи розрахунку пільг</a></li>

            <li><a href="staff_list.php">Працівники</a></li>
            <li><a href="runner_sectors.php">Дільниці</a></li>
            <li><a href="dov_posts.php">Посади</a></li>

            <li><a href="adr_tree_selector.php">Класифікатор адрес</a></li>
            <li><a href="dov_abon.php">Довідник фіз.осіб</a></li>            
            <li><a href="dov_multifloor.php">Багатоповерхові будинки</a></li>
            <li><a href="dov_gek.php">ЖЕК</a></li>
            <li><a href="dov_smart.php">Будинки зі СМАРТ</a></li>
            <li><a href="#">Типи обладнання »</a>
              <ul class="sub_menu">
                <li><a href="dov_meters.php">Лічильники </a></li>
                <li><a href="dov_compi.php">Вимірювальні трансформатори </a></li>
                <li><a href="dov_corde.php">Провода </a></li>
                <li><a href="dov_cable.php">Кабеля </a></li>
                <li><a href="dov_compensator.php">Трансформаторы </a></li>
                <li><a href="dov_switch.php">Комутаційне обладнання </a></li>
                <li><a href="dov_fuse.php">Запобіжники </a></li>
              </ul>
            </li>
            <!--
            <li><a href="Dov_list1.php">Опалювальні періоди</a></li>
            -->
            <li><a href="dov_fider.php">Фідери </a></li>
            <li><a href="dov_tp.php">Трансформаторні підстанції </a></li>
            <li><a href="map_tp.php">Карта підстанцій </a></li>
            <li><a href="dov_banks.php">Банки та установи</a></li>
            <li><a href="dov_pay_origin.php">Походження платежів</a></li>
            <li><a href="dov_heat.php">Опалювальні періоди</a></li>
            <li><a href="dov_addparam.php">Додаткові ознаки</a></li>
            <li><a href="res_params.php">Параметри РЕМ</a></li>
          </ul>
        </li>

        <li><a href="reps_main.php">Звіти</a>
              <ul class="sub_menu">
                <li><a href="spravka_main.php">Довідки </a></li>
              </ul>
        </li>
<!--
        <li><a href="#">Допомога</a>
          <ul class="sub_menu">
            <li><a href="<li><a href="javascript:" onclick="window.open(\'../calc/calc.html\', \'calc\', \'location=no,scrollbars=no,width=700,height=320,top=100,left=800\'); return false;">Калькулятор</a></li>
          </ul>
        </li>
-->
        <li><a href="#">Адмін.</a>
          <ul class="sub_menu">
            <li><a href="user_list.php">Користувачі</a></li>
            <li><a href="dov_enviroment.php">Ідентифікатори прав доступа</a></li>
            <li><a href="version_hash.php">Резервне копіювання</a></li>            
            
            <li><a href="dov_errlist.php">Помилки та повідомлення</a></li>                 
            
            <li><a href="adm_import.php">Розрахунки та імпорт даних</a></li>                 
            <li><a href="calendar.php">Календар</a></li>                             

            <li><a href="<li><a href="javascript:" onclick="window.open(\'../calc/calc.html\', \'calc\', \'location=no,scrollbars=no,width=700,height=320,top=100,left=800\'); return false;">Калькулятор</a></li>

          </ul>
        </li>
        <li><a href="abon_en_logout.php">Вихід</a></li>

      </ul>
      
      &nbsp;&nbsp; База:'. $_SESSION['base_name'].' ('.$user.')</div>'; 
 else
      echo '<div id="page-wrap" class ="no-print">
      <ul class="dropdown">
      
        <li><a href="abon_en_main.php">Абоненти</a></li>
<!--
        <li><a href="#">Розрахунки</a>
          <ul class="sub_menu">
            
            <li><a href="../dbf/upload_privat.php">Загрузка оплат</a></li>
            
          </ul>
        </li>
-->        
        <li><a href="#">Журнали</a>
          <ul class="sub_menu">
            <li><a href="ind_packs.php">Показники</a></li>
            <li><a href="abon_en_bills.php">Рахунки</a></li>
            <li><a href="payment_operations.php">Оплата</a></li>
            <li><a href="abon_en_switch_list.php">Попередження/відключення</a></li>
            <li><a href="abon_en_works_list.php">Роботи(заміни, перевірки)</a></li>
            <li><a href="work_packs.php">Списки контрольних оглядів</a></li>
            <li><a href="abon_en_subs.php">Списки на субсидію</a></li>
            <li><a href="abon_en_subs_check.php">Звірка даних по субсидії</a></li>            
            <li><a href="abon_en_lgt_dop.php">Додаткові пільги</a></li>
            <li><a href="abon_en_badindic_list.php">Показники з помилками</a></li>
            <li><a href="abon_en_bankpay.php">Завантажені оплати</a></li>
            <li><a href="abon_en_lgt.php">Завантажені пільги</a></li>
            <li><a href="abon_en_smart.php">Завантажені показники СМАРТ</a></li> 
            <li><a href="ind_cabinet.php">Показники з інтернет-каб. та Call-центру</a></li> 
            <li><a href="ind_cabinet_bad.php">Брак Call-центру</a></li> 
            <li><a href="dovid_list.php">Журнал довідок</a></li> 
            <li><a href="abon_en_lgt_manual_list.php">Керування пільгами</a></li> 
            <li><a href="abon_en_task_list.php">Журнал завдань</a></li> 
          </ul>
        </li>


        <li><a href="#">Довідники</a>
          <ul class="sub_menu">

            <li><a href="tarif_list.php">Тарифи</a></li>
            <li><a href="lgt_list.php">Пільги</a></li>
            <li><a href="lgt_category.php">Групи пільг</a></li>
            <li><a href="lgt_calc.php">Способи розрахунку пільг</a></li>

            <li><a href="staff_list.php">Працівники</a></li>
            <li><a href="runner_sectors.php">Дільниці</a></li>
            <li><a href="dov_posts.php">Посади</a></li>

            <li><a href="adr_tree_selector.php">Класифікатор адрес</a></li>
            <li><a href="dov_abon.php">Довідник фіз.осіб</a></li>            
            <li><a href="dov_multifloor.php">Багатоповерхові будинки</a></li>
            <li><a href="dov_gek.php">ЖЕК</a></li>
            <li><a href="dov_smart.php">Будинки зі СМАРТ</a></li>
            <li><a href="#">Типи обладнання »</a>
              <ul class="sub_menu">
                <li><a href="dov_meters.php">Лічильники </a></li>
                <li><a href="dov_compi.php">Вимірювальні трансформатори </a></li>
                <li><a href="dov_corde.php">Провода </a></li>
                <li><a href="dov_cable.php">Кабеля </a></li>
                <li><a href="dov_compensator.php">Трансформаторы </a></li>
                <li><a href="dov_switch.php">Комутаційне обладнання </a></li>
                <li><a href="dov_fuse.php">Запобіжники </a></li>
              </ul>
            </li>
            <!--
            <li><a href="Dov_list1.php">Опалювальні періоди</a></li>
            -->
            <li><a href="dov_fider.php">Фідери </a></li>
            <li><a href="dov_tp.php">Трансформаторні підстанції </a></li>

            <li><a href="dov_banks.php">Банки та установи</a></li>
            <li><a href="dov_pay_origin.php">Походження платежів</a></li>
            <li><a href="dov_heat.php">Опалювальні періоди</a></li>
            <li><a href="dov_addparam.php">Додаткові ознаки</a></li>
            <li><a href="res_params.php">Параметри РЕМ</a></li>
          </ul>
        </li>

        <li><a href="reps_main.php">Звіти</a>
              <ul class="sub_menu">
                <li><a href="spravka_main.php">Довідки </a></li>
              </ul>
        </li>
<!--
        <li><a href="#">Допомога</a>
          <ul class="sub_menu">
            <li><a href="<li><a href="javascript:" onclick="window.open(\'../calc/calc.html\', \'calc\', \'location=no,scrollbars=no,width=700,height=320,top=100,left=800\'); return false;">Калькулятор</a></li>
          </ul>
        </li>
-->
        <li><a href="#">Адмін.</a>
          <ul class="sub_menu">
            <li><a href="user_list.php">Користувачі</a></li>
            <li><a href="dov_enviroment.php">Ідентифікатори прав доступа</a></li>
            <li><a href="version_hash.php">Резервне копіювання</a></li>            
            
            <li><a href="dov_errlist.php">Помилки та повідомлення</a></li>                 
            
            <li><a href="adm_import.php">Розрахунки та імпорт даних</a></li>                 
            <li><a href="calendar.php">Календар</a></li>                             

            <li><a href="<li><a href="javascript:" onclick="window.open(\'../calc/calc.html\', \'calc\', \'location=no,scrollbars=no,width=700,height=320,top=100,left=800\'); return false;">Калькулятор</a></li>

          </ul>
        </li>
        <li><a href="abon_en_logout.php">Вихід</a></li>

      </ul>
      
      &nbsp;&nbsp; База:'. $_SESSION['base_name'].' ('.$user.')</div>'; 
    /*
     * ::::: Removed :::::::::
     * abon_en_dov.php
     * abon_en_packs.php
     * abon_en_zvit.php
     * abon_en_help.php
     * 
     */
}

////////// тело главной страницы - главное меню
function middle_mpage() {

  print("</head>\n");
  print("<body>\n");
  
  main_menu();
}

function end_mpage() { //печать завершающих тэгов главной html-страницы
  print("</body>\n");
  print("</html>\n");
}


//////////////////////////////////// функции основного меню и главной страницы

function paccnt_addmenu() {

  print("<ul id=\"jsddm2\">\n");
  print("    <li><a href=\"abon_en_paccnt.php\">Картка</a></li>\n");
  print("    <li><a href=\"#\">Обладнання</a></li>\n");
  print("    <li><a href=\"#\">Пільги</a></li>\n");
  print("    <li><a href=\"#\">Договір</a></li>\n");
  print("    <li><a href=\"#\">Акти порушення</a></li>\n");
  print("    <li><a href=\"#\">Дебітор</a></li>\n");
  print("    <li><a href=\"#\">Реструктуризація</a></li>\n");
  print("</ul>\n");
}

function middle_doc() {

  print("<ul id=\"doc_l\">\n");
//	print("    <li><a href=\"#\">".iconv("windows-1251","utf-8","Перелік звітів")."</a></li>\n");
  print("    <li><a href=\"zv_abon_card.php\">Поточна картка абонента</a></li>\n");
  print("    <li><a href=\"#\">Звіт 2</a></li>\n");
  print("    <li><a href=\"#\">Звіт 3</a></li>\n");
  print("</ul>\n");
}

function middle_adm() {

  print("<ul id=\"adm_l\">\n");
//	print("    <li><a href=\"#\">".iconv("windows-1251","utf-8","Функції адміністратора")."</a></li>\n");
  print("            <li><a href=\"base_data.php\">Реквізити бази</a></li>\n");
  print("            <li><a href=\"abon_en_dov.php\">Довідники</a></li>\n");
  print("            <li><a href=\"abon_en_pref.php\">Параметри системи</a></li>\n");
  print("            <li><a href=\"#\">Завантаження</a></li>\n");
//	print("            <li><a href=\"prgp.php\">".iconv("windows-1251","utf-8","Ins_pgrp")."</a></li>\n");
//	print("            <li><a href=\"saldo.php\">".iconv("windows-1251","utf-8","Ins_saldo")."</a></li>\n");
  print("            <li><a href=\"calc_norm.php\">Розрахунок по нормам</a></li>\n");
  print("            <li><a href=\"calc_cubm.php\">Розрахунок споживання та встановлення тарифної групи</a></li>\n");
  print("            <li><a href=\"abon_en_help.php\">Допомога</a></li>\n");
  print("</ul>\n");
}

function middle_dov() {
  print("<ul id=\"dov_l\">\n");
  print("<br> \n");
  print("            <li><a href=\"Dov_list2.php\">Дільниці контролерів</a></li>\n");
  print("            <li><a href=\"Dov_addr.php\">Адреси</a>\n");
  print("            </li>\n");
  print("            <li><a href=\"Dov_list3.php\">Тарифні категорії</a></li>\n");
  print("            <li><a href=\"Dov_list4.php\">Пільгові категорії</a>\n");
  print("            </li>\n");
  print("            <li><a href=\"Dov_list5.php\">Типи лічильників</a>\n");
  print("            </li>\n");
  print("            <li><a href=\"Dov_list1.php\">Опалювальні періоди</a></li>\n");
  print("            <li><a href=\"dov_list6.php\">Банки та установи</a></li>\n");
  print("</ul>\n");
  print("            </li>\n");
  print("</ul>\n");
}

function middle_help() {

  print("<ul id=\"help_l\">\n");
  print("    <li><a href=\"#\">Допомога</a></li>\n");
  print("            <li><a href=\"#\">Користувач</a>\n");
  print("<ul>\n");
  print("            <li><a href=\"#\">Картотека</a></li>\n");
  print("            <li><a href=\"#\">Звіти</a></li>\n");
  print("</ul>\n");
  print("            </li>\n");
  print("            <li><a href=\"#\">Адміністратор</a>\n");
  print("<ul>\n");
  print("            <li><a href=\"#\">Списки користувачів</a></li>\n");
  print("            <li><a href=\"#\">Завантаження даних</a></li>\n");
  print("</ul>\n");
  print("            </li>\n");
  print("</ul>\n");
}

function head_addrpage() {
  //echo '<script type="text/javascript" language="javascript" src="js/jquery.dropdownPlain.js"></script>';
  print("<link rel=\"shortcut icon\"  href=\"images/logo.ico\" type=\"image/ico\">\n");
  print("<link rel=\"icon\"  href=\"images/logo.ico\" type=\"image/ico\">\n");
 
  echo '<link rel="stylesheet" href="css/style_menu.css" type="text/css" media="screen, projection"/>';
  print("<link rel=\"stylesheet\" type=\"text/css\" href=\"css/style_m.css\" /> \n ");
  print("<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"css/jquery-ui-1.8.8.custom.css\" /> \n ");
  print("<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"css/ui.jqgrid.css\" /> \n ");
  print("<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"css/ui.multiselect.css\" /> \n ");
  /*
    print("<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"css/flick/jquery-ui-1.7.2.custom.css\" /> \n ");
   */
  print("<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"css/style.css\" /> \n ");
  //print("<script src=\"js/jquery-1.7.2.min.js\" type=\"text/javascript\"></script> \n");
  //print("<script src=\"js/jquery-1.7.1.js\" type=\"text/javascript\"></script> \n");
  print("<script src=\"js/jquery-1.7.2.min.js\" type=\"text/javascript\"></script> \n");
  print("<script type=\"text/javascript\" src=\"js/i18n/grid.locale-ua.js\"></script> \n");
  print("<script type=\"text/javascript\" src=\"js/jquery.jqGrid.min.js\"></script> \n");
  print("<script src=\"js/mmenu.js\" type=\"text/javascript\"></script> \n");
  print("<script type=\"text/javascript\" src=\"js/jquery.maskedinput.js\"></script> \n");
  print("<script type=\"text/javascript\" src=\"js/sliding.form.js\"></script> \n");
  print("<script src=\"js/jquery.dropdownPlain.js\" type=\"text/javascript\"></script> \n");  
 
}

function head_addr2page($g_nm1, $gdesc1, $f_nm1, $cap1, $col_nm1, $col_typ1, $cnt1, $col1, $add_par1, $hg, $wg, $col_wd, $col_ed, $y_tbar, $y_edit, $sortnm, $sortord, $gdesc2, $subgr) {
// 12 входящих переменных и неодного комментария)
//echo "sh# $g_nm1, $gdesc1, $f_nm1, $cap1, $col_nm1, $col_typ1, $cnt1, $col1, $add_par1, $hg, $wg, $col_wd, $col_ed, $y_tbar, $y_edit, $sortnm, $sortord, $gdesc2, $subgr <br>";
  print("<script type=\"text/javascript\">\n");
  print("\$(function (){ \n");
  print(" \$(\"#" . $g_nm1 . "_table\").jqGrid({\n");
  print("url:'" . $f_nm1 . ".php',\n");
  print("datatype: 'json',\n");
  print("mtype: 'POST',\n");
  print("height:" . $hg . ",\n");
  print("width:" . $wg . ",\n");
  print("hidegrid: false,\n");
  print("colNames:[\n");
  $j = 1;
  while ($j <= $cnt1) {
    $cl = $col1[$j];
    if ($j < $cnt1) {
      $t = ",";
    } else {
      $t = " ";
    }
    print("'" . $cl . "'" . $t . "\n");
    $j++;
  }
  print("],\n");
  print("colModel:[\n");
  $i = 1;
  while ($i <= $cnt1) {
    $tp = $col_typ1[$i];
    $wd = $col_wd[$i];
    $c_ed = $col_ed[$i];
    if ($tp == "date") {
      $tp1 = "text";
    } else {
      $tp1 = $tp;
    } //|| $tp="checkbox"
    if ($tp == "select") {
      $add_p = $add_par1[$i];
      $add = ",formatter:'select', editoptions: {value:{" . $add_p . "},size:10,maxlength:35} ";
    } else {
      if ($tp == "select1") {
        $tp = "select";
        $tp1 = "select";
        $add_p = $add_par1[$i];
        $add = ",formatter:'select' " . $add_p . " ";
      } /// , editoptions: {dataUrl:'".$add_p."',maxlength:35}
      else {
        if ($tp == "checkbox") {
          $add = ", editoptions: { value:\"t:f\" }";
        } // ,formatter:'checkbox',formatoptions:{disabled:true}
        else {
          if ($tp == "date") { //if ($c_ed==0) {
            $add = " ,formatter:'date',formatoptions:{newformat:'d-m-Y'}"; //,masks:{ShortDate}
            //} 
            //else {$add=" ";}
          } //,reformatAfterEdit:true////,formatter:'date',formatoptions:{newformat:'d-m-Y'}
          else {
            $add = $add_par1[$i];
          }
        }
      }
    }
    $nm = $col_nm1[$i];
    if ($i < $cnt1) {
      $t = ",";
    } else {
      $t = " ";
    }
    if ($c_ed == 1) {
      print("{name:'" . $nm . "',index:'" . $nm . "', width:" . $wd . ", sorttype:\"" . $tp . "\", editable:true , edittype:\"" . $tp1 . "\"" . $add . "}" . $t . "\n"); //".$tp."///
    } else {
      print("{name:'" . $nm . "',index:'" . $nm . "', width:" . $wd . ", sorttype:\"" . $tp . "\"" . $add . "}" . $t . "\n");
    }
    $i++;
  }
  print("],\n");

  print("onSelectRow: function(id){\n");
  print("          if (id) { \n");
  print("var ret = jQuery(\"#" . $g_nm1 . "_table\").jqGrid('getRowData',id);\n");
  print("id_g = ret." . $col_nm1[1] . ";\n");

  print("$.ajax({\n");
  print("url: '" . $f_nm1 . "_edit.php',\n");
  print("data : 'id_dov='+ id_g,\n");
  print("type : \"POST\",\n");
//	print("success: function (data) {\n");
//	print("alert (data);}\n");
  print("});\n");
  if ($gdesc1 != "o") {
    print(" jQuery(\"#" . $gdesc1 . "_table\").trigger(\"reloadGrid\");  \n");
  }
  if ($gdesc2 != "o") {
    print(" jQuery(\"#" . $gdesc2 . "_table\").trigger(\"reloadGrid\");  \n");
  }

  print("  }\n");
  print("},\n");

  if ($gdesc1 != "o") {
    print("loadComplete: function(){\n");
    print("id_g = 0;\n");
    print("$.ajax({\n");
    print("url: '" . $f_nm1 . "_edit.php',\n");
    print("data : 'id_dov='+ id_g,\n");
    print("type : \"POST\",\n");
    print("});\n");
    //if ($gdesc1!="o") {
    print(" jQuery(\"#" . $gdesc1 . "_table\").trigger(\"reloadGrid\");  \n"); //}
    if ($gdesc2 != "o") {
      print(" jQuery(\"#" . $gdesc2 . "_table\").trigger(\"reloadGrid\");  \n");
    }

    print("},\n");
  }

  if ($y_tbar == 1) {
    print("pager: '#" . $g_nm1 . "_tablePager',\n");
    print("rowNum:10,\n");
    print("rowList:[10,20,100],\n");
    print("gridview:true,\n");
    print("sortname: '" . $sortnm . "',\n");
    print("viewrecords: true,\n");
    print("caption:'" . $cap1 . "',\n");
    print("sortorder: '" . $sortord . "',\n");
    print("editurl: \"" . $f_nm1 . "_edit.php\",\n");
  } else {
    print("gridview:true,\n");
    print("sortname: '" . $sortnm . "',\n");
    print("viewrecords: true,\n");
    print("caption:'" . $cap1 . "',\n");
    print("sortorder: '" . $sortord . "',\n");
    print("editurl: \"" . $f_nm1 . "_edit.php\",\n");
  }

///////
  if ($subgr == 1) {
    print("subGrid: true, \n");

    print("subGridRowExpanded: function(subgrid_id, row_id) { \n");
    print("var subgrid_table_id; \n");
    print("subgrid_table_id = subgrid_id+'_t'; \n");

    print("$('#'+subgrid_id).html('<table id=\"'+subgrid_table_id+'\"></table><div id=\"'+subgrid_table_id+'_pager\"></div>');\n");
    print("$('#'+subgrid_table_id).jqGrid({ \n");
    print("url: 'p3e4.php?get=subgrid', \n");
    print("datatype: 'json', \n");
    print("mtype: 'POST', \n");
    print("postData: {'get':'subgrid', 'id':row_id}, \n");
    print("colNames: ['обладн.', 'стан', 'марка'], \n");
    print("colModel: [ \n");
    print("{name: 'id_dev', index: 'id_dev', width:1}, \n");
    print("{name: 'nm_dev', index: 'nm_dev', width:20}, \n");
    print("{name: 'stt', index: 'stt', width:5}, \n");
    print("{name: 'nm_typ_dev', index: 'nm_typ_dev', width:15} \n");
    print(" ], \n");
    print("height: 'auto', \n");
    print("autowidth: true, \n");
    print("rownumbers: true, \n");
    print("rownumWidth: 40, \n");
    print("rowNum: 3, \n");
    print("sortname: 'nm_dev', \n");
    print("sortorder: 'asc', \n");
    print("pager: $('#'+subgrid_table_id+'_pager'), \n");
    print("rowNum:3, \n");
    print("rowList:[3,10] \n");
    print("}); \n");
    print("}, \n");
  }
//////////



  print("});\n");

  if ($y_tbar == 1) {
    if ($y_edit == 1) {
      print("jQuery(\"#" . $g_nm1 . "_table\").navGrid('#" . $g_nm1 . "_tablePager',{edit:true,add:true,del:true,search:false}" .
              " ,{recreateForm:true},{recreateForm:true});\n");
    } else {
      if ($y_edit == 2) {
        print("jQuery(\"#" . $g_nm1 . "_table\").navGrid('#" . $g_nm1 . "_tablePager',{edit:true,add:true,del:true,search:false});\n");
      } else {
        print("jQuery(\"#" . $g_nm1 . "_table\").navGrid('#" . $g_nm1 . "_tablePager',{edit:false,add:false,del:false,search:false});\n");
      }
    }
  }

  print("});\n");


  print("</script>\n");
}

function head_paccpage($rn, $pg, $g_nm1, $gdesc1, $f_nm1, $cap1, $col_nm1, $col_typ1, $cnt1, $col1, $add_par1, $hg, $wg, $col_wd, $col_ed, $y_tbar, $y_edit) {
  $sort_op = ", sortable: true, sorttype: \"text\"";
  print("<script type=\"text/javascript\">\n");
  print("\$(function (){ \n");
  print(" \$(\"#" . $g_nm1 . "_table\").jqGrid({\n");
  print("url:'" . $f_nm1 . ".php',\n");
  print("datatype: 'json',\n");
  print("mtype: 'POST',\n");
  print("height:" . $hg . ",\n");
  print("width:" . $wg . ",\n");
  print("hidegrid: false,\n");
  print("colNames:[\n");
  $j = 1;
  while ($j <= $cnt1) {
    $cl = $col1[$j];
    if ($j < $cnt1) {
      $t = ",";
    } else {
      $t = " ";
    }
    print("'" . $cl . "'" . $t . "\n");
    $j++;
  }
  print("],\n");
  print("colModel:[\n");
  $i = 1;
  while ($i <= $cnt1) {
    $tp = $col_typ1[$i];
    $wd = $col_wd[$i];
    $c_ed = $col_ed[$i];
    if ($tp == "select") {
      $add_p = $add_par1[$i];
      $add = ", editoptions: {value:{" . $add_p . "},size:10,maxlength:35} ";
    } else {
      if ($tp == "checkbox") {
        $add = ", editoptions: { value:\"t:f\" }";
      } else {
        $add = "";
      }
    }
    $nm = $col_nm1[$i];
    if ($i < $cnt1) {
      $t = ",";
    } else {
      $t = " ";
    }
    if ($c_ed == 1) {
      print("{name:'" . $nm . "',index:'" . $nm . "', width:" . $wd . ",sorttype:\"" . $tp . "\", editable:true, edittype:\"" . $tp . "\"" . $add . "}" . $t . "\n");
    } else {
      print("{name:'" . $nm . "',index:'" . $nm . "', width:" . $wd . ",sorttype:\"" . $tp . "\"" . $add . "}" . $t . "\n");
    }
    $i++;
  }
  print("],\n");

  print("onSelectRow: function(id){\n");
  print("          if (id) { \n");
  print("var ret = jQuery(\"#" . $g_nm1 . "_table\").jqGrid('getRowData',id);\n");
  print("id_g = ret." . $col_nm1[1] . ";\n");
  print("var rn = jQuery(\"#" . $g_nm1 . "_table\").jqGrid('getGridParam','rowNum');\n");
  print("var pg = jQuery(\"#" . $g_nm1 . "_table\").jqGrid('getGridParam','page');\n");

  print("$.ajax({\n");
  print("url: '" . $f_nm1 . "_edit.php',\n"); //".$f_nm1."_edit.php
  print("data : 'id_dov='+ id_g,\n");
  print("type : \"POST\",\n");
//	print("success: function (data) {\n");
//	print("alert (data);}\n");
  print("});\n");

  print("$.ajax({\n");
  print("url: 'get_par.php',\n");
  print("data : 'postVar2='+ pg,\n");
  print("type : \"POST\",\n");
//	print("success: function (data) {\n");
//	print("alert (data);}\n");
  print("});\n");


  print("$.ajax({\n");
  print("url: 'get_par.php',\n");
  print("data : 'postVar3='+ rn,\n");
  print("type : \"POST\",\n");
//	print("success: function (data) {\n");
//	print("alert (data);}\n");
  print("});\n");


  if ($gdesc1 != "o") {
    print(" jQuery(\"#" . $gdesc1 . "_table\").trigger(\"reloadGrid\");  \n");
  }

  print("  }\n");
  print("},\n");

  if ($gdesc1 != "o") {
    print("loadComplete: function(){\n");
    print("id_g = 0;\n");
    print("$.ajax({\n");
    print("url: '" . $f_nm1 . "_edit.php',\n");
    print("data : 'id_dov='+ id_g,\n");
    print("type : \"POST\",\n");
    print("});\n");
    //if ($gdesc1!="o") {
//        print(" jQuery(\"#".$gdesc1."_table\").trigger(\"reloadGrid\");  \n"); //}
    print("},\n");
  }

  if ($y_tbar == 1) {
    print("pager: '#" . $g_nm1 . "_tablePager',\n");
    print("rowNum:20,\n");
//	print("loadonce:true,\n");
//   	print("rowNum:".$rn.",\n");
    //  	print("page:".$pg.",\n");
    // 	print("rowList:[10,20,100],\n");
    print("gridview:true,\n");
    print("sortname: 'naccnt',\n");
    print("viewrecords: true,\n");
    print("caption:'" . $cap1 . "',\n");
    print("sortorder: 'asc',\n");
    print("editurl: \"" . $f_nm1 . "_edit.php\",\n");
  }

  print("});\n");

  if ($y_tbar == 1) {
    if ($y_edit == 1) {
      print("jQuery(\"#" . $g_nm1 . "_table\").navGrid('#" . $g_nm1 . "_tablePager',{edit:true,add:true,del:true,search:false});\n");
    } else {
      if ($y_edit == 2) {
        print("jQuery(\"#" . $g_nm1 . "_table\").navGrid('#" . $g_nm1 . "_tablePager',{edit:false,add:true,del:true,search:false});\n");
//	print("jQuery(\"#".$g_nm1."_table\").filterToolbar({stringResult: true,searchOnEnter : false});\n");
      } else {
        print("jQuery(\"#" . $g_nm1 . "_table\").navGrid('#" . $g_nm1 . "_tablePager',{edit:false,add:false,del:false,search:false});\n");
      }
    }
  }

  print("});\n");

////////////////////////////////////////////

  print(" var timeoutHnd;\n");
  print("var flAuto = true; \n");


  print(" function doSearch(ev){ if(!flAuto) return; \n");
// var elem = ev.target||ev.srcElement; 

  print(" if(timeoutHnd) clearTimeout(timeoutHnd) \n");
  print(" timeoutHnd = setTimeout(gridReload,500) } \n");

  print(" function gridReload(){  \n");
  print(" var cd_mask = jQuery(\"#search_cd\").val(); \n");
  print(" var nm_mask = jQuery(\"#item\").val(); \n");
  print(" var tn_mask = jQuery(\"#n_town\").val(); \n");
  print(" var adr_mask = jQuery(\"#addr\").val(); \n");
  print(" var stt_mask = jQuery(\"#stt\").val(); \n");
  print(" var sal_mask = jQuery(\"#sald\").val(); \n");


  print(" jQuery(\"#" . $g_nm1 . "_table\").jqGrid('setGridParam',{url:\"" . $f_nm1 . ".php?cd_mask=\"+cd_mask+\"&nm_mask=\"+nm_mask+\"&tn_mask=\"+tn_mask+\"&adr_mask=\"+adr_mask+\"&stt_mask=\"+stt_mask+\"&sal_mask=\"+sal_mask,page:1}).trigger(\"reloadGrid\"); }  \n");
  /*
    //    	print(" function enableAutosubmit(state){ flAuto = state;  \n");
    //   	print(" jQuery("#submitButton").attr("disabled",state); } \n");

    //////////////////////////////////////////////
   */
  print("</script>\n");
}

function grid_addrpage($g_nm1, $g_nm2, $g_nm3, $g_nm4, $g_nm5, $g_nm6) {

  print("<div id=\"grid_dform\">\n");
  print("<table id=\"m_addr\">\n");
  print("<tr>\n");
  print("<td>\n");
  print("<table id=\"" . $g_nm1 . "_table\" ></table>\n");
  print("<div id=\"" . $g_nm1 . "_tablePager\">\n");
  print("</div>\n");
  print("</td>\n");
  print("<td>\n");
  print("<table id=\"" . $g_nm2 . "_table\" ></table>\n");
  print("<div id=\"" . $g_nm2 . "_tablePager\">\n");
  print("</div>\n");
  print("</td>\n");
  print("<td>\n");
  print("<table id=\"" . $g_nm3 . "_table\" ></table>\n");
  print("<div id=\"" . $g_nm3 . "_tablePager\">\n");
  print("</div>\n");
  print("</td>\n");
  print("</tr>\n");
  print("</table>\n");
  print("<table id=\"m_addr1\">\n");
  print("<tr>\n");
  print("<td>\n");
  print("<table id=\"" . $g_nm4 . "_table\" ></table>\n");
  print("<div id=\"" . $g_nm4 . "_tablePager\">\n");
  print("</div>\n");
  print("</td>\n");
  print("<td>\n");
  print("<table id=\"" . $g_nm5 . "_table\" ></table>\n");
  print("<div id=\"" . $g_nm5 . "_tablePager\">\n");
  print("</div>\n");
  print("</td>\n");
  print("<td>\n");
  print("<table id=\"" . $g_nm6 . "_table\" ></table>\n");
  print("<div id=\"" . $g_nm6 . "_tablePager\">\n");
  print("</div>\n");
  print("</td>\n");
  print("</tr>\n");
  print("</table>\n");
  print("</div>\n");
}

function grid_addrpage1($g_nm1) {

  print("<div id=\"grid_dform\">\n");
  print("<table id=\"m_addr\">\n");
  print("<tr>\n");
  print("<div> <input type=\"text\" size=\"6\" id=\"search_cd\" onkeydown=\"doSearch(arguments[0]||event)\" />  \n");
  print(" <input type=\"text\" size=\"42\" id=\"item\" onkeydown=\"doSearch(arguments[0]||event)\" /> \n");
  print(" <input type=\"text\" size=\"17\" id=\"n_town\" onkeydown=\"doSearch(arguments[0]||event)\" /> \n");
  print(" <input type=\"text\" size=\"42\" id=\"addr\" onkeydown=\"doSearch(arguments[0]||event)\" /> \n");
  print(" <input type=\"text\" size=\"10\" id=\"stt\" onkeydown=\"doSearch(arguments[0]||event)\" /> \n");
  print(" <input type=\"text\" size=\"7\" id=\"sald\" onkeydown=\"doSearch(arguments[0]||event)\" /> \n");

  print(" </div> \n");
  print("</tr>\n");
  print("<tr>\n");
  print("<td>\n");
  print("<table id=\"" . $g_nm1 . "_table\" ></table>\n");
  print("<div id=\"" . $g_nm1 . "_tablePager\">\n");
  print("</div>\n");
  print("</td>\n");
  print("</tr>\n");
  print("</table>\n");
  print("</div>\n");
}

function grid_paccntpage($g_nm1, $g_nm2) {

  print("<div id=\"grid_dform1\">\n");
  print("<table id=\"m_addr\">\n");
  print("<tr>\n");
  print("<td>\n");
  print("<table id=\"" . $g_nm1 . "_table\" ></table>\n");
  print("<div id=\"" . $g_nm1 . "_tablePager\">\n");
  print("</div>\n");
  print("</td>\n");
  print("</tr>\n");
  print("<tr>\n");
  print("<td>\n");
  print("<table id=\"" . $g_nm2 . "_table\" ></table>\n");
  print("<div id=\"" . $g_nm2 . "_tablePager\">\n");
  print("</div>\n");
  print("</td>\n");
  print("</tr>\n");
  print("</table>\n");
  print("</div>\n");
}

function grid_packpage($g_nm1, $g_nm2) {

  print("<div id=\"grid_dform2\">\n");
  print("<table id=\"m_addr\">\n");
  print("<tr>\n");
  print("<td>\n");
  print("<table id=\"" . $g_nm1 . "_table\" ></table>\n");
  print("<div id=\"" . $g_nm1 . "_tablePager\">\n");
  print("</div>\n");
  print("</td>\n");
  print("</tr>\n");
  print("<tr>\n");
  print("<td>\n");
  print("<table id=\"" . $g_nm2 . "_table\" ></table>\n");
  print("<div id=\"" . $g_nm2 . "_tablePager\">\n");
  print("</div>\n");
  print("</td>\n");
  print("</tr>\n");
  print("</table>\n");
  print("</div>\n");
}

function get_database_link($_SES, $mode = 0)
{
    if (isset($_SES['ses_link_str'])) 
    {
        
        $cons =$_SES['ses_link_str'];
        
        //die($cons);
        
        $new_link = pg_connect($cons) or die("Connection Error: " . pg_last_error($new_link));
        return $new_link;
    }
    else
    {
        if ($mode == 0)
        {
           header("Location: abon_en_login.php");
        }
        if ($mode == 1)
        {
            die("Истек срок ожидания (Время жизни сессии)! Обновите страницу!");
        }
        
        if ($mode == 2)
        {
            echo echo_result(2, "Истек срок ожидания (Время жизни сессии)! Обновите страницу!");
            die();
        }
    }
        
}

?>