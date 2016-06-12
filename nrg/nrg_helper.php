<?php

require_once '../functions.php';

/**
 * Функция Калькулятор ТК Энергия
 * @param string $city_from Город отправитель
 * @param string $city_to Город получатель
 * @param integer $weight Вес груза в кг
 * @param float $volume Объем груза в м3 (например 0.16)
 * @param integer $quantity Кол-во мест
 * @return Array
 */
function NRG_Calculate($city_from, $city_to, $places) {
    $responseStatus = '';
    $cost = 0;
    $minDays = 0;
    $maxDays = 0;
    $pickupCost = 0;
    $deliveryCost = 0;
    $additionalInfo = '';

    $id_city_from = NRG_GetCityId($city_from);
    //echo 'id_city_from='.$id_city_from.'<br/>';
    $id_city_to = NRG_GetCityId($city_to);
    //echo 'id_city_to='.$id_city_to.'<br/>';

    if (is_null($id_city_from) or is_null($id_city_to)) {
        $responseStatus = "err";
        $additionalInfo = "В базе данных не найден один из городов отправитель|получатель: " . $id_city_from . "|" . $id_city_to;
    } else {
        // old $url = 'http://api.nrg-tk.ru/api/rest/?method=nrg.calculate&from=' . $id_city_from . '&to=' . $id_city_to . '&weight=' . $weight . '&volume=' . $volume . '&place=' . $quantity;
        //echo $url;
        $request_data = '{
  "idCityFrom": ' . $id_city_from . ',
  "idCityTo": ' . $id_city_to . ',
  "cover": 0,
  "idCurrency": 0,
  "items": [' . $places . ']
}';

        $json = GetResponse_post('https://api2.nrg-tk.ru/v2/price', $request_data);
        //echo $json_response;
        //{"places":2,"weight":20,"volume":0.32,"cover":0,"transfer":[{"typeId":1,"type":"Авто","price":464,"interval":"2-4 дней","oversize":null}],"request":{"typeId":0,"type":"","price":250,"interval":"","oversize":null},"delivery":{"typeId":0,"type":"","price":700,"interval":"","oversize":null}}
        $array = json_decode($json, true);
        if (array_key_exists('transfer', $array)) { // if NRG response is OK
            $responseStatus = "ok"; // mark result is ok
            $cost = round($array['transfer'][0]['price']);

            //extract periods
            //var_dump($array['transfer'][0]['interval']);
            preg_match_all('!\d+!', $array['transfer'][0]['interval'], $matches);
            if (array_key_exists(0, $matches[0])) {
                $minDays = round($matches[0][0]);
            }
            //echo 'MIN:'.$minDays.'<br/>';
            if (array_key_exists(1, $matches[0])) {
                $maxDays = round($matches[0][1]);
            }
            //echo 'MAX:'.$maxDays.'<br/>';
            
            if (array_key_exists('request', $array)) {
                $pickupCost = round($array['request']['price']);
            }
            if (array_key_exists('delivery', $array)) {
                $deliveryCost = round($array['delivery']['price']);
            }
        } else {
            $responseStatus = 'err';
            $additionalInfo = 'NRG Api error';
        }
    }
    return PrepareReponseArray($responseStatus, $cost, $minDays, $maxDays, $pickupCost, $deliveryCost, $additionalInfo);
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

function NRG_GetCities() {

    return 'RESULT IS ';
}

//$json = get_web_page('https://api2.nrg-tk.ru/v2/cities');


//https://api2.nrg-tk.ru/v2/cities

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

