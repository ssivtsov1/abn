<?php
   $fullPath = "../tmp/".$_GET['path'];
   if (!is_file($fullPath)) { die("<b>404 File not found!</b>"); }
   
   $len = filesize($fullPath);
   $filename = basename($fullPath);
   //$filename='12345';
   
   header("Pragma: public");
   header("Expires: 0");
   header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
   header("Cache-Control: public");
   header("Content-Description: File Transfer");

   header("Content-Type: Application/vnd.ms-excel");   
   header("Content-Disposition: attachment; filename=$filename");
   header("Content-Transfer-Encoding: binary");
   header("Content-Length: ".$len);
   
   readfile($fullPath);
?>