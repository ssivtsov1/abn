<html>
  <head></head>
  <body bgcolor="6699CC">
    <a href="./backup.php">back</a><br><br>
    <a href="./version_hash.php">/refresh</a><br><br>
    <form action="version_hash_get.php" method="post" enctype="multipart/form-data">
      <input type="submit" value="GO"><br>
    </form>
    <?php
    require 'app_config.php';

    // FUNCTIONS --------------------------------------------------------------
    function getHash($arg) {
      return md5($arg . "SoLz1208"); // this is a secret! :)
    }

    function checkHash($arg) {
      $ret = 0;
      $s_hash = substr($arg, 0, 32);
      $s_num = substr($arg, 32);
      if (getHash($s_num) == $s_hash)
        $ret = 1;
      return $ret;
    }

    // SQL ----------------------------------------------------------------------
    global $app_maint_cstr;
    $c_str = $app_maint_cstr; //"host=10.71.1.94 dbname=sys_en user=local password=postgres";
    $lnk = pg_connect($c_str);

    $sql_select = "SELECT id,control_key FROM update_ver ORDER BY id DESC LIMIT 1"; // берем последний ключ
    $result = pg_query($lnk, $sql_select);
    $res = pg_fetch_array($result);
    $row = $res['control_key'];

    $num_insert = (int) $row + 1; // добавляем 1
    $hash_insert = getHash($num_insert);
    $sql = "INSERT INTO update_ver (hash, control_key) VALUES ('$hash_insert', '$num_insert')"; // вставляем новый
    pg_query($lnk, $sql);
    pg_close($lnk);

    // FILE --------------------------------------------------------------------
    $f_path = './security.key'; // chmod = rw-rw-rw-
    if (!file_exists($f_path)) {
      echo "file not exists<br>";
    }
    $f = fopen($f_path, 'w') or die("can't open file");
    fwrite($f, $hash_insert . $num_insert);
    fclose($f);

    // ECHOS -------------------------------------------------------------------
    echo "#$num_insert<br>";
    echo 'hash: <a style="color: darkred;">' . strtoupper($hash_insert) . '</a> // ' . date('Y-m-d H:i:s') . ' time@10.71.1.94 <br>';

    // WORK -------------------------------------------------------------------------
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $filename = './backup_hash/work.sh';
      if (file_exists($filename)) {
        echo 'ok';
        exec($filename);
      }
    }
    ?>

  </body>
</html>