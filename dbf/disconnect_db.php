<?php 


$status = @pg_connection_status($connection);

if ($status === PGSQL_CONNECTION_OK) 
  pg_close($connection);



?>