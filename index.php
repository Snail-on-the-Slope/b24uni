<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@300&display=swap" rel="stylesheet">
    <title>Pluging</title>
</head>
<body>
    <div class="parsing-google-sheets">
        <form action=""  method="post">
            <label for="idTable">ID таблицы</label>
            <input type="text" id="idTable" name="idTable" value="1Q5bw0D9y3_WfyhFhimr7_GgJZxxUNSMltOEW7WAFsuo">
            <label for="idSheet">ID листа</label>
            <input type="text" id="idSheet" name="idSheet" value="332621208">
            <label for="cells">Ячейки URL</label>
            <input type="text" id="cells" name="cells" value="A4:A181">
            <input type="submit" name="SubmitParsing" value="Парсинг и категоризация данных в таблице">
            <p>процесс займет некоторое время...</p>
        </form>
        <h1>  
        <?php
            if(isset($_POST['SubmitParsing'])){
                $idTable = $_POST['idTable'];
                $idSheet = $_POST['idSheet'];
                $cells = $_POST['cells'];
                if ($idTable != null && $idSheet != null && $cells != null) {
                    $out = "python myscript.py " . $idTable . " " . $idSheet . " " . $cells;
                    $output = shell_exec($out);
                    $time  = date("H:i:s", mktime(date("H")+3, date("i")+3, date("s")+3, 0, 0, 0));
                    echo "Парсинг завершен ", $time;
                }
            }
        ?>
        </h1>
    </div>

    <div class="import-data">
        <form action="#" method="post">
            <div class="obligatory">
                <label for="idTableImport">ID таблицы</label>
                <input type="text" id="idTableImport" name="idTableImport" value="1Q5bw0D9y3_WfyhFhimr7_GgJZxxUNSMltOEW7WAFsuo">
                <label for="idSheetImport">ID листа</label>
                <input type="text" id="idSheetImport" name="idSheetImport" value="332621208">
            </div>
            <p>Создание / Обновление карточки компании с полями и значениями:</p>
            <div class="optional" id="add_field_area">
                <div class="all-field">
                    <div class="field-optional">
                        <label for="fieldimport">Поле</label>
                        <input type="text" id="fieldimport" name="fieldimport" value="URL">
                    </div>
                    <div class="cells-optional">
                        <label for="cellsimport">Ячейки</label>
                        <input type="text" id="cellsimport" name="cellsimport" value="A4:A181">
                    </div>
                    <button onclick="return addField();" class="plus-button">+</button>
                    <button onclick="return deleteField(this);" class="plus-button hide">-</button>
                </div>
                <div class="error"></div>
            </div>
            <div id="new_fields"></div>
            <input type="submit" name="SubmitImport" value="Импортировать данные из Google Sheets">
        </form>
        <textarea name="import-area" id="import-area" cols="30" rows="10" disabled>
            <?php
                if(isset($_POST['SubmitImport'])){
                    $array = [];
                    $k = 0;
                    foreach($_POST as $value) {
                        if ($k<(count($_POST)-1)) {
                            $array[] =  $value;
                            $k+=1;
                        }
                    }
                    $outImport = "python import.py " . escapeshellarg(json_encode($array));
                    $outputImport = shell_exec($outImport);
                    print_r($outputImport);
                    //print_r(json_decode($outputImport));
                }
            ?>
        </textarea>
    </div>

    <script>
        var k = 0;
        function addField() {
            let elem = document.getElementById('add_field_area');
            let newFields = document.getElementById('new_fields');

            let clone = elem.cloneNode(true);

            k++;
            clone.id = clone.id + k;
            clone.children[0].children[0].children[1].id = clone.children[0].children[0].children[1].id + k;
            clone.children[0].children[0].children[1].name = clone.children[0].children[0].children[1].name + k;
            clone.children[0].children[0].children[1].value = "";

            clone.children[0].children[1].children[1].id = clone.children[0].children[1].children[1].id + k;
            clone.children[0].children[1].children[1].name = clone.children[0].children[1].children[1].name + k;
            clone.children[0].children[1].children[1].value = "";

            clone.children[0].children[3].classList.remove('hide');

            newFields.appendChild(clone);

            let cellsimport = document.getElementById('cellsimport');
            let error = clone.children[1];
            clone.children[0].children[1].children[1].onblur = function() {
                var numEl = cellsimport.value.match(/\d+/g),
                numI = this.value.match(/\d+/g),
                kol = 0,
                flag=0;

                if ( numEl.length == 2 ) {
                    kol = parseInt(numEl[1]) - parseInt(numEl[0]);
                    if (!((numI != null) && ( numI.length == 2 ) && (parseInt(numI[1]) - parseInt(numI[0]) == kol)) ) {
                        flag++;
                    }
                } else {
                    flag++;
                }

                if (flag!=0) {
                    this.classList.add('invalid');
                    error.innerHTML = 'Количество ячеек данного поля не совпадает с количеством ячеек поля URL'
                }
            };
            clone.children[0].children[1].children[1].onfocus = function() {
                if (this.classList.contains('invalid')) {
                    this.classList.remove('invalid');
                    error.innerHTML = "";
                }
            };

            clone.children[0].children[0].children[1].onblur = function() {
                if ((this.value=="")||(this.value==null)) {
                    this.classList.add('invalid');
                    error.innerHTML = 'Не заполнено название поля'
                }
            };
            clone.children[0].children[0].children[1].onfocus = function() {
                if (this.classList.contains('invalid')) {
                    this.classList.remove('invalid');
                    error.innerHTML = "";
                }
            };
            return false;
        }

        function deleteField(a) {
            var contDiv = a.parentNode.parentNode;
            contDiv.parentNode.removeChild(contDiv);
            k--;
            return false;
        }
    </script>
</body>
</html>