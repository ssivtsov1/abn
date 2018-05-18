<?php
header('Content-type: text/html; charset=utf-8');
?>

<html>
  <head>
    <title>админка синхонизации</title>
    <script>
      function cent_gl($par) {
        // window.location = "adm_sync.php";
        switch($par) {
          case 1:
            alert('Создать дамп, частично, для РЕСа №' + document.getElementById('central_part_val').value);
            break;
          case 2:
            alert('Восстановить дамп, частично, для РЕСа №' + document.getElementById('central_part_val').value);
            break;
          case 3:
            alert('Создать дамп, полностью, для РЕСа №' + document.getElementById('central_full_val').value);
            break;
          case 4:
            alert('Восстановить дамп, полностью, для РЕСа №' + document.getElementById('central_full_val').value);
            break;
          case 5:
            alert('Создать дамп, полностью, для всех РЕСов');
            break;
          case 6:
            alert('Восстановить дамп, полностью, для всех РЕСов');
            break;
          default:
            alert('cent_gl :: Error 0');
        }
      }
      // RES -----------------------------------------------------------------------------
      function res_gl($par) {
        // window.location = "adm_sync.php";
        switch($par) {
          case 1:
            $r1 = 'Создать дамп, частично, для центральной';
            if(document.getElementById('res_part_ch').checked){ $r1 += ' и срочно!'; }
            alert($r1);
            break;
          case 2:
            $r1 = 'Восстановить дамп, частично, для центральной';
            if(document.getElementById('res_part_ch').checked){ $r1 += ' и срочно!'; }
            alert($r1);
            break;
          case 3:
            $r1 = 'Создать дамп, полностью, для центральной';
            if(document.getElementById('res_full_ch').checked){ $r1 += ' и срочно!'; }
            alert($r1);
            break;
          case 4:
            $r1 = 'Восстановить дамп, полностью, для центральной'
            if(document.getElementById('res_full_ch').checked){ $r1 += ' и срочно!'; }
            alert($r1);
            break;
          default:
            alert('res_gl :: Error 0');
        }
      }
      
    </script>
  </head>
  <body bgcolor="6B8E55">
    <a href="backup.php">назад</a><br>
    <form method="POST" action="adm_sync_central.php">
      <table>
        <tr>
          <td><br>Центральная:</td>
        </tr>
        <tr>
          <td align="right">частично</td>
          <td><input type="button" onclick="cent_gl(1); return false;" value="Создать дамп"></td>
          <td><input type="button" onclick="cent_gl(2); return false;" value="Восстановить дамп"></td>
          <td>РЕС <input type="text"id="central_part_val" value="320" maxlength="3" size="5"></td>
        </tr>
        <tr>
          <td align="right">полностью</td>
          <td><input type="button" onclick="cent_gl(3); return false;" value="Создать дамп"></td>
          <td><input type="button" onclick="cent_gl(4); return false;" value="Восстановить дамп"></td>
          <td>РЕС <input type="text" id="central_full_val" value="320" maxlength="3" size="5"></td>
        </tr>
        <tr>
          <td align="right">для всех РЕСов</td>
          <td><input type="button" onclick="cent_gl(5); return false;" value="Создать дамп"></td>
          <td><input type="button" onclick="cent_gl(6); return false;" value="Восстановить дамп"></td>
        </tr>
        <tr>
          <td><br>РЕС:</td>
        </tr>
        <tr>
          <td align="right">частично</td>
          <td><input type="button" onclick="res_gl(1); return false;" value="Создать дамп"></td>
          <td><input type="button" onclick="res_gl(2); return false;" value="Восстановить дамп"></td>
          <td><input id="res_part_ch" type="checkbox"> срочно!</td>
        </tr>
        <tr>
          <td align="right">полностью</td>
          <td><input type="button" onclick="res_gl(3); return false;" value="Создать дамп"></td>
          <td><input type="button" onclick="res_gl(4); return false;" value="Восстановить дамп"></td>
          <td><input id="res_full_ch" type="checkbox"> срочно!</td>
        </tr>
      </table>
    </form>
  </body>
</html>