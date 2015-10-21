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
    $responseStatus = ''; //Статус
    $cost = 0; //Стоимость межтерминальной перевозки
    $minDays = 0; //Минимальное кол-во дней
    $maxDays = 0; //Макс кол-во дней
    $pickupCost = 0; //Стоимость забора груза
    $deliveryCost = 0; //Стоимость доставки до получателя
    $additionalInfo = ''; //Доп.инфо

    $id_city_from = DELLIN_GetCityId($city_from); //"6300000100000000000000000";
    $id_city_to = DELLIN_GetCityId($city_to); //"7800000000000000000000000";
    $isTerminal = DELLIN_TerminalExists($city_to);
    if (is_null($id_city_from) or is_null($id_city_to)) {
        $responseStatus = "err";
        $additionalInfo = "В базе данных не найден один из городов отправитель|получатель: " . $id_city_from . "|" . $id_city_to;
    } else {
        $ar_request = ["appKey" => $appkey,
            "derivalPoint" => $id_city_from, // код КЛАДР пункта отправки  (обязательное поле)
            "derivalDoor" => true, // необходима доставка груза от адреса     (необязательный параметр), true/false
            "arrivalPoint" => $id_city_to, // код КЛАДР пункта прибытия (обязательный параметр)
            "arrivalDoor" => true, // необходима доставка груза до адреса    (необязательный параметр), true/false
            "sizedVolume" => $volume * $quantity, // общий объём груза в кубических метрах (обязательный параметр)
            "sizedWeight" => $weight * $quantity, // общий вес груза в килограммах (обязательный параметр)
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

            $cost = $isTerminal?round($ar['intercity']['price']):round($ar['intercity']['price']+round($ar['arrival']['price']));
            $minDays = round($ar['time']['value']);
            $maxDays = round($ar['time']['value']);

            if (array_key_exists("derival", $ar)) {
                $pickupCost = round($ar['derival']['price']);
                //$additionalInfo['terminals']['departure'] = $ar['derival']['terminals'];
            }

            if (!$isTerminal) {
                $additionalInfo[1]="Терминала в " . $city_to . " нет. Стоимость перевозки до ".$ar['arrival']['terminal'].": ".round($ar['intercity']['price'])." + доставка до " . $city_to . ": ".round($ar['arrival']['price']). ".";
            }

            if (array_key_exists("express", $ar)) {
                $additionalInfo[2] = "Возможна Экспресс-Доставка: " . Round($ar['express']['price']) . " руб.";
            }

            if (array_key_exists("air", $ar)) {
                $additionalInfo[3] = "Возможна доставка самолетом: " . Round($ar['air']['price']) . " руб.";
            }

            if (array_key_exists("arrival", $ar)) {
                $deliveryCost = $isTerminal?round($ar['arrival']['price']):0; // если терминала нет, то доставка обязательна. Доставка учитывается в $cost
                //$additionalInfo['terminals'] = $ar['arrival']['terminals'];
            }

        } else {
            $responseStatus = "err";
            $additionalInfo = "DELLIN API error";
        }
    }
    return PrepareReponseArray($responseStatus, $cost, $minDays, $maxDays, $pickupCost, $deliveryCost, $additionalInfo);
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

function DELLIN_TerminalExists($city) {
    return GetValueFromDB("dellin_cities", "isTerminal", $city);
}

// TEST DELLIN
//echo '<pre>';
//$start = microtime(true);
//print_r(DELLIN_Calculate('Самара', 'Нягань', 10, 0.16, 1));
//echo "Время выполнения скрипта: " . (microtime(true) - $start);
//echo '</pre>';

//echo DELLIN_TerminalExists('нягань');
//echo DELLIN_GetCities_CSVurl(); //ссылка действительна 10 мин с момента получения
//echo DELLIN_GetCityId('Самара');