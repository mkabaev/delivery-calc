<?php

require_once 'functions.php';

/**
 * Функция Калькулятор ТК ПЭК
 * @param string $city_from Город отправитель
 * @param string $city_to Город получатель
 * @param integer $weight Вес груза в кг
 * @param float $volume Объем груза в м3 (например 0.16)
 * @param integer $quantity Кол-во мест
 * @return Array
 */
function PEC_Calculate($city_from, $city_to, $weight, $volume, $quantity) {
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

    $id_city_from = PEC_GetCityId($city_from);
    $id_city_to = PEC_GetCityId($city_to);
    if (is_null($id_city_from) or is_null($id_city_to)) {
        $responseStatus = "err";
        $additionalInfo = "В базе данных не найден один из городов отправитель|получатель: " . $id_city_from . "|" . $id_city_to;
    } else {
//example: http://pecom.ru/bitrix/components/pecom/calc/ajax.php?places[0][]=&places[0][]=&places[0][]=&places[0][]=1&places[0][]=1&places[0][]=0&places[0][]=0&take[town]=-446&take[tent]=0&take[gidro]=0&take[manip]=0&take[speed]=0&take[moscow]=0&deliver[town]=-441&deliver[tent]=0&deliver[gidro]=0&deliver[manip]=0&deliver[speed]=0&deliver[moscow]=0&plombir=0&strah=0&ashan=0&night=0&pal=0&pallets=0
        //places[0][] (7 параметров по очереди) - Ширина, Длина, Высота, Объем, Вес, Признак негабаритности груза, Признак ЖУ
        //take[town]: -457 	ID города забора 
        //take[tent]: 1 	требуется растентровка при заборе 
        //take[gidro]: 1 	требуется гидролифт при заборе 
        //take[manip]: 1 	требуется манипулятор при заборе 
        //take[speed]: 1 	Срочный забор (только для Москвы) 
        //take[moscow]: 1 	Без въезда, МОЖД, ТТК, Садовое. 
        //            
        //
    //           значения соответственно: 0,1,2,3 
        //deliver[town]: 64883 	ID города доставки
        //deliver[tent]: 1 	Требуется растентровка при доставке
        //deliver[gidro]: 1 	Требуется гидролифт при доставке
        //deliver[manip]: 1 	Требуется манипулятор при доставке
        //deliver[speed]: 0 	Срочная доставка (только для Москвы) 
        //deliver[moscow]: 0	Без въезда, МОЖД, ТТК, Садовое. 
        //            
        //
    //           значения соответственно: 0,1,2,3 
        //plombir: 12 	Количество пломб 
        //strah: 33 	Величина страховки 
        //ashan: 1 	Доставка в Ашан 
        //night: 1 	Забор в ночное время 
        //pal: 3 	Требуется запаллечивание груза (0 - не требуется, значение больше нуля - количество паллет)
        //pallets: 4 	Кол-во паллет для расчет услуги паллетной перевозки (только там, где эта услуга предоставляется)

        $url = 'http://pecom.ru/bitrix/components/pecom/calc/ajax.php?places[0][]=&places[0][]=&places[0][]=&places[0][]=' . $volume*$quantity . '&places[0][]=' . $weight*$quantity . '&places[0][]=0&places[0][]=0&take[town]=' . $id_city_from . '&take[tent]=0&take[gidro]=0&take[manip]=0&take[speed]=0&take[moscow]=0&deliver[town]=' . $id_city_to . '&deliver[tent]=0&deliver[gidro]=0&deliver[manip]=0&deliver[speed]=0&deliver[moscow]=0&plombir=0&strah=0&ashan=0&night=0&pal=0&pallets=0';
        $json_response = GetResponse_get($url);
        $ar = json_decode($json_response, true, JSON_UNESCAPED_UNICODE);
        if (array_key_exists("auto", $ar) or array_key_exists("avia", $ar)) { // if PEC response is OK
            $responseStatus = 'ok';
            if (array_key_exists("auto", $ar)) {
                $cost_at = round($ar['auto'][2]);
                if (array_key_exists("periods", $ar)) {
                    preg_match_all('!\d+!', $ar['periods'], $matches);
                    if (array_key_exists(0, $matches[0])) {
                        $minDays_at = round($matches[0][0]);
                    }
                    if (array_key_exists(1, $matches[0])) {
                        $maxDays_at = round($matches[0][1]);
                    }
                }
                if (array_key_exists("alma_auto", $ar)) { // доп. стоимость для Алматы
                    $cost_at = $cost_at + round($ar['alma_auto'][2]);
                    $additionalInfo = 'Доставка будет осуществляться через г. Екатеринбург';
                }
            }

            if (array_key_exists("avia", $ar)) {
                $cost_av = round($ar['avia'][2]);
//        $minDays_av = 0;
//        $maxDays_av = 0;
            }

            if (array_key_exists("take", $ar)) {
                $pickupCost = round($ar['take'][2]);
            }
            if (array_key_exists("deliver", $ar)) {
                $deliveryCost = round($ar['deliver'][2]);
            }
        } else {
            $responseStatus = 'err';
            $additionalInfo = 'PEC Api error';
        }
    }
    return PrepareReponseArray($responseStatus, $cost_at, $minDays_at, $maxDays_at, $cost_av, $minDays_av, $maxDays_av, $cost_rw, $minDays_rw, $maxDays_rw, $pickupCost, $deliveryCost, $additionalInfo);
}

function PEC_GetCityId($city) {
    return GetValueFromDB("pec_cities", "id", $city);
}

function PEC_GetCitiesCSV() {
    //$json = GetResponse_get('http://pecom.ru/ru/calc/towns.php');
    $json = file_get_contents('pec_cities.json');
    $ar_locations = json_decode($json, true);
    $json = json_encode($ar_locations, JSON_UNESCAPED_UNICODE);
    $ar_locations = json_decode($json, true);

    foreach ($ar_locations as $key => $value) {
        foreach ($value as $id => $name) {
            echo '"' . $id . '","' . $name . '","' . $key . '"<br/>';
        }
//    echo $key;
//    echo '<pre>';
//    print_r($value);
//    echo '<pre/>';
    }
}

//TEST PEC
//PEC_GetCitiesCSV();
//echo PEC_GetCityId('Самара');
//echo '<pre>';
//$start = microtime(true);
//print_r(PEC_Calculate('Самара', 'Новосибирск', 10, 0.16, 2));
//echo "Время выполнения скрипта: " . (microtime(true) - $start);
//echo '</pre>';
