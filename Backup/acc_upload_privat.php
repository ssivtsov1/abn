<?php

header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();
error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];


$nmp1 = 'Завантаження оплат';
start_mpage($nmp1); //заголовок
head_addrpage();  // хедер, подключение библиотек

print('<link rel="stylesheet" type="text/css" href="css/ded_styles.css" /> ');
print('<link rel="stylesheet" type="text/css" href="css/layout-default-latest.css" /> ');
print('<script type="text/javascript" src="js/jquery.layout.min-latest.js"></SCRIPT>');
print('<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script> ');
//print('<script type="text/javascript" src="dov_posts.js"></script> ');

?>

    <script language="javascript">
      //Динамическое создание 2х кнопок и OpenDialog в диве
      function add_input(obj)
      {
        var new_input=document.createElement('div');
        var x = document.getElementById('my_div').getElementsByTagName('div').length;

        if(x > 19)
        {
          alert ("20 - максимальное кол-во загрузок");
          return;
        }
 
        new_input.innerHTML='<input type="button" value="-" title ="Удалить строку" onclick="del_input(this.parentNode);">';
        new_input.innerHTML=new_input.innerHTML+'<input type="button" value="+" title ="Добавить строку" onclick="add_input(this.parentNode);">';
        new_input.innerHTML=new_input.innerHTML+'<input type="file" size="70px" name="userfile[' + x.toString() + ']">';

        //   if (obj.nextSibling)
        //    document.getElementById('my_div').insertBefore(new_input,obj.nextSibling)
        //   else 
        document.getElementById('my_div').appendChild(new_input);
      }

      //Удаление дива по клику на кнопку
      function del_input(obj)
      {
        document.getElementById('my_div').removeChild(obj);
      }

    </script>
    <?php
    include ("acc_errors_in_file.php");

    if (!isset($_POST['up_form'])) { //до передачи параметров вывожу форму с выбором файлов
      $title = "Загрузка DBF-файлов (ПриватБанк)";

      echo '
<title>' . $title . '</title>
</head>';
 
echo '<body>
  
<DIV id="pmain_header"> ';
     main_menu();
echo '</DIV>
   
  <br><br>
  <a href="acc_dbf.php">Просмотреть список файлов</a><br> 
  
<div>
Виберіть файл для завантаження:<br>
<form enctype="multipart/form-data" action="upload_privat.php" method="post" id="file_form" name="file_form">
<div id="my_div">
<div><input type="button" value="+" onclick="add_input(this.parentNode);"><input type="file" size="70px" title ="Добавить строку" name="userfile[]"></div>
</div>
<input name="up_form" type="submit" value="Отправить" style="margin-left:500px;"/>
</form>		

</div>
';
    } else { //после передачи параметров
      $title = "Загрузка DBF-файлов (ПриватБанк) - Результат загрузки";
      $path = "privat_files/";

      echo '<title>' . $title . '</title>
</head>

<body> <a href="../abon_en_packs.php">Назад</a><br>';

//максимальный размер файлов для загрузки 4Мб.
      function check_size($file_size) {
        if ($file_size > 1024 * 4 * 1024)
          return false;
        else
          return true;
      }

//провека имени файла (для Приватбанка DNP*.dbf или DNK*.dbf)
      function check_bank($filename) {
        $start = substr(trim($filename), 0, 3);
        $end = substr(trim($filename), -4, 4);

        if (strcasecmp($start, "dnk") == 0 and strcasecmp($end, ".dbf") == 0)
          return 1;
        elseif (strcasecmp($start, "dnp") == 0 and strcasecmp($end, ".dbf") == 0)
          return 2;
        elseif (strcasecmp($start, "kvi") == 0 and strcasecmp($end, ".dbf") == 0)
          return 3;
        else
          return 0;
      }

// Проверка загружен ли файл в каталог $path на сервере
      function check_loading($path, $tmp_file, $file) {
        $result = false;
        if (is_uploaded_file($tmp_file)) {
          move_uploaded_file($tmp_file, $path . $file);
          $result = true;
        }
        return $result;
      }

//проверка, загружался ли этот файл раньше
      function load_before($file, $md5, $pref) {
        $fin = 0;
        $filename = substr($file, 0, strlen($file) - 4); //без расширения
        $query_text = "SELECT id,filename,md5 FROM dn_stat_tbl WHERE md5='$md5'";
        include("connect_db.php");
        $result = pg_query($connection, $query_text) or die(pg_last_error());
        $row = pg_fetch_row($result);
        $fin = $row[0];  // row[0] = id
        if ($fin > 0) {  // мд5 найден! файл не загружать
        } else {
          // мд5 не найдет, ищем дальше по имени файла
          $query_text = "SELECT id,filename,md5,id_person,dt FROM dn_stat_tbl WHERE filename='$filename'";
          $result = pg_query($connection, $query_text) or die(pg_last_error());
          $row = 0;
          $row = pg_fetch_row($result);
          if($row[0] > 0) {  // имя файла найдено, удаляем все записи по id
            $del_id = $row[0];
            switch ($pref) {  // определяем от какого банка нужно удалить
              case 1: $table = 'dn_k_tbl'; break;
              case 2: $table = 'dn_p_tbl'; break;
              case 3: $table = 'dn_oschad_tbl'; break;
              default: break;
            }
            $query_text = "DELETE FROM $table WHERE id_stat=$del_id";
            echo 'Файл ранее загружался под именем "',$row[1],'". Пользователем №',$row[3],'. Время загрузки: ',$row[4],'. Те данные были удалены.<br>';
            $result = pg_query($connection, $query_text) or die(pg_last_error());
          }
        }

        include("disconnect_db.php");
        return $fin;
      }

      //проверка числа
      function check_int($val) {
        $val = trim(htmlspecialchars(pg_escape_string($val)));

        if (substr($val, 0, 1) == "-") { //отрицательное число
          if (ereg("^[0-9]+$", substr($val, 1, strlen($val))))
            return $val;
        }
        elseif (ereg("^[0-9]+$", $val))
          return $val;

        return "NULL";
      }

      //проверка строки
      function check_varchar($val) {
        $val = trim(htmlspecialchars(pg_escape_string($val)));

        if ($val == '')
          return "NULL";
        else
          return "'" . $val . "'";
      }

      //проверка даты
      function check_date($val) {
        $val = trim(htmlspecialchars(pg_escape_string($val)));

        if (strpos($val, "-") > 0 and strrpos($val, "-") > 0 and strpos($val, "-") != strrpos($val, "-")) {
          $dmg = explode("-", $val);
          if (!checkdate($dmg[1], $dmg[2], $dmg[0]))
            return "NULL";
          else
            return "'" . $val . "'";
        }
        elseif (check_int($val) != "NULL" and strlen($val) == 8) {
          $year = substr($val, 0, 4);
          $mon = substr($val, 4, 2);
          $day = substr($val, 6, 2);

          if (!checkdate($mon, $day, $year))
            return "NULL";
          else
            return "'" . $year . "-" . $mon . "-" . $day . "'";
        }
      }

//перел вставкой данных в базу...
      function check($val, $i) {
        if ($i == 0) //число
          return check_int(iconv('CP866', 'utf-8', $val));
        elseif ($i == 1) //строка
          return check_varchar(iconv('CP866', 'utf-8', $val));
        elseif ($i == 2) //дата
          return check_date(iconv('CP866', 'utf-8', $val));
      }

//Вставка в базу 
      function dbf2psql($file, $pref, $id_person, $md5_str) {
        $db = dbase_open($file, 0);
        if ($db) {
          $rows = dbase_numrecords($db);
          if ($rows == 0)
            return 1; //пустой файл

          for ($i = 1; $i <= $rows; $i++)
            // $row[] = dbase_get_record_with_names($db, $i);
            $row[] = dbase_get_record($db, $i);

          dbase_close($db);

          if ($id_person == NULL)
            $id_person = 0;

          $query_text = "select CASE WHEN (max(id) IS NULL) then 1 else max(id) + 1 end as id from dn_stat_tbl";

          include("connect_db.php");
          $id = pg_query($connection, $query_text) or die(pg_last_error());
          if (!$id)
            return 2;

          $r = pg_fetch_row($id);
          $id_stat = $r[0];
          
          echo sizeof($row[1]),' :: size<br>';

          for ($i = 0; $i < count($row); $i++) {

            if ($pref == 1) //dnk
              $query_text = "INSERT INTO dn_k_tbl(id_stat, city, tel, lich, fio, addr) 
	  VALUES (" . check($id_stat, 0) . ", " . check($row[$i]['CITY'], 1) . ", " . check($row[$i]['TEL'], 0) . ", " . check($row[$i]['LICH'], 1) . ", " . check($row[$i]['FIO'], 1) . ", " . check($row[$i]['ADDR'], 1) . ");";

            elseif ($pref == 2) //dnp  
              $query_text = "INSERT INTO dn_p_tbl(id_stat, city, ko, tel, suma, lich, data, fio, addr, branch)
    	VALUES (" . check($id_stat, 0) . ", " . check($row[$i]['CITY'], 1) . ", " . check($row[$i]['KO'], 1) . ", " . check($row[$i]['TEL'], 0) . ", " . check($row[$i]['SUMA'], 0) . ", " . check($row[$i]['LICH'], 1) . ", " . check($row[$i]['DATA'], 2) . ", " . check($row[$i]['FIO'], 1) . ", " . check($row[$i]['ADRES'], 1) . ", " . check($row[$i]['BRANCH'], 1) . ");";

            //----- OSCHAD
            elseif ($pref == 3)
              $query_text = "INSERT INTO dn_oschad_tbl VALUES (nextval('dn_k_seq')"; // ||,55,'var 1','wet 2','jeckkk 3',5)
    	        $query_text .= ','.$id_stat;
              
                for($j=0; $j<45; $j++){
                  $query_text .= ','.check($row[$i][$j],1);
                }
                $query_text .= ');';
                  //  . check($row[$i]['CITY'], 1) . ", " . check($row[$i]['KO'], 1) . ", " . check($row[$i]['TEL'], 0) . ", " . check($row[$i]['SUMA'], 0) . ", " . check($row[$i]['LICH'], 1) . ", " . check($row[$i]['DATA'], 2) . ", " . check($row[$i]['FIO'], 1) . ", " . check($row[$i]['ADRES'], 1) . ", " . check($row[$i]['BRANCH'], 1) . ");";
            //------------
            
            $res = pg_query($connection, $query_text) or die(pg_last_error());
            if (!$res)
              return 2;
          }

          $filename = substr($file, strrpos($file, "/") + 1, strlen($file) - (strrpos($file, "/") + 1) - 4);

          if ($pref == 1) $tab = "dn_k_tbl";
          if ($pref == 2) $tab = "dn_p_tbl";
          if ($pref == 3) $tab = "dn_oschad_tbl";

          $query_text = "INSERT INTO dn_stat_tbl(filename, id_person, row_count, md5) VALUES ('$filename', $id_person, (SELECT count(*) FROM $tab WHERE id_stat = $id_stat), '$md5_str');";

          $r1 = pg_query($connection, $query_text) or die(pg_last_error());
          if (!$r1)
            return 2; //ошибка при загрузке данных

          include("disconnect_db.php");
          return 0;
        }
      }

      /*

        Максимальное число файлов для одновременной загрузки на сервер 20шт. (php.ini max_file_uploads)
        Перебираю в цикле эти файлы, после проверок заношу инф-цию для вывода на экран
        в массив строк $result_files.

        проверки:
        - выбран ли файл
        - check_size - размер < 4Мб. (в php.ini 8Мб. upload_max_filesize и post_max_size)
        - check_bank - типы файлов смотрю по названию (dnk*.dbf и  dnp*.dbf для ПриватБанка)
        - check_loading - скопировался ли файл на сервер ( у юзера apache должны быть права на чтение и запись в $path = "privat_files/")
        - load_before - загружался ли этот файл раньше (проверяю запросом из базы)
        - проверки значений в файле перед вставой: check_int, check_varchar, check_date

        Функция dbf2psql($file, $pref, $id_person) вставляет данные из файда в базу (файл парсится библиотекой dbase)
        $file - путь/имя_файла,
        $pref - 1 - dnk_файл, 2 - dnp_файл,
        $id_person - код пользователя, загружающего данные (0 если пустой)

       */
      for ($i = 0; $i < 20; $i++) {
        if (isset($_FILES['userfile']['name'][$i]) && trim($_FILES['userfile']['name'][$i]) != "") {
          if (check_size($_FILES['userfile']['size'][$i])) {

            $file_pref = check_bank($_FILES['userfile']['name'][$i]); //1 - dnk, 2 - dnp
            $id_person = 0; //id пользователя 0 - с сервера

            if ($file_pref == 0)
              $result_files[$i] = "неправильная структура файла <strong>'" . $_FILES['userfile']['name'][$i] . "'</strong> для загрузки";
            else {
              if (check_loading($path, $_FILES['userfile']['tmp_name'][$i], $_FILES['userfile']['name'][$i])) {
                
                $g_file = $path . $_FILES['userfile']['name'][$i];
                $hash_str = md5_file($g_file);
                
                if (load_before($_FILES['userfile']['name'][$i], $hash_str, $file_pref) == 0) {
                  $dbf = dbf2psql($g_file, $file_pref, $id_person, $hash_str);
                  if ($dbf == 0)
                    $result_files[$i] = "данные из файла <strong>'" . $_FILES['userfile']['name'][$i] . "'</strong> успешно загружены в БД. Хэш-код=" . strtoupper($hash_str);
                  elseif ($dbf == 1)
                    $result_files[$i] = "файл <strong>'" . $_FILES['userfile']['name'][$i] . "'</strong> не содержит данных";
                  elseif ($dbf == 2)
                    $result_files[$i] = "ошибка при загрузке данных из файла <strong>'" . $_FILES['userfile']['name'][$i] . "'</strong> в БД";
                }
                else
                  $result_files[$i] = "данные из файла <strong>'" . $_FILES['userfile']['name'][$i] . "'</strong> уже загружались в БД ранее. Хэш-код=" . $hash_str;
              }
              else
                $result_files[$i] = "не удалось сохранить файл <strong>'" . $_FILES['userfile']['name'][$i] . "'</strong> на сервере";
            }
          }
          else
            $result_files[$i] = "размер файла <strong>'" . $_FILES['userfile']['name'][$i] . "'</strong> > 4Мб.";
        }
        elseif (isset($_FILES['userfile']['name'][$i]) && trim($_FILES['userfile']['name'][$i]) == "")
          $result_files[$i] = "файл не выбран";
      }

      for ($i = 0; $i < 20; $i++)
        if (isset($_FILES['userfile']['name'][$i]))
          echo "<strong>" . ($i + 1) . " - </strong>" . $result_files[$i] . "<br>";

      echo '<br><a style="margin-left:10px;" href="upload_privat.php">Вернуться к выбору файлов</a>';
    }
    
    // --------------------------------------------------------------------------------
    // ВСТАВИТЬ ГРИД ТУТ !!
    //
    
//require '../acc_dbf.php';
    
    ?>
    </body>
</html>