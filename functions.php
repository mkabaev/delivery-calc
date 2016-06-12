<?php

// 0 - production
// 1 - devPlaceSGS
// 2 - devPlaceHome
$MODE = 2;

switch ($MODE) {
    case 0:
        $CURLOPT_PROXY = '';
        $CURLOPT_PROXYUSERPWD = '';
        $MYSQL_SERVER = 'vimax.mysql';
        $MYSQL_USER = 'vimax_test';
        $MYSQL_PASSWORD = 't_B6gKYj';
        $MYSQL_DB = 'vimax_test';
        break;
    case 1:
        $CURLOPT_PROXY = '10.254.30.3:8080';
        $CURLOPT_PROXYUSERPWD = 'eame\maxim_kabaev:RJHJKMfhneh18';
        $MYSQL_SERVER = 'localhost';
        $MYSQL_USER = 'root';
        $MYSQL_PASSWORD = '';
        $MYSQL_DB = 'dbcalc';
        break;
    case 2:
        $CURLOPT_PROXY = '';
        $CURLOPT_PROXYUSERPWD = '';
        $MYSQL_SERVER = 'localhost';
        $MYSQL_USER = 'root';
        $MYSQL_PASSWORD = '';
        $MYSQL_DB = 'dbvarm';
        break;
    default:
        $CURLOPT_TIMEOUT = 5;
        $CURLOPT_PROXY = '';
        $CURLOPT_PROXYUSERPWD = '';
        $MYSQL_SERVER = '';
        $MYSQL_USER = '';
        $MYSQL_PASSWORD = '';
        $MYSQL_DB = '';
        break;
}

function curl_get_contents($options) {
    $ch = curl_init();
    curl_setopt_array($ch, $options);
    $data = curl_exec($ch);
    // ???curl_multi_getcontent($ch)
    //var_dump(curl_getinfo($ch));
    curl_close($ch);
    return $data;
}

function GetResponse_get($url_request) {
    global $CURLOPT_TIMEOUT;
    global $CURLOPT_PROXY;
    global $CURLOPT_PROXYUSERPWD;
    $curl_options = [CURLOPT_URL => $url_request,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_PROXY => $CURLOPT_PROXY,
        CURLOPT_PROXYUSERPWD => $CURLOPT_PROXYUSERPWD,
        CURLOPT_TIMEOUT => $CURLOPT_TIMEOUT
    ];
    return curl_get_contents($curl_options);
}

function get_web_page($url) {
    $options = array(
        CURLOPT_RETURNTRANSFER => true,   // return web page
        CURLOPT_HEADER         => false,  // don't return headers
        CURLOPT_FOLLOWLOCATION => true,   // follow redirects
        CURLOPT_MAXREDIRS      => 10,     // stop after 10 redirects
        CURLOPT_ENCODING       => "",     // handle compressed
        CURLOPT_USERAGENT      => "test", // name of client
        CURLOPT_AUTOREFERER    => true,   // set referrer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
        CURLOPT_TIMEOUT        => 120,    // time-out on response
    ); 

    $ch = curl_init($url);
    curl_setopt_array($ch, $options);

    $content  = curl_exec($ch);

    curl_close($ch);

    return $content;
}

function GetResponse_post($url_request, $request_data) {
    global $CURLOPT_TIMEOUT;
    global $CURLOPT_PROXY;
    global $CURLOPT_PROXYUSERPWD;
    $curl_options = [CURLOPT_URL => $url_request,
        CURLOPT_RETURNTRANSFER => 1,
        //CURLOPT_HEADER => 0,
        CURLOPT_PROXY => $CURLOPT_PROXY,
        CURLOPT_PROXYUSERPWD => $CURLOPT_PROXYUSERPWD,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        //CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => $request_data,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_SSL_VERIFYHOST => 0,
        //CURLOPT_HTTPHEADER => array('Expect:')
        CURLOPT_TIMEOUT => $CURLOPT_TIMEOUT
    ];
    return curl_get_contents($curl_options);
}

function GetValueFromDB($tableName, $valueName, $searchString, $searchParamName = 'name') {
    global $MYSQL_SERVER;
    global $MYSQL_USER;
    global $MYSQL_PASSWORD;
    global $MYSQL_DB;
    $mysqli = new mysqli($MYSQL_SERVER, $MYSQL_USER, $MYSQL_PASSWORD, $MYSQL_DB);
    if ($mysqli->connect_error) {
        die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
    }
    /* Select запросы возвращают результирующий набор */
    mysqli_query($mysqli, "SET NAMES utf8");
//if ($result = $mysqli->query("SELECT searchString, name FROM cls_cities where searchString like 'Сама%' and code like '%00000000000000000' limit 100")) {
    $q = "SELECT " . $valueName . " FROM " . $tableName . " WHERE " . $searchParamName . " LIKE '" . $searchString . "%'";
    if ($result = $mysqli->query($q)) {
        //printf("Select вернул %d строк.\n", $result->num_rows);
        //$data=  mysqli_fetch_assoc($result);
        $data = mysqli_fetch_assoc($result); // all($result, MYSQLI_ASSOC);
        //echo json_encode($data, JSON_UNESCAPED_UNICODE);
        /* очищаем результирующий набор */
        //mysqli_free_result($result);
        $result->close();
    }

// Если нужно извлечь большой объем данных, используем MYSQLI_USE_RESULT */
//if ($result = $mysqli->query("SELECT * FROM City", MYSQLI_USE_RESULT)) {
//
//    /* Важно заметить, что мы не можем вызывать функции, которые взаимодействуют
//       с сервером, пока не закроем результирующий набор. Все подобные вызовы
//       будут вызывать ошибку 'out of sync' */
//    if (!$mysqli->query("SET @a:='this will not work'")) {
//        printf("Ошибка: %s\n", $mysqli->error);
//    }
//    $result->close();
//}
    $mysqli->close();
    if (empty($data)) {
        return;
    } else {
        return $data[$valueName];
    }
}

function PrepareReponseArray($responseStatus, $cost, $minDays, $maxDays, $pickupCost, $deliveryCost, $additionalInfo) {
//    $arr_auto = [
//        "cost" => $cost_at,
//        "minDays" => $minDays_at,
//        "maxDays" => $maxDays_at,
//    ];

    $result = ["status" => $responseStatus];
    if ($cost > 0) {
        $result['cost'] = $cost;
    }

    if ($minDays > 0) {
        $result['minDays'] = $minDays;
    }
    if ($maxDays > 0) {
        $result['maxDays'] = $maxDays;
    }
//    if ($cost_av > 0) {
//        $result['avia'] = $arr_avia;
//    }

    if ($pickupCost > 0) {
        $result['pickupCost'] = $pickupCost;
    }
    if ($deliveryCost > 0) {
        $result['deliveryCost'] = $deliveryCost;
    }

    $result['additionalInfo'] = $additionalInfo;
    return $result;
}
