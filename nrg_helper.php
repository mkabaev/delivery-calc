<?php

require_once 'functions.php';

function NRG_Calculate($city_from, $city_to, $weight, $volume, $quantity) {
    $id_city_from = NRG_GetCityId($city_from);
    $id_city_to = NRG_GetCityId($city_to);
    $url = 'http://api.nrg-tk.ru/api/rest/?method=nrg.calculate&from=' . $id_city_from . '&to=' . $id_city_to . '&weight=' . $weight . '&volume=' . $volume . '&place=' . $quantity;
    $json_response = GetResponse_get($url);
    $ar = json_decode($json_response, true)['rsp'];

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
    if ($ar['stat'] == "ok") { // if NRG response is OK
        $responseStatus = 'ok';

        foreach ($ar['values'] as $value) {
            if ($value['type'] == "avto") {
                $cost_at = $value['price'];

                //extract periods
                preg_match_all('!\d+!', $value['term'], $matches);
                if (array_key_exists(0, $matches[0])) {
                    $minDays_at = round($matches[0][0]);
                }
                if (array_key_exists(1, $matches[0])) {
                    $maxDays_at = round($matches[0][1]);
                }
            }
            if ($value['type'] == "rw") {
                $cost_rw = $value['price'];
                //extract periods
                preg_match_all('!\d+!', $value['term'], $matches);
                if (array_key_exists(0, $matches[0])) {
                    $minDays_rw = round($matches[0][0]);
                }
                if (array_key_exists(1, $matches[0])) {
                    $maxDays_rw = round($matches[0][1]);
                }
            }
            if ($value['type'] == "avia") {
                $cost_av = $value['price'];
                //extract periods
                preg_match_all('!\d+!', $value['term'], $matches);
                if (array_key_exists(0, $matches[0])) {
                    $minDays_av = round($matches[0][0]);
                }
                if (array_key_exists(1, $matches[0])) {
                    $maxDays_av = ($matches[0][1]);
                }
            }
        }
    } else {
        $responseStatus = 'err';
        $additionalInfo = 'NRG Api error';
    }
    return PrepareReponseArray($responseStatus, $cost_at, $minDays_at, $maxDays_at, $cost_av, $minDays_av, $maxDays_av, $cost_rw, $minDays_rw, $maxDays_rw, $pickupCost, $deliveryCost, $additionalInfo);
}

function NRG_GetCityIdFromFile($city) {
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

function NRG_GetCityId($city) {
    return GetValueFromDB("nrg_cities", "id", $city);
}

function NRG_GetCitiesCSV() {
    // после получения списка переименовать С.Петербург в Санкт-Петербург
    $json = GetResponse_get('http://api.nrg-tk.ru/api/rest/?method=nrg.get.locations');     // или можно взять локальный файл вместо запроса file_get_contents('nrg_cities.json');
    $ar_locations = json_decode($json, true)['rsp']['locations'];
    foreach ($ar_locations as $location) {
        echo '"' . $location['id'] . '","' . $location['name'] . '"<br/>';
    }
}

//echo NRG_GetCitiesCSV();
//{"error":"1","errorcode":["empty_pers","empty_mail","empty_phone","empty_letter","empty_scode"]}
//$json_data = array ('id'=>1,'name'=>"ivan",'country'=>'Russia',"office"=>array("yandex"," management"));
//echo json_encode($json_data);
//$json_string='{"id":1,"name":"ivan","country":"Russia","office":["yandex"," management"]} ';
//$obj=json_decode($json_string); 
//echo $obj->name; //Отобразит имя ivan
//echo $obj->office[0]; //Отобразит компанию yandex
//$ar_NRG["status"]="ok";
//$ar_NRG["tk"]="energy";
//$ar_tk_data["price"]="250";
//$ar_tk_data["term"]="3-5 days";
//$ar_tk_data["type"] ="Avto";
//$ar["NRG"]=$ar_tk_data;
//$ar["KIT"]=$ar_tk_data;
//echo json_encode($ar);
//echo NRG_GetCityId('Рязань');
// TEST NRG
//echo '<pre>';
//print_r(NRG_Calculate("Самара", "Москва", 10, 0.16, 1));
//echo '<pre/>';
