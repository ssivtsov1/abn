<? header('Content-type: text/html; charset=utf-8'); ?>

<html>
  <head>
    <title>Восстановление базу данных из файла</title>
  </head>
  <body>
    <h2><p><b>Загрузка файла</b></p></h2>
    <form action="backup_get.php" method="post" enctype="multipart/form-data">
      <input type="file" name="filename"><br> 
      <input type="submit" value="Восстановить"><br>
    </form>
    <?
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $jj = $_FILES["filename"]["size"];
      echo "$jj size<br>";
      if ($jj > 32 * 1024 * 1024) {
        echo ("Размер файла превышает 32 мегабайт");
        exit;
      }
      // Проверяем загружен ли файл
      if (is_uploaded_file($_FILES["filename"]["tmp_name"])) {
        // Если файл загружен успешно, перемещаем его
        // из временной директории в конечную
        $b = move_uploaded_file($_FILES["filename"]["tmp_name"], "./backup/abn.dmp.gz");
        if($b == 1){
          if(file_exists('./backup/rest.sh')){
            echo 'ok';
            exec('./backup/rest.sh');
          }
        }
      } else {
        echo("Ошибка загрузки файла");
      }
    }
    ?>
  </body>
</html>

