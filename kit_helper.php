<?php

require_once 'functions.php';

function KIT_Calculate($city_from, $city_to, $weight, $volume, $quantity) {
    $id_city_from = KIT_GetCityId($city_from);
    $id_city_to = KIT_GetCityId($city_to);
    //ИЗМЕНИТЬ RCODE и прочие
    //SZONE=0000006301&RZONE=0000007700&
    $url = 'http://tk-kit.ru/API.1/?f=price_order&I_DELIVER=1&I_PICK_UP=1&WEIGHT=' . $weight . '&VOLUME=' . $volume . '&SLAND=RU&SZONE=' . $id_city_from . '&SCODE=860001000000&SREGIO=&RLAND=RU&RZONE=' . $id_city_to . '&RCODE=890000700000&RREGIO=&KWMENG=1&LENGTH=&WIDTH=&HEIGHT=&GR_TYPE=&LIFNR=&PRICE=&WAERS=RUB';
    //echo 'request: ' . $url . '<br/>';
// response is {"PRICE":{"PICKUP":"350.0","TRANSFER":"300.0","DELIVERY":"0.0","TOTAL":"650.0","EXTRA":[{"price":"50.0","name":"\u0421\u0442\u0440\u0430\u0445\u043e\u0432\u0430\u043d\u0438\u0435"}]},"IS_OVER":"","DAYS":3.5,"E_WAERS":"RUB","E_RATE":{"AMD":"8.0","BYR":"300.0","KGS":"1.0","KZT":"5.0","UAH":"0.33333","RUB":1}}
// response is {"PRICE":{"PICKUP":"350.0","TRANSFER":"500.0","DELIVERY":"400.0","TOTAL":"1250.0"},"IS_OVER":"","DAYS":7,"E_WAERS":"RUB","E_RATE":{"AMD":"8.0","BYR":"300.0","KGS":"1.0","KZT":"5.0","UAH":"0.33333","RUB":1}}
    $json_response = GetResponse_get($url);
    //echo '<hr/>response is: ' . $json_response;
    $ar = json_decode($json_response, true);
    $responseStatus = '';
    $cost_at = 0;
    $minDays_at = 0;
    $maxDays_at = 0;
    $cost_av = 0;
    $minDays_av = 0;
    $maxDays_av = 0;
    $cost_rw = 0;
    $minDays_rw = 0;
    $maxDays_rw = 0;
    $pickupCost = 0;
    $deliveryCost = 0;
    $additionalInfo = '';

//    echo '<pre>';
//    print_r($ar);
//    echo '</pre>';
    if (array_key_exists("PRICE", $ar)) { // if KIT response is OK
        $responseStatus = 'ok';
        $cost_at = round($ar['PRICE']['TRANSFER']);
        $pickupCost = round($ar['PRICE']['PICKUP']);
        $deliveryCost = round($ar['PRICE']['DELIVERY']);
        $minDays_at=round($ar['DAYS']);
        $maxDays_at=round($ar['DAYS']);
    } else {
        $result["status"] = "err";
        $result["text"] = "KIT API error";
    }
    return PrepareReponseArray($responseStatus, $cost_at, $minDays_at, $maxDays_at, $cost_av, $minDays_av, $maxDays_av, $cost_rw, $minDays_rw, $maxDays_rw, $pickupCost, $deliveryCost, $additionalInfo);
}

function KIT_GetCityIdFromFile($city) {
//Array ( [ID] => 630000100000 [NAME] => Самара [COUNTRY] => RU [TZONEID] => 0000006301 [REGION] => 63 [TZONE] => Y [SR] => Y [OC] => X [TP] => гор. [SP] => 1 ) 
//Array ( [ID] => 770000000000 [NAME] => Москва [COUNTRY] => RU [TZONEID] => 0000007700 [REGION] => 77 [TZONE] => Y [SR] => Y [OC] => X [TP] => гор. [SP] => 1 ) 
    $json = file_get_contents('kit_cities.json');
    //$json = curl_get_contents('http://tk-kit.ru/API.1/?f=get_city_list');
    $ar_locations = json_decode($json, true)["CITY"];
    foreach ($ar_locations as $location) {
        if (strcasecmp(mb_strtoupper($location['NAME'], 'utf8'), mb_strtoupper($city, 'utf8')) == 0) {
            $result = $location['TZONEID'];
            break;
        }
    }
    return $result;
}

function KIT_GetCityId($city) {
    $mysqli = new mysqli('localhost', 'root', '', 'dbcalc');
    if ($mysqli->connect_error) {
        die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
    }
    /* Select запросы возвращают результирующий набор */
    mysqli_query($mysqli, "SET NAMES utf8");
//if ($result = $mysqli->query("SELECT searchString, name FROM cls_cities where searchString like 'Сама%' and code like '%00000000000000000' limit 100")) {
    $searchstring = $city;
    if ($result = $mysqli->query("SELECT TZONEID FROM kit_cities WHERE name LIKE '%" . $searchstring . "%'")) {
        //printf("Select вернул %d строк.\n", $result->num_rows);
        //$data=  mysqli_fetch_assoc($result);
        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        //echo json_encode($data, JSON_UNESCAPED_UNICODE);
        /* очищаем результирующий набор */
        $result->close();
    }
    $mysqli->close();
    return $data[0]['TZONEID'];
}

function KIT_GetCities() {
    $json = file_get_contents('kit_cities.json');
    //$json = curl_get_contents('http://tk-kit.ru/API.1/?f=get_city_list');
    $ar_locations = json_decode($json, true)["CITY"];
    $json = json_encode($ar_locations, JSON_UNESCAPED_UNICODE);
    $ar_locations = json_decode($json, true);
    echo '"ID","NAME","COUNTRY","TZONEID","REGION","TZONE","SR","OC","TP","SP"<br/>';
    foreach ($ar_locations as $location) {
        echo '"' . $location['ID'] . '",' .
        '"' . $location['NAME'] . '",' .
        '"' . $location['COUNTRY'] . '",' .
        '"' . $location['TZONEID'] . '",' .
        '"' . $location['REGION'] . '",' .
        '"' . $location['TZONE'] . '",' .
        '"' . $location['SR'] . '",' .
        '"' . $location['OC'] . '",' .
        '"' . $location['TP'] . '",' .
        '"' . $location['SP'] . '"'
        . '<br/>';
    };

    //return json_encode($ar_locations, JSON_UNESCAPED_UNICODE);
}

//print_r(KIT_Calculate("Самара", "Рязань", 10, 0.16, 1));
//KIT_GetCities();
//echo KIT_GetCityId('Рязань');
