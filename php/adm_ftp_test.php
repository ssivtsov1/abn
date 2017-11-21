<?
header('Content-type: text/html; charset=utf-8');
?>

<html>
  <head>
    <title>ftp</title>
  </head>
  <body>
    <a href="backup.php">назад</a><br>
    <form action="ftp_test.php" method="POST" enctype="multipart/form-data">
      <table align="center">
        <tr>
          <td align="right"> Сервер: </td>
          <td>
            <input size="50" type="text" name="server" value="10.71.1.94">
          </td>
        </tr>
        <tr>
          <td align="right"> Пользователь: </td>
          <td>
            <input size="50" type="text" name="user"  value="scyther">
          </td>
        </tr>
        <tr>
          <td align="right"> Пароль: </td>
          <td>
            <input size="50" type="password" name="password" value="9035" >
          </td>
        </tr>
        <tr>
          <td align="right"> Путь на сервере: </td>
          <td>
            <input size="50" type="text" name="pathserver" value="./tmp" >
          </td>
        </tr>
        <tr>
          <td align="right"> Выберете файл для загрузки: </td>
          <td>
            <input name="userfile" type="file" size="50">
          </td>
        </tr>
      </table>
      <table align="center">
        <tr>
          <td align="center">
            <input type="submit" name="submit" value="Отправить" />
          </td>
        </tr>
      </table>
    </form>

  </body>
</html>

<?

//-------------------------------------
// "php.ini"
// 
// post_max_size = 32M
// upload_max_filesize = 32M
// 
//-------------------------------------

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

// the file name that should be uploaded
  $filep = $_FILES['userfile']['tmp_name'];
// ftp server
  $ftp_server = $_POST['server'];
//ftp user name
  $ftp_user_name = $_POST['user'];
//ftp username password
  $ftp_user_pass = $_POST['password'];
//path to the folder on which you wanna upload the file
  $paths = $_POST['pathserver'];
//the name of the file on the server after you upload the file
  $name = $_FILES['userfile']['name'];

//-------------
  $conn_id = ftp_connect($ftp_server);
// login with username and password
  $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
// check connection
  if ((!$conn_id) || (!$login_result)) {
    echo "FTP connection has failed!";
    echo "Attempted to connect to $ftp_server for user $ftp_user_name....";
    exit;
  } else {
    echo "Connected to $ftp_server, for user $ftp_user_name" . "...<br>";
  }

  // upload the file
  $pa_name = $paths . '/' . $name;
  // echo '<br>', $pa_name, '<br>';
  $upload = ftp_put($conn_id, $pa_name, $filep, FTP_BINARY);

// check upload status
  if (!$upload) {
    echo "FTP upload has failed!";
  } else {
    echo "Uploaded $name to $ftp_server ";
  }

  ftp_close($conn_id);
}

?>