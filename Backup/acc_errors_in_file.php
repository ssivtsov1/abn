<?php
//Вывод ошибок в файл	
set_error_handler('err_handler');

function err_handler($errno, $errmsg, $filename, $linenum)
   {
	
   // if (!in_array($errno, Array(E_NOTICE, E_STRICT, E_WARNING))) {

        $date = date('Y-m-d H:i:s (T)');
   
        $f = fopen('errors.log', 'a');
       
        if (!empty($f)) 
		{
            $err  = "<error>\r\n";
            $err .= "  <date>$date</date>\r\n";
            $err .= "  <errno>$errno</errno>\r\n";
            $err .= "  <errmsg>$errmsg</errmsg>\r\n";
            $err .= "  <filename>$filename</filename>\r\n";
            $err .= "  <linenum>$linenum</linenum>\r\n";
            $err .= "</error>\r\n";
			
			flock($f, LOCK_EX);
			fwrite($f, $err);
			flock($f, LOCK_UN);
            
            fclose($f);
         }
       //}
    }
?>





