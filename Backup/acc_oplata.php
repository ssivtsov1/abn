<?php
header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

$gn1 = 'acc_oplata';
$gn1_table = $gn1.'_table';
$gn1_pager = $gn1.'_tablePager';

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

print('<script type="text/javascript" src="acc_oplata.js?version='.$app_version.'"></script> ');

?>
    <title>Разнесение оплаты абонента</title>
    <script>
      function clean_in(par){
        id = 'in' + par.toString();
        if(par == 4) {
          document.getElementById('log1').value = '';
          id = 'in1';
        } else {
          document.getElementById(id).value = '';
        }
        document.getElementById(id).select();
      }
      
      function clean_all(attr){
        if(attr == 0){
          document.getElementById('in1').value = '';
          document.getElementById('in2').value = '';
          document.getElementById('in3').value = '';
        }
      }
      
      function log_all(attr){
        if(attr == 0){
          str = 'book['+document.getElementById('in1').value+'] count[';
          str += document.getElementById('in2').value+'] sum[';
          str += document.getElementById('in3').value+']';
          document.getElementById('log1').value = str;
        }
      }
      
      function hh() {
        // log_all(0);
        // clean_all(0);
        // $('#in1').select();
        // return false;
      }

      $(function(){
        document.getElementById('in1').select();
        $('#in1').keypress(function(ev){
          if(ev.which == 13){
            $('#in2').select();
            return false;
          }
        });
        $('#in2').keypress(function(ev){
          if(ev.which == 13){
            $('#in3').select();
            return false;
          }
        });
        $('#in3').keypress(function(ev){
          if(ev.which == 13){
            hh();
          }
        });
      })
    </script>
  </head>
  <body>
    <DIV id="pmain_header"> 
<?     main_menu(); ?>
</DIV>
    <br><br>
    <form action="acc_oplata.php" method="POST">
      <table>
        <tr><td>Книга</td><td>Лицевой счет</td><td>Сумма</td></tr>
        <tr>
          <td><input type="text" tabindex="11" name="in1" value="27" id="in1" maxlength="12"></td>
          <td><input type="text" tabindex="12" name="in2" value="15" id="in2" maxlength="12"></td>
          <td><input type="text" tabindex="13" name="in3" value="311,73" id="in3" maxlength="12"></td>
          <td><input type="submit" tabindex="-1" value="Сохранить"></td>
        </tr>
      </table>
    </form>
    <br>
    <?
    $id_headplay = 9; // ид в headpay
    $id_person = 0; // ид юзера

    if (!isset($reg_num)) { // определение масимального номера в пачке
      $reg_num_query = "SELECT MAX(reg_num) FROM acm_pay_tbl WHERE id_headpay=$id_headplay"; // ONES
      $result = pg_query($Link, $reg_num_query);
      $row = pg_fetch_row($result, 0);
      $reg_num = $row[0];
    } else {
      $reg_num = 0;
    }

    if (!isset($reg_date)) { // инициализирует дату по дате пачки
      $reg_date_query = "SELECT reg_date FROM acm_headpay_tbl WHERE id_head=$id_headplay";
      $result = pg_query($Link, $reg_date_query);
      $row = pg_fetch_row($result, 0);
      $reg_date = $row[0];
      if($reg_date == '') {
        $reg_date = '2012-01-01';
      }
    } else {
      $reg_date = '2012-01-01';
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $in1 = $_POST['in1'];
      $in2 = $_POST['in2'];
      $in3 = $_POST['in3'];
      $reg_num++; // инкремент номера в пачке

      if (!isset($id_paccnt)) { // инициализирует дату по дате пачки
        $id_paccnt_query = "SELECT id FROM clm_paccnt_tbl WHERE book=$in1 AND code=$in2 LIMIT 1";
        $result = pg_query($Link, $id_paccnt_query);
        $row = pg_fetch_row($result, 0);
        $id_paccnt = $row[0];
      } else {
        $id_paccnt = 0;
      }
      if ($id_paccnt == NULL) {
        $id_paccnt = 0;
      }
      $value_pay = number_format(str_replace(',', '.', $in3), 2, '.', ''); // сумма оплаты +заменяем , -> . 
      $value_tax = number_format($value_pay / 6, 2, '.', ''); // ПДВ 20%
      $value = number_format($value_pay - $value_tax, 2, '.', ''); // сумма без ПДВ
      $str_query = "INSERT INTO acm_pay_tbl VALUES ($id_headplay,nextval('acm_pay_seq'),now(),$id_person,1,10,$reg_num,'$reg_date'," .
              "$id_paccnt,$value,$value_tax,$value_pay,(SELECT reg_date FROM acm_headpay_tbl WHERE id_head=$id_headplay))";
      pg_query($Link, $str_query);
// echo '<br>',$str_query,'<br>';
// INSERT INTO acm_pay_tbl VALUES (5,nextval('acm_pay_seq'),now(),0,1,10,1,'2012-01-01',12528,259.77,51.96,311.73,(SELECT reg_date FROM acm_headpay_tbl WHERE id_head=5))
      echo "Добавлено в базу: Пачка №$id_headplay, запись №$reg_num, Книга $in1, лицевой $in2, сумма = $value_pay грн.";
    }
    ?>
    <div id="grid_dform">    
      <table id="<?php echo $gn1_table ?>" > </table>
      <div id="<?php echo $gn1_pager ?>" ></div>
    </div>
  </body>
</html>