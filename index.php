<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pluging</title>
</head>
<body>
    <h1>
        <?php
        echo "hello world";
        $output = shell_exec("python myscript.py");
        var_dump($output);
        echo "succes";
        ?>
    </h1>
</body>
</html>