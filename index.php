<?php
echo "hello world"
// $output = shell_exec("python myscript.py");
// var_dump($output); 
// echo "succes"; 
is_callable('shell_exec') && false === stripos(ini_get('disable_functions'), 'shell_exec');
echo "succes";
?>