<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Pluging</title>
</head>
<body>
    <div class="parsing-google-sheets">
        <form action="#"  method="post">
            <label for="id-table">ID таблицы:</label>
            <input type="text" id="id-table" name="id-table" value="1Q5bw0D9y3_WfyhFhimr7_GgJZxxUNSMltOEW7WAFsuo">
            <label for="id-sheet">ID листа:</label>
            <input type="text" id="id-sheet" name="id-sheet" value="332621208">
            <input type="submit" name="SubmitButton" value="Парсинг и категоризация данных в таблице">
        </form>
        <h1>
        <?php
        echo "hello world\n";
        ?>
        <?php
            
                $in = "sort"
                $out = "python myscript.py " . $in;
                echo $out;
                $output = shell_exec($out);
                echo $output;
        ?>
        </h1>
    </div>

    <div class="import-data"  method="post">
        <form action="#">
            <input type="submit" value="Импортировать данные из Google Sheets">
        </form>
        <textarea name="import-area" id="import-area" cols="30" rows="10" disabled></textarea>
        <h1></h1>
    </div>
</body>
</html>