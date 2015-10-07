<?php

require_once 'functions.php';

$appkey = '330AF810-6347-11E5-B2E6-00505683A6D3'; //Ключ приложения
$url_pack = 'https://api.dellin.ru/v1/public/packages.json';
$url_services = 'https://api.dellin.ru/v1/public/services.json';
$url_places = 'https://api.dellin.ru/v1/public/places.json';
$url_delivery_types = 'https://api.dellin.ru/v1/public/request_delivery_types.json';
$url_statuses = 'https://api.dellin.ru/v1/public/statuses.json';

/**
 * Функция Калькулятор ТК Деловые Линии
 * @param string $city_from Город отправитель
 * @param string $city_to Город получатель
 * @param integer $weight Вес груза в кг
 * @param float $volume Объем груза в м3 (например 0.16)
 * @param integer $quantity Кол-во мест
 * @return Array
 */
function DELLIN_Calculate($city_from, $city_to, $weight, $volume, $quantity) {
    global $appkey;
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

    $id_city_from = DELLIN_GetCityId($city_from); //"6300000100000000000000000";
    $id_city_to = DELLIN_GetCityId($city_to); //"7800000000000000000000000";
    if (is_null($id_city_from) or is_null($id_city_to)) {
        $responseStatus = "err";
        $additionalInfo = "В базе данных не найден один из городов отправитель|получатель: " . $id_city_from . "|" . $id_city_to;
    } else {
        $ar_request = ["appKey" => $appkey,
            "derivalPoint" => $id_city_from, // код КЛАДР пункта отправки  (обязательное поле)
            "derivalDoor" => true, // необходима доставка груза от адреса     (необязательный параметр), true/false
            "arrivalPoint" => $id_city_to, // код КЛАДР пункта прибытия (обязательный параметр)
            "arrivalDoor" => true, // необходима доставка груза до адреса    (необязательный параметр), true/false
            "sizedVolume" => $volume*$quantity, // общий объём груза в кубических метрах (обязательный параметр)
            "sizedWeight" => $weight*$quantity, // общий вес груза в килограммах (обязательный параметр)
            //    "oversizedVolume" => "1", // объём негабаритной части груза в метрах кубических (необязательный параметр)
            //    "oversizedWeight" => "1", // вес негабаритной части груза в килограммах (необязательный параметр)
            //    "length" => "1", // длинна самого длинного из мест (необязательный параметр)
            //    "width" => "1", // ширина самого широкого из мест (необязательный параметр)
            //    "height" => "1", // высота самого высокого из мест (необязательный параметр)
            //    "statedValue" => 1000, // заявленная стоимость груза в рублях. необходимо передать этот параметр, если требуется страхование груза с заявленной стоимостью         (необязательный параметр)
            //    "packages" => [
            //        "0xAD22189D098FB9B84EEC0043196370D6"
            //    ], // необходимо упаковать груз в упаковку (необязательные параметры)
            //    "derivalServices" => ["0xb83b7589658a3851440a853325d1bf69"], // требуются дополнительные услуги для доставки груза от адреса - боковая погрузка (необязательные параметры) 
            //    "arrivalServices" => ["0xb83b7589658a3851440a853325d1bf69"], // требуются дополнительные услуги для доставки груза до адреса - боковая погрузка (необязательные параметры)
            "quantity" => $quantity // количество мест (необязательно), по-умолчанию расчет производится для одного места
        ];
        $url_calc = 'https://api.dellin.ru/v1/public/calculator.json';
        $json_response = GetResponse_post($url_calc, $ar_request);
        $ar = json_decode($json_response, true);
//echo '<pre>';
//print_r($ar_request);
//echo '</pre><hr/>';
        if (!array_key_exists("errorses", $ar)) { // if DELLIN response is OK
            $responseStatus = "ok"; // mark result is ok
            if (array_key_exists("derival", $ar)) {
                $pickupCost = round($ar['derival']['price']);
                //$ar['derival'['terminals']
            }
            if (array_key_exists("arrival", $ar)) {
                $deliveryCost = round($ar['arrival']['price']);
                //$ar['arrival'['terminals']
            }
            $cost_at = round($ar['price'] - $pickupCost - $deliveryCost);
            $minDays_at = round($ar['time']['value']);
            $maxDays_at = round($ar['time']['value']);
        } else {
            $responseStatus = "err";
            $additionalInfo = "DELLIN API error";
        }
    }
    return PrepareReponseArray($responseStatus, $cost_at, $minDays_at, $maxDays_at, $cost_av, $minDays_av, $maxDays_av, $cost_rw, $minDays_rw, $maxDays_rw, $pickupCost, $deliveryCost, $additionalInfo);
}

function DELLIN_GetCities_CSVurl() {
    //полученная ссылка действительна 10 мин с момента получения
    global $appkey;
    $ar_request = ["appKey" => $appkey];
    $url_cities = 'https://api.dellin.ru/v1/public/cities.json';
    $json_response = GetResponse_post($url_cities, $ar_request);
    return json_decode($json_response, true)['url'];
    // CSV FILE
    //id — уникальный идентификатор города;
    //name — наименование;
    //codeKLADR — КЛАДР города;
    //isTerminal — флаг наличия терминала в городе.
}

function DELLIN_GetCityId($city) {
    return GetValueFromDB("dellin_cities", "codeKLADR", $city);
}

// TEST DELLIN
//echo '<pre>';
//$start = microtime(true);
//print_r(DELLIN_Calculate('Самара', 'Санкт-Петербург', 10, 0.1, 3));
//echo "Время выполнения скрипта: " . (microtime(true) - $start);
//echo '</pre>';

//echo DELLIN_GetCities_CSVurl(); //ссылка действительна 10 мин с момента получения
//echo DELLIN_GetCityId('Самара');