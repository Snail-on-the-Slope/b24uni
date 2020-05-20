<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@300&display=swap" rel="stylesheet">
	<script src="https://api.bitrix24.com/api/v1/"></script>
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
            <input type="submit" id="SubmitParsing" name="SubmitParsing" value="Парсинг и категоризация данных в таблице">
            <p>процесс займет некоторое время...</p>
        </form>
        <h1>  
        <?php
			$permission_to_connect_to_bitrix = 0;

            if(isset($_POST['SubmitParsing'])){
                $idTable = $_POST['idTable'];
                $idSheet = $_POST['idSheet'];
                $cells = $_POST['cells'];
                if ($idTable != null && $idSheet != null && $cells != null) {
                    $out = "python parsing.py " . $idTable . " " . $idSheet . " " . $cells;
                    $output = shell_exec($out);
                    if ($output == "OK") {
                        $time  = date("H:i:s", mktime(date("H")+3, date("i")+3, date("s")+3, 0, 0, 0));
                        echo "Парсинг завершен ", $time;
                    } else {
                        echo "Ошибка во время парсинга   ";
                        print_r($output);
                    }
                } else {
                    echo "Ошибка отправки данных. Есть незаполненные поля";
                }
            }
        ?>
        </h1>
    </div>

    <div class="import-data">
        <form action="#" method="post">
            <div class="obligatory">
                <label for="idTableImport">ID таблицы</label>
                <input type="text" id="idTableImport" name="idTableImport"
                    value="1Q5bw0D9y3_WfyhFhimr7_GgJZxxUNSMltOEW7WAFsuo">
                <label for="idSheetImport">ID листа</label>
                <input type="text" id="idSheetImport" name="idSheetImport" value="332621208">
            </div>
            <p>Создание / Обновление карточки компании с полями и значениями:</p>
            <div class="optional" id="add_field_area">
                <div class="all-field">
                    <div class="field-optional">
                        <label for="fieldimport">Поле</label>
                        <select id="field_selection" name="field_selection">
                        </select>
                    </div>
                    <div class="cells-optional">
                        <label for="cellsimport">Ячейки</label>
                        <input type="text" id="cellsimport" name="cellsimport" value="A4:A181">
                    </div>
                    <button onclick="return addField();" class="plus-button">+</button>
                    <button onclick="return deleteField(this);" class="plus-button hide">-</button>
                </div>
                <div id="add_name_custom_field" class="add_name_field hide">
                    <input type="text" id="name_custom_field" name="name_custom_field" placeholder="Название поля">
                </div>
                <div class="error"></div>
            </div>
            <div id="new_fields"></div>
            <input type="submit" onclick="return verification_import();" id="SubmitImport" name="SubmitImport" value="Импортировать данные из Google Sheets">
        </form>
        <textarea name="import-area" id="import-area" cols="30" rows="10" disabled>
            <?php
                if(isset($_POST['SubmitImport'])){
                    $array = [];
                    $name_fields = '';
                    $k = 0;
                    $k_items = 0;
                    $flag_k = false;
                    foreach($_POST as $value) {
                        if ($k < (count($_POST)-1)) {
                            if ($k < 2) {
                                $array[] =  $value;
                            } else {
                                if ($k_items > 2) {
                                    $k_items = 0;
                                }

                                if ($k_items == 0) {
                                    if ($value == 'CUSTOM_FIELD'){
                                        $flag_k = true;
                                    } else {
                                        $name_fields = $name_fields . $value . ' ';
                                    }
                                } elseif ($k_items == 1) {
                                    $array[] =  $value;
                                } else {
                                    if ($flag_k) {
                                        $name_fields = $name_fields . $value . ' ';
                                        $flag_k = false;
                                    }
                                }
                                $k_items += 1;
                            }
                            
                            $k+=1;
                        }
                    }
                    
                    $outImport = "python import.py " . escapeshellarg(json_encode($array));
                    $outputImport = shell_exec($outImport);
                    $data_table = json_decode($outputImport);

                    if (strripos($outputImport, 'DATA IMPORT ERROR!') == false) {
                        echo "Подключено к базе данных...  \n";
                        if ($k==5) {
                            echo count($data_table[0])," компаний найдено. ";
                            $inport_data_table_to_js = '["' . implode('", "', $data_table[0]) . '"]';
                            $count_item = 1;
                        } else {
                            $count_company = count($data_table);
                            $count_item = count($data_table[0]);
                            echo $count_company," компаний найдено. ";
                            $inport_data_table_to_js = '';
                            foreach ($data_table as $value) {
                                foreach ($value as $item) {
                                    $inport_data_table_to_js = $inport_data_table_to_js . $item . ', ';
                                }
                            }
                        }
                        $permission_to_connect_to_bitrix = 1;
                    } else {
                        echo "Не удалось подключиться к базе данных.  ", $outputImport;
                    }
                }
            ?>
        </textarea>
    </div>


    <script> 
        // добавление компании (название и поля)
        function add_company_b24 (title_, field) {
            BX24.init(function(){
                    BX24.callMethod( "crm.company.add", 
                        {
                            fields: field,
                            params: { "REGISTER_SONET_EVENT": "Y" }		
                        }, 
                        function(result) 
                        {
                            if(result.error())
                                console.error(result.error());
                            else {
                                var textarea = document.getElementById('import-area');
                                textarea.innerHTML += "\n Создана компания " + title_ + " с ID " + result.data();
                            }
                        }
                    );
            });
        }

        // добавление или обновление компании (название и поля)
        function add_or_update_company_b24(title_, field) {
            BX24.init( function() {
                let company_id = '';
                function load_b24_method(callback) {
                    BX24.callMethod( "crm.company.list", 
                        { 
                            order: { "DATE_CREATE": "ASC" },
                            filter: { "TITLE": title_ },
                            select: [ "ID" ]				
                        }, 
                        function(result) 
                        {
                            if (result.error())
                                alert('ERROR' + result.error());
                            else {
                                if (result.data()[0] != undefined)
                                    company_id = result.data()[0]['ID'];
                                callback(true);
                            }
                        }
                    );
                }

                load_b24_method(value => {
                    if (company_id == '') {
                        BX24.callMethod( "crm.company.add", 
                            {
                                fields: field,
                                params: { "REGISTER_SONET_EVENT": "Y" }		
                            }, 
                            function(result) 
                            {
                                if(result.error())
                                    alert(result.error());
                                else {
                                    var textarea = document.getElementById('import-area');
                                    textarea.innerHTML += "\n Создана компания " + title_ + " с ID " + result.data();
                                }
                            }
                        );
                    } else {
                        BX24.callMethod( "crm.company.update", 
                            { 
                                id: company_id,
                                fields: field,
                                params: { "REGISTER_SONET_EVENT": "Y" }				
                            }, 
                            function(result) 
                            {
                                if(result.error())
                                    alert(result.error());
                                else {
                                    var textarea = document.getElementById('import-area');
                                    textarea.innerHTML += "\n Данные компания " + title_ + " обновлены ";						
                                }
                            }
                        );
                    }
                });
            }); 
        }
 
        // ----------------------- заполнение select -----------------------
        function custon_field(selectList) {
            var option = document.createElement("option");
            option.value = 'CUSTOM_FIELD';
            option.text = 'Пользовательское поле';
            selectList.appendChild(option);
        }

        BX24.init(function(){
            var selectList = document.getElementById('field_selection');
            if (localStorage.getItem("option.value") == null) {
                BX24.callMethod(
                	"crm.company.fields", 
                	{}, 
                	function(result) 
                	{
                		if(result.error())
                			alert(result.error());
                		else {
                            var obj = result.data();
                            var str_option = '';
                            
                            for (var i in obj){
                                if (obj[i]['isReadOnly']==false && (obj[i]['type']=='string' || obj[i]['type']=='integer' || obj[i]['type']=='double' || obj[i]['type']=='char')) {
                                    if (obj[i]['title'].indexOf('UF_CRM_') == -1) 
                                        temp = [i, obj[i]['title'], obj[i]['type']]; 
                                    else
                                        temp = [i, obj[i]['listLabel'], obj[i]['type']]; 

                                    var option = document.createElement("option");
                                    option.value = temp[0];
                                    option.text = temp[1];
                                    selectList.appendChild(option);
                                    localStorage.setItem(option.value, temp[2]);
                                    str_option += temp[0] + ' ' + temp[1] + ' ';
                                } 
                            }
                            localStorage.setItem("option.value", str_option.substr(0, str_option.length - 1));
                            custon_field(selectList);
                		}
                	}
                );
            } else {
                var array_oprion = localStorage.getItem("option.value").split('", "');
                for (i = 0; i < array_oprion.length; i+=2) {
                    var option = document.createElement("option");
                    option.value = array_oprion[i];
                    option.text = array_oprion[i+1];
                    selectList.appendChild(option);
                }
                custon_field(selectList);
            }
        });
        alert(localStorage.getItem("option.value"));
        // ----------------------- END -----------------------


        // ----------------------- работа с динамичной обработкой и созданием ячеек в .import-data -----------------------
        var k = 0;
        var button_import = document.getElementById('SubmitImport');

        var field = document.getElementById("add_field_area");
        var select = document.getElementById("field_selection");
        var div = document.getElementById("add_name_custom_field");

        function addField() {
            let newFields = document.getElementById('new_fields');
            let clone = field.cloneNode(true);

            k++;
            clone.id = clone.id + k;
            clone.children[0].children[0].children[1].id = clone.children[0].children[0].children[1].id + k;
            clone.children[0].children[0].children[1].name = clone.children[0].children[0].children[1].name + k;
            clone.children[0].children[0].children[1].value = "TITLE";
            if (clone.children[0].children[0].children[1].classList.contains('invalid')) {
                clone.children[0].children[0].children[1].classList.remove('invalid');
            }

            clone.children[0].children[1].children[1].id = clone.children[0].children[1].children[1].id + k;
            clone.children[0].children[1].children[1].name = clone.children[0].children[1].children[1].name + k;
            clone.children[0].children[1].children[1].value = "";
            if (clone.children[0].children[1].children[1].classList.contains('invalid')) {
                clone.children[0].children[1].children[1].classList.remove('invalid');
            }

            clone.children[0].children[3].classList.remove('hide');

            clone.children[1].id = clone.children[1].id + k;
            if (!clone.children[1].classList.contains('add_name_field')) {
                clone.children[1].classList.add('add_name_field');
            }
            if (!clone.children[1].classList.contains('hide')) {
                clone.children[1].classList.add('hide');
            }
            clone.children[1].children[0].id = clone.children[1].children[0].id + k;
            clone.children[1].children[0].name = clone.children[1].children[0].name + k;
            clone.children[1].children[0].value = "";

            clone.children[2].innerHTML = '';

            newFields.appendChild(clone);

            onblur_onfocus(clone.children[0].children[0].children[1], clone.children[1].children[0], clone.children[0].children[1].children[1], clone.children[2]);
            var select_item = clone.children[0].children[0].children[1];
            var div = clone.children[1];
            select_item.addEventListener("click", function () { inputAddNameField(select_item, div) });
            return false;
        }

        function onblur_onfocus(item1, item2, item3, error) {
            let cellsimport = document.getElementById('cellsimport');
            item3.onblur = function () {
                var numEl = cellsimport.value.match(/\d+/g),
                    numI = this.value.match(/\d+/g),
                    kol = 0,
                    flag = 0;

                if (numEl!=null && numEl.length == 2) {
                    kol = parseInt(numEl[1]) - parseInt(numEl[0]);
                    if (!((numI != null) && (numI.length == 2) && (parseInt(numI[1]) - parseInt(numI[0]) == kol))) {
                        flag++;
                        error.innerHTML = 'Количество ячеек данного поля не совпадает с количеством ячеек первого поля'
                    }
                } else {
                    flag++;
                    error.innerHTML = 'Не верно заполненно первое поле'
                }

                if (flag != 0) {
                    this.classList.add('invalid');
                    button_import.disabled = true;
                }
            };
            item3.onfocus = function () {
                if (this.classList.contains('invalid')) {
                    this.classList.remove('invalid');
                    button_import.disabled = false;
                    error.innerHTML = "";
                }
            };

            item2.onblur = function () {
                if ((!this.classList.contains('hide')) && ((this.value == "") || (this.value == null))) {
                    this.classList.add('invalid');
                    button_import.disabled = true;
                    error.innerHTML = 'Не заполнено название поля'
                }
                if ((!this.classList.contains('hide')) && ((this.value.length > 13))) {
                    this.classList.add('invalid');
                    button_import.disabled = true;
                    error.innerHTML = 'Слишком длинное название'
                }
            };
            item2.onfocus = function () {
                if (this.classList.contains('invalid')) {
                    this.classList.remove('invalid');
                    button_import.disabled = false;
                    error.innerHTML = "";
                }
            };

            item1.onblur = function () {
                if (this.selectedIndex == -1) {
                    this.classList.add('invalid');
                    button_import.disabled = true;
                    error.innerHTML = 'Не выбрано название поля'
                }
            };
            item1.onfocus = function () {
                if (this.classList.contains('invalid')) {
                    this.classList.remove('invalid');
                    button_import.disabled = false;
                    error.innerHTML = "";
                }
            };
        }

        function deleteField(a) {
            var contDiv = a.parentNode.parentNode;
            contDiv.parentNode.removeChild(contDiv);
            k--;
            return false;
        }
        
        function inputAddNameField(select, div) {
            if (select.value == "CUSTOM_FIELD") {
                if (div.classList.contains('hide')) {
                    div.classList.remove('hide');
                }
            } else {
                if (!div.classList.contains('hide')) {
                    div.classList.add('hide');
                }
            }
        }

        onblur_onfocus(select, div, document.getElementById("cellsimport"), field.querySelector('.error'));
        select.addEventListener("click", function () { inputAddNameField(select, div) });

        var error =  field.querySelector('.error');

        function onblur_onfocus_div_import(id_element) {
            document.getElementById(id_element).onblur = function () {
                if ((this.value == "") || (this.value == null)) {
                    this.classList.add('invalid');
                    button_import.disabled = true;
                }
            };
            document.getElementById(id_element).onfocus = function () {
                if (this.classList.contains('invalid')) {
                    this.classList.remove('invalid');
                    button_import.disabled = false;
                }
            };
        }
        
        onblur_onfocus_div_import("idTableImport");
        onblur_onfocus_div_import("idSheetImport");

        // ----------------------- END -----------------------


        // ----------------------- проверка заполненности полей в .parsing-google-sheets -----------------------

        function onblur_onfocus_div_parsing(id_element) {
            let button_parsing = document.getElementById("SubmitParsing");
            document.getElementById(id_element).onblur = function () {
                if ((this.value == "") || (this.value == null)) {
                    this.classList.add('invalid');
                    button_parsing.disabled = true;
                }
            };
            document.getElementById(id_element).onfocus = function () {
                if (this.classList.contains('invalid')) {
                    this.classList.remove('invalid');
                    button_parsing.disabled = false;
                }
            };
        }

        onblur_onfocus_div_parsing("idTable");
        onblur_onfocus_div_parsing("idSheet");
        onblur_onfocus_div_parsing("cells");

        // ----------------------- END -----------------------
        

        // ----------------------- проверка данных в формах перед отправкой -----------------------

        function verification_import() {
            // проверка, что ровно одно из полей - название компании
            // проверка заполненности всех полей
            let result = true;
            let quantity_name_field = 0;

            if (select.value == 'TITLE') 
                quantity_name_field++;
            if (select.selectedIndex == -1) 
                result = false; 
            if ((select.value == "") || (select.value == null)) 
                result = false; 

            if ((document.getElementById("idTableImport").value == "") || (document.getElementById("idTableImport").value == null)) 
                result = false; 

            if ((document.getElementById("idSheetImport").value == "") || (document.getElementById("idSheetImport").value == null)) 
                result = false; 

            for (i=0; i<k; i++) {
                let temp = document.getElementById("field_selection"+i);
                if (temp.value == 'TITLE') 
                    quantity_name_field++;
                if (temp.selectedIndex == -1) 
                    result = false; 
            }
            if (quantity_name_field != 1) 
                result = false;

            if (result) {
                return true;
            } else {
                alert('ERROR');
                return false;
            }
        }
        // ----------------------- END -----------------------

        // ----------------------- после отправки формы .import-data -----------------------
        // проверка совпадения типа поля и вводимого значения
        function get_type_field_(name_field, item) { 
            var result = item;
            var value_type = '';

            let keys = Object.keys(localStorage);
            for (let key of keys) {
                if (key == name_field) 
                    value_type = localStorage.getItem(key);
            }

            if (value_type == 'string' || value_type == 'char') 
                result = item;
            if (value_type == 'integer') {
                if (item = "") 
                    result = "-1";
                result = parseInt(result);
            }
            if (value_type == 'double') {
                if (item = "") 
                    result = "-1";
                result = parseFloat(result);
            }

            if ((typeof result != 'string') && isNaN(result)) {  
                alert("Неверный тип данных поля " + name_field + " значения " + item + " (требуется тип " + value_type + ")");
            }
            return result;
        }

        // если была нажата кнопка  "Импортировать данные из Google Sheets"
        var permission = '<?php echo $permission_to_connect_to_bitrix;?>';
            if (permission == 1) {
                var textarea = document.getElementById('import-area');
                // получение и обработка списка значений из таблицы
                var obj = '<?php echo $inport_data_table_to_js;?>';
                var array = [];
                var count_item = '<?php echo $count_item;?>';
                var k_import = '<?php echo $k;?>';

                if (k_import == 5) {
                    array = obj.substr(2, obj.length - 2).split('", "');
                } else {
                    var temp = obj.substr(0, obj.length - 2).split(', ');
                    var index_temp = 0;
                    var temp_temp = [];
                    for (i=0; i < temp.length; i++) {
                        if (index_temp < count_item) {
                            temp_temp.push(temp[i]);
                            index_temp++;
                        } else {
                            array.push(temp_temp);
                            temp_temp = [];
                            temp_temp.push(temp[i]);
                            index_temp = 1;
                        }
                    }
                    array.push(temp_temp);
                }
                var name_fields = '<?php echo $name_fields;?>'.split(' ');
                name_fields = name_fields.slice(0, name_fields.length-1);

                // создание компаний из полученного списка
                var add_data_fields = {};
                var title_company = '';
                for (i=0; i < array.length; i++) {
                    add_data_fields = {};
                    if (count_item == 1) {
                        add_data_fields[name_fields[0]] = get_type_field_(name_fields[0], array[i]); // = array[i]
                        if (name_fields[0] == 'TITLE')
                            title_company = add_data_fields[name_fields[0]];
                    } else {
                        for (j = 0; j < name_fields.length; j++) {
                            add_data_fields[name_fields[j]] =  get_type_field_(name_fields[j], array[i][j]); // = array[i][j];
                            if (name_fields[0] == 'TITLE')
                                title_company = add_data_fields[name_fields[0]];
                        }
                    }
                    
                    if (title_company == '') {
                        alert('ERROR Название компании - обязательное не пустое поле');
                        continue;
                    }
                    // add_company_b24(title_company, add_data_fields); // добавление
                    add_or_update_company_b24(title_company, add_data_fields);
                }

                <?php $permission_to_connect_to_bitrix = 0;?>
            }
        
        // ----------------------- END -----------------------
    </script>
</body>
</html>

