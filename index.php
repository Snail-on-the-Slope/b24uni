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
                    // $out = "python parsing.py " . $idTable . " " . $idSheet . " " . $cells;
                    // $output = shell_exec($out);
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
                    //$outImport = "python import.py " . escapeshellarg(json_encode($array));
                    //$outputImport = shell_exec($outImport);
                    //$data_table = json_decode($outputImport);
					//echo count($data_table[0])," компаний найдено. ";
					echo "подключено к базе данных...  ";
					$permission_to_connect_to_bitrix = 1;

                    // define('APP_ID', 'local.5ebe63d7585bb6.31756347');
                    // define('APP_SECRET_CODE', 'ievod89YV39EqGlJPqGYBbW6wC98Z0ZoBF4Ji3NZkiCEAz7NaO');
                    // define('APP_REG_URL', 'https://b24uni.herokuapp.com/'); 

                    // print_r($_REQUEST);
                    // echo ' - ';
                    // requestCode($_REQUEST['DOMAIN']);
                    // $queryUrl = 'https://'.$_REQUEST['DOMAIN'].'/rest/user.current.json';
                    // $queryData = http_build_query(array( "auth" => $_REQUEST['AUTH_ID'] ));

                    // $curl = curl_init();
                    // curl_setopt_array($curl, array(
                    //     CURLOPT_SSL_VERIFYPEER => 0,
                    //     CURLOPT_POST => 1,
                    //     CURLOPT_HEADER => 0,
                    //     CURLOPT_RETURNTRANSFER => 1,
                    //     CURLOPT_URL => $queryUrl,
                    //     CURLOPT_POSTFIELDS => $queryData,
                    // ));

                    // $result = json_decode(curl_exec($curl), true);
                    // curl_close($curl);
                }
            ?>
        </textarea>
    </div>

    <script>
		var permission = '<?php echo $permission_to_connect_to_bitrix;?>';
		consol.log(permission);


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

<?php
function executeHTTPRequest ($queryUrl, array $params = array()) {
    $result = array();
    $queryData = http_build_query($params);

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $queryUrl,
        CURLOPT_POSTFIELDS => $queryData,
    ));

    $curlResult = curl_exec($curl);
    curl_close($curl);

    if ($curlResult != '') $result = json_decode($curlResult, true);

    return $result;
}

function redirect($url) {
    header("HTTP 302 Found");
    header("Location: ".$url);
    die();
}

function requestCode ($domain) {
    // https://b24-19xsto.bitrix24.ru/oauth/authorize/?client_id=local.5ebe63d7585bb6.31756347&response_type=code&redirect_uri=https://b24uni.herokuapp.com/
    //$url = 'https://' . $domain . '/oauth/authorize/?client_id=' . urlencode(APP_ID) . '&response_type=code&redirect_uri=' . urlencode(APP_REG_URL);
    //redirect($url);
    $result = file_get_contents('https://' . $domain . '/oauth/authorize/?client_id=' . urlencode(APP_ID));
    print_r($result);
    echo ' || ';

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => 'https://' . $domain . '/oauth/authorize/?client_id=' . urlencode(APP_ID),
    ));

    $curlResult = curl_exec($curl);
    curl_close($curl);

    print_r(json_decode($curlResult, true));
    echo ' || ';

    print_r($_REQUEST);
}

function requestAccessToken ($code, $server_domain) {
    // 'https://b24-19xsto.bitrix24.ru/oauth/token/?grant_type=authorization_code&client_id=local.5ebe63d7585bb6.31756347&client_secret=ievod89YV39EqGlJPqGYBbW6wC98Z0ZoBF4Ji3NZkiCEAz7NaO&code=
    $url = 'https://' . $server_domain . '/oauth/token/?' .
        'grant_type=authorization_code'.
        '&client_id='.urlencode(APP_ID).
        '&client_secret='.urlencode(APP_SECRET_CODE).
        '&code='.urlencode($code);
    return executeHTTPRequest($url);
}

function executeREST ($rest_url, $method, $params, $access_token) {
    $url = $rest_url.$method.'.json';
    return executeHTTPRequest($url, array_merge($params, array("auth" => $access_token)));
}
?>