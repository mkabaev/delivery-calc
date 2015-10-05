<?php

include_once 'functions.php';

function NRG_Calculate($city_from, $city_to, $weight, $volume, $quantity) {
    $id_city_from = NRG_GetCityId($city_from);
    $id_city_to = NRG_GetCityId($city_to);
    $url = 'http://api.nrg-tk.ru/api/rest/?method=nrg.calculate&from=' . $id_city_from . '&to=' . $id_city_to . '&weight=' . $weight . '&volume=' . $volume . '&place=' . $quantity;
    $json_response = GetResponse_get($url);
    $ar = json_decode($json_response, true)['rsp'];

    $responseStatus='';
    $cost_at=0;
    $minDays_at=0;
    $maxDays_at=0;
    $cost_av=0;
    $minDays_av=0;
    $maxDays_av=0;
    $cost_rw=0;
    $minDays_rw=0;
    $maxDays_rw=0;
    $pickupCost=0;
    $deliveryCost=0;
    $additionalInfo='';
    if ($ar['stat'] == "ok") { // if NRG response is OK
        $responseStatus='ok';
        foreach ($ar['values'] as $value) {
            if ($value['type'] == "avto") {
                $cost_at=$value['price'];
                $minDays_at=$value['term'];
                $maxDays_at=$value['term'];
            }
            if ($value['type'] == "rw") {
                $cost_rw=$value['price'];
                $minDays_rw=$value['term'];
                $maxDays_rw=$value['term'];
            }
            if ($value['type'] == "avia") {
                $cost_av=$value['price'];
                $minDays_av=$value['term'];
                $maxDays_av=$value['term'];
            }
        }
    } else {
        $responseStatus='err';
        $additionalInfo='NRG Api error';
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
$mysqli = new mysqli('localhost', 'root', '', 'dbcalc');
if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}
/* Select запросы возвращают результирующий набор */
mysqli_query($mysqli, "SET NAMES utf8");
//if ($result = $mysqli->query("SELECT searchString, name FROM cls_cities where searchString like 'Сама%' and code like '%00000000000000000' limit 100")) {
$searchstring = $city;
if ($result = $mysqli->query("SELECT id FROM `nrg_cities` WHERE name LIKE '%" . $searchstring . "%'")) {
    //printf("Select вернул %d строк.\n", $result->num_rows);
    //$data=  mysqli_fetch_assoc($result);
    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
    //echo json_encode($data, JSON_UNESCAPED_UNICODE);
    /* очищаем результирующий набор */
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



    return $data[0]['id'];
}

function NRG_GetCitiesCSV() {
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
echo '<pre>';
print_r(NRG_Calculate("Самара", "ТЮМЕНЬ", 10, 0.16, 1));
echo '<pre/>';