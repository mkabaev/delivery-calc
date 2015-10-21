<?php

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
    $curl_options = [CURLOPT_URL => $url_request,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_PROXY => '10.254.30.3:8080',
        CURLOPT_PROXYUSERPWD => 'eame\maxim_kabaev:RJHJKMfhneh18',
        CURLOPT_TIMEOUT => 3
    ];
    return curl_get_contents($curl_options);
}

function GetResponse_post($url_request, $ar_request) {
    $curl_options = [CURLOPT_URL => $url_request,
        CURLOPT_RETURNTRANSFER => 1,
        //CURLOPT_HEADER => 0,
        CURLOPT_PROXY => '10.254.30.3:8080',
        CURLOPT_PROXYUSERPWD => 'eame\maxim_kabaev:RJHJKMfhneh18',
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        //CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => json_encode($ar_request),
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_SSL_VERIFYHOST => 0,
        //CURLOPT_HTTPHEADER => array('Expect:')
        CURLOPT_TIMEOUT => 3
    ];
    return curl_get_contents($curl_options);
}

function GetValueFromDB($tableName, $valueName, $searchString, $searchParamName = 'name') {
    $mysqli = new mysqli('localhost', 'root', '', 'dbcalc');
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
