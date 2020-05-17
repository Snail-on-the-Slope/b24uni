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
            <input type="submit" name="SubmitParsing" value="Парсинг и категоризация данных в таблице">
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
            <input type="submit" name="SubmitImport" value="Импортировать данные из Google Sheets">
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
                    echo "подключено к базе данных...  \n";
                    
                    if ($k==5) {
                        echo count($data_table[0])," компаний найдено. ";
                        $inport_data_table_to_js = '["' . implode('", "', $data_table[0]) . '"]';
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
                }
            ?>
        </textarea>
    </div>

    <script>
        // function add_company_b24 (field) {
        //     // BX24.init(function(){
        //             BX24.callMethod( "crm.company.add", 
        //                 {
        //                     fields: field,
        //                     params: { "REGISTER_SONET_EVENT": "Y" }		
        //                 }, 
        //                 function(result) 
        //                 {
        //                     if(result.error())
        //                         console.error(result.error());
        //                     else {
        //                         var textarea = document.getElementById('import-area');
        //                         textarea.innerHTML += "Создана компания с ID " + result.data() + "\n";
        //                     }
        //                 }
        //             );
        //         // });
        // }

        // function get_list_company_b24 (field) {
        //     BX24.init(function(){
        //             BX24.callMethod( "crm.company.add", 
        //                 {
        //                     fields: field,
        //                     params: { "REGISTER_SONET_EVENT": "Y" }		
        //                 }, 
        //                 function(result) 
        //                 {
        //                     if(result.error())
        //                         console.error(result.error());
        //                     else {
        //                         var textarea = document.getElementById('import-area');
        //                         textarea.innerHTML += "Создана компания с ID " + result.data();
        //                     }
        //                 }
        //             );
        //         });
        // }

        // async function get_type_field_b24(name_field) {
        //     var print_result = '';
        //     alert('in get_type_field_b24 ' + name_field);
        //     await BX24.init(function(){
        //         alert('in get_type_field_b24 || BX24');
        //             BX24.callMethod(
        //                 "crm.company.fields", 
        //                 {}, 
        //                 function(result) 
        //                 {
        //                     if(result.error())
        //                         alert(result.error());
        //                     else {
        //                         var obj = result.data();
        //                         print_result = obj[name_field]['type'];
        //                     }
        //                 }
        //             );
        //         });
        //     return print_result;
        // }

        // async function creation_of_companies_from_the_list_received(array, name_fields) {
        //     var add_data_fields = {};
        //     for (i=0; i < array.length; i++) {
        //         add_data_fields = {};
        //         for (j = 0; j < name_fields.length; j++) {
        //             if (array[i][j]='')
        //                 array[i][j] = '-1'

        //             // var type_value = await get_type_field_b24(name_fields[j]);

        //             // if (type_value == "integer") 
        //             //     add_data_fields[name_fields[j]] = parseInt(array[i][j]);
        //             // if (type_value == "double") 
        //             //     add_data_fields[name_fields[j]] = parseFloat(array[i][j]);
        //             // if (type_value == "string" || type_value == "char")
        //                 add_data_fields[name_fields[j]] = array[i][j];
                            
        //             alert(type_value + ' - ' + JSON.stringify(add_data_fields));
        //         }
        //         alert(JSON.stringify(add_data_fields));
        //         // add_company_b24(add_data_fields);
        //     }
        // }
 
        // ----------------------- заполнение select -----------------------
        BX24.init(function(){
                BX24.callMethod(
                	"crm.company.fields", 
                	{}, 
                	function(result) 
                	{
                		if(result.error())
                			alert(result.error());
                		else {
                            var selectList = document.getElementById('field_selection');
                            var obj = result.data();
                            
                            for (var i in obj){
                                if (obj[i]['isReadOnly']==false && (obj[i]['type']=='string' || obj[i]['type']=='integer' || obj[i]['type']=='double' || obj[i]['type']=='char')) {
                                    if (obj[i]['title'].indexOf('UF_CRM_') == -1) 
                                        temp = [i, obj[i]['title']]; 
                                    else
                                        temp = [i, obj[i]['listLabel']]; 

                                    var option = document.createElement("option");
                                    option.value = temp[0];
                                    option.text = temp[1];
                                    selectList.appendChild(option);
                                } 
                            }
                            var option = document.createElement("option");
                            option.value = 'CUSTOM_FIELD';
                            option.text = 'Пользовательское поле';
                            selectList.appendChild(option);
                		}
                	}
                );
            // ----------------------- END -----------------------
        });

        // ----------------------- работа с динамичной обработкой и созданием ячеек в .import-data -----------------------
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
                    }
                } else {
                    flag++;
                }

                if (flag != 0) {
                    this.classList.add('invalid');
                    error.innerHTML = 'Количество ячеек данного поля не совпадает с количеством ячеек поля URL'
                }
            };
            item3.onfocus = function () {
                if (this.classList.contains('invalid')) {
                    this.classList.remove('invalid');
                    error.innerHTML = "";
                }
            };

            item2.onblur = function () {
                if ((!this.classList.contains('hide')) && ((this.value == "") || (this.value == null))) {
                    this.classList.add('invalid');
                    error.innerHTML = 'Не заполнено название поля'
                }
                if ((!this.classList.contains('hide')) && ((this.value.length > 13))) {
                    this.classList.add('invalid');
                    error.innerHTML = 'Слишком длинное название'
                }
            };
            item2.onfocus = function () {
                if (this.classList.contains('invalid')) {
                    this.classList.remove('invalid');
                    error.innerHTML = "";
                }
            };

            item1.onblur = function () {
                if (this.selectedIndex == -1) {
                    this.classList.add('invalid');
                    error.innerHTML = 'Не выбрано название поля'
                }
            };
            item1.onfocus = function () {
                if (this.classList.contains('invalid')) {
                    this.classList.remove('invalid');
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

        var field = document.getElementById("add_field_area");
        var select = document.getElementById("field_selection");
        var div = document.getElementById("add_name_custom_field");

        onblur_onfocus(select, div, document.getElementById("cellsimport"), field.querySelector('.error'));
        select.addEventListener("click", function () { inputAddNameField(select, div) });

        var error =  field.querySelector('.error');
        document.getElementById("cellsimport").onblur = function () {
            if ((this.value == "") || (this.value == null)) {
                this.classList.add('invalid');
                error.innerHTML = 'Не заполнено поле ячеек'
            }
        };
        document.getElementById("cellsimport").onfocus = function () {
            if (this.classList.contains('invalid')) {
                this.classList.remove('invalid');
                error.innerHTML = "";
            }
        };

        div.onblur = function () {
            if ((!this.classList.contains('hide')) && ((this.value == "") || (this.value == null))) {
                this.classList.add('invalid');
                error.innerHTML = 'Не заполнено название поля'
            }
        };
        div.onfocus = function () {
            if (this.classList.contains('invalid')) {
                this.classList.remove('invalid');
                error.innerHTML = "";
            }
        };

        select.onblur = function () {
            if (this.selectedIndex == -1) {
                this.classList.add('invalid');
                error.innerHTML = 'Не выбрано название поля'
            }
        };
        select.onfocus = function () {
            if (this.classList.contains('invalid')) {
                this.classList.remove('invalid');
                error.innerHTML = "";
            }
        };
        // ----------------------- END -----------------------


        // ----------------------- после отправки формы .import-data -----------------------
        var permission = '<?php echo $permission_to_connect_to_bitrix;?>';
            if (permission == 1) {
                var textarea = document.getElementById('import-area');
                // получение и обработка списка значений из таблицы
                var obj = '<?php echo $inport_data_table_to_js;?>';
                var array = [];
                var k_import = '<?php echo $k;?>';

                if (k_import == 5) {
                    array = obj.substr(2, obj.length - 2).split('", "');
                } else {
                    var count_company = '<?php echo $count_company;?>';
                    var count_item = '<?php echo $count_item;?>';
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
                // creation_of_companies_from_the_list_received(array, name_fields);
                var add_data_fields = {};
                alert(JSON.stringify(array));
                for (i=0; i < array.length; i++) {
                    alert('i: ' + JSON.stringify(array[i]));
                    for (j = 0; j < array[i].length; j++) {
                        alert('j: ' + JSON.stringify(array[i][j]) + ' ' + name_field[j]);
                    }
                    // add_data_fields = {};
                    // for (j = 0; j < name_fields.length; j++) {
                    //     // if (array[i][j]='')
                    //     //     array[i][j] = '-1'

                    //     // var type_value = get_type_field_b24(name_fields[j]);

                    //     // if (type_value == "integer") 
                    //     //     add_data_fields[name_fields[j]] = parseInt(array[i][j]);
                    //     // if (type_value == "double") 
                    //     //     add_data_fields[name_fields[j]] = parseFloat(array[i][j]);
                    //     // if (type_value == "string" || type_value == "char")
                    //         add_data_fields[name_fields[j]] = array[i][j];
                            
                    //     alert(name_fields[j] + ' - ' + JSON.stringify(array)  + ' - ' + JSON.stringify(array[i]) + ' - ' + array[i][j] + ' - ' + JSON.stringify(add_data_fields));
                    // }
                    // alert(JSON.stringify(add_data_fields));
                    // add_company_b24(add_data_fields);
                }
                // alert(JSON.stringify(add_data_fields));
                // add_company_b24(add_data_fields);

                <?php $permission_to_connect_to_bitrix = 0;?>
            }
        
        // ----------------------- END -----------------------
    </script>
</body>
</html>

