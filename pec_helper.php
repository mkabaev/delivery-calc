<?php

require_once 'functions.php';

function PEC_Calculate($city_from, $city_to, $weight, $volume, $quantity) {
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

    $id_city_from = PEC_GetCityId($city_from);
    $id_city_to = PEC_GetCityId($city_to);
    $url = 'http://pecom.ru/bitrix/components/pecom/calc/ajax.php?places[0][]=&places[0][]=&places[0][]=&places[0][]=' . $volume . '&places[0][]=' . $weight . '&places[0][]=0&places[0][]=0&take[town]=' . $id_city_from . '&take[tent]=0&take[gidro]=0&take[manip]=0&take[speed]=0&take[moscow]=0&deliver[town]=' . $id_city_to . '&deliver[tent]=0&deliver[gidro]=0&deliver[manip]=0&deliver[speed]=0&deliver[moscow]=0&plombir=0&strah=0&ashan=0&night=0&pal=0&pallets=0';
    $json_response = GetResponse_get($url);
    $ar = json_decode($json_response, true, JSON_UNESCAPED_UNICODE);

    $responseStatus = '';
    $cost_at = $ar['auto'][2];
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

//    echo '<br/>cost_at '.$cost_at;
//    echo '<br/>pickup cost '.$pickupCost;
//    echo '<br/>delivery cost '.$deliveryCost;
//    echo '<br/>err '.$ar['error'][0];
//    
    //echo '<br/>ALLARRAY<pre>';
    //print_r($ar);
    //echo '<pre/>';

    if (array_key_exists("auto", $ar)) {
        $responseStatus='ok';
        $cost_at = $ar['auto'][2];
        if (array_key_exists("periods", $ar)) {
            $minDays_at = $ar['periods'];
            $maxDays_at = $ar['periods'];
            // TODO распарсить
            //Количество суток в пути: 4 - 6
            //Дни отправки: пн, вт, ср, чт, пт, сб, вс
        }
        if (array_key_exists("alma_auto", $ar)) { // доп. стоимость для Алматы
            $cost_at=$cost_at+$ar['alma_auto'][2];
            $additionalInfo = 'Доставка будет осуществляться через г. Екатеринбург';
        }
    }

    if (array_key_exists("avia", $ar)) {
        $responseStatus='ok';
        $cost_av = $ar['avia'][2];
//        $minDays_av = 0;
//        $maxDays_av = 0;
    }

    if (array_key_exists("take", $ar)) {
        $pickupCost = $ar['take'][2];
    }
    if (array_key_exists("deliver", $ar)) {
        $deliveryCost = $ar['deliver'][2];
    }

//    if ($ar['stat'] == "ok") { // if NRG response is OK
//        $responseStatus = 'ok';
//        foreach ($ar['values'] as $value) {
//            if ($value['type'] == "avto") {
//                $cost_at = $value['price'];
//                $minDays_at = $value['term'];
//                $maxDays_at = $value['term'];
//            }
//            if ($value['type'] == "rw") {
//                $cost_rw = $value['price'];
//                $minDays_rw = $value['term'];
//                $maxDays_rw = $value['term'];
//            }
//            if ($value['type'] == "avia") {
//                $cost_av = $value['price'];
//                $minDays_av = $value['term'];
//                $maxDays_av = $value['term'];
//            }
//        }
//    } else {
//        $responseStatus = 'err';
//        $additionalInfo = 'PEC Api error';
//    }
    return PrepareReponseArray($responseStatus, $cost_at, $minDays_at, $maxDays_at, $cost_av, $minDays_av, $maxDays_av, $cost_rw, $minDays_rw, $maxDays_rw, $pickupCost, $deliveryCost, $additionalInfo);
}

function PEC_GetCityId($city) {
    $mysqli = new mysqli('localhost', 'root', '', 'dbcalc');
    if ($mysqli->connect_error) {
        die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
    }
    /* Select запросы возвращают результирующий набор */
    mysqli_query($mysqli, "SET NAMES utf8");
//if ($result = $mysqli->query("SELECT searchString, name FROM cls_cities where searchString like 'Сама%' and code like '%00000000000000000' limit 100")) {
    $searchstring = $city;
    if ($result = $mysqli->query("SELECT * FROM pec_cities WHERE name LIKE '" . $searchstring . "%'")) {
        //printf("Select вернул %d строк.\n", $result->num_rows);
        //$data=  mysqli_fetch_assoc($result);
        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        //echo json_encode($data, JSON_UNESCAPED_UNICODE);
        /* очищаем результирующий набор */
        $result->close();
    }
    $mysqli->close();
    return $data[0]['id'];
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
//echo '<hr/><pre>';
//print_r(PEC_Calculate("Самара", "Новосибирск", 10, 0.16, 1));
//echo '</pre>';
