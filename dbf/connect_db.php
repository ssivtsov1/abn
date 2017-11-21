<?php

	$host = "10.71.1.94";
	$user = "postgres";
	$pass = "postgres";
	$db = "abn_en_mcn";

	$connection = pg_connect ("host=$host dbname=$db user=$user password=$pass");

	if (!$connection)
	{ 
	  echo'<p><strong>Ошибка подключения к БД!</strong></p>';
      exit(); 
	}
?>