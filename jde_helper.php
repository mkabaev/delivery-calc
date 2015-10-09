<?php

require_once 'functions.php';

/**
 * Функция Калькулятор ТК Энергия
 * @param string $city_from Город отправитель
 * @param string $city_to Город получатель
 * @param integer $weight Вес груза в кг
 * @param float $volume Объем груза в м3 (например 0.16)
 * @param integer $quantity Кол-во мест
 * @return Array
 */
function JDE_Calculate($city_from, $city_to, $weight, $volume, $quantity) {
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

    $id_city_from = JDE_GetCityId($city_from);
    $id_city_to = JDE_GetCityId($city_to);

    if (is_null($id_city_from) or is_null($id_city_to)) {
        $responseStatus = "err";
        $additionalInfo = "В базе данных не найден один из городов отправитель|получатель: " . $id_city_from . "|" . $id_city_to;
    } else {
        $url = "http://apitest.jde.ru:8000/calculator/price?from=".$id_city_from."&to=".$id_city_to."&weight=".$weight."&width=1&volume=".$volume;
        //echo $url;
        $json_response = GetResponse_get($url);
        // normal response: {"price":"5118.0000","mindays":"7","maxdays":"10"}
        // err response: {"errors":"You should determine all required fields. Check dev docs."}
        // err response2: -1
        //echo '<br/>Response is: ' . $json_response;
        $ar = json_decode($json_response, true);
        if ($ar != -1 or array_key_exists('errors', $ar)) { // if JDE response is OK
            $responseStatus = 'ok';
            $cost_at = round($ar['price']);
            $minDays_at = $ar['mindays'];
            $maxDays_at = $ar['maxdays'];
        } else {
            $responseStatus = 'err';
            $additionalInfo = 'JDE Api error';
            if (array_key_exists('errors', $ar)) {
                $additionalInfo = $additionalInfo . ': ' . $ar['errors'];
            }
        }
    }
    return PrepareReponseArray($responseStatus, $cost_at, $minDays_at, $maxDays_at, $cost_av, $minDays_av, $maxDays_av, $cost_rw, $minDays_rw, $maxDays_rw, $pickupCost, $deliveryCost, $additionalInfo);
}

function JDE_GetCityIdFromFile($city) {
    $json = file_get_contents('nrg_cities.json');
    //$json = curl_get_contents('http://api.nrg-tk.ru/api/rest/?method=nrg.get.locations');
    $ar_locations = json_decode($json, true)['rsp']['locations'];
    foreach ($ar_locations as $location) {
        if (strcasecmp(mb_strtoupper($location['name'], 'utf8'), mb_strtoupper($city, 'utf8')) == 0) {
            $result = $location['id'];
            break;
        }
    }
    return $result;
}

function JDE_GetCityId($city) {
    return GetValueFromDB("jde_cities", "code", $city, "title");
}

function JDE_GetCitiesCSV() {
    //$json = GetResponse_get('http://apitest.jde.ru:8000/geo/search?mode=2');     // или можно взять локальный файл вместо запроса file_get_contents('jde_cities.json');
    $json = file_get_contents('jde_cities.json');
    //response example: {"code":"1125899906842653","title":"Абакан","kladr_code":"1900000100000","addr":"г.Абакан, ул. Игарская, д 5 \"В\"","coords":{"lat":"53.710762","lng":"91.390152"},"city":"Абакан"}    
    // типы терминалов mode=
    //      1 - пункты приема
    //      2 - пункты выдачи
    $ar_locations = json_decode($json, true);
//    echo '<pre>';
//    print_r($ar_locations);
//    echo '</pre>';
    echo '"code","title","kladr_code","addr","coords_lat","coords_lng","city"<br/>';
    foreach ($ar_locations as $location) {
        $coords_lat=array_key_exists('lat', $location['coords'])?$location['coords']['lat']:"";
        $coords_lng=array_key_exists('lng', $location['coords'])?$location['coords']['lng']:"";
        echo '"' . $location['code'] . '","' . $location['title'] . '","' . $location['kladr_code'] . '","' . $location['addr'] . '","' . $coords_lat. '","' . $coords_lng . '","' . $location['city'] . '"<br/>';
    }
}

// TEST JDE
//echo '<pre>';
//$start = microtime(true);
//print_r(JDE_Calculate('Самара', 'Москва', 10, 0.16, 1));
//echo "Время выполнения скрипта: " . (microtime(true) - $start);
//echo '</pre>';

//JDE_GetCitiesCSV();
//echo JDE_GetCityId('Новосибирск');