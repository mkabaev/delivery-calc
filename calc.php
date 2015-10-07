<?php

require_once 'nrg_helper.php';
require_once 'dellin_helper.php';
require_once 'kit_helper.php';
require_once 'pec_helper.php';

$city_to = filter_input(INPUT_GET, 'city_to');
$weight = filter_input(INPUT_GET, 'weight');
$volume = filter_input(INPUT_GET, 'volume');
$quantity = filter_input(INPUT_GET, 'quantity');
$isHTMLResult = filter_input(INPUT_GET, 'isHTMLResult');

//$city_to = 'Новосибирск';
//$weight = 10;
//$volume = 0.16;
//$quantity = 1;
//$isHTMLResult = true;

if (!is_null($city_to) and ! is_null($weight) and ! is_null($volume) and ! is_null($quantity)) {
    $ar_NRGResult = NRG_Calculate("Самара", $city_to, $weight, $volume, $quantity);
    $ar_DELLINResult = DELLIN_Calculate("Самара", $city_to, $weight, $volume, $quantity);
    $ar_PECResult = PEC_Calculate("Самара", $city_to, $weight, $volume, $quantity);
    $ar_KITResult = KIT_Calculate("Самара", $city_to, $weight, $volume, $quantity);
    $ar_result['nrg'] = $ar_NRGResult;
    $ar_result['dellin'] = $ar_DELLINResult;
    $ar_result['pec'] = $ar_PECResult;
    $ar_result['kit'] = $ar_KITResult;

    if ($isHTMLResult) {
        echo '<h3>Доставка в ' . $city_to . '</h3>';
        echo "<p>Энергия:<br/>" . json_encode($ar_NRGResult, JSON_UNESCAPED_UNICODE) . "<p/>";
        echo "<p>Деловые Линии:<br/>" . json_encode($ar_DELLINResult, JSON_UNESCAPED_UNICODE) . "<p/>";
        echo "<p>ПЭК:<br/>" . json_encode($ar_PECResult, JSON_UNESCAPED_UNICODE) . "<p/>";
        echo "<p>КИТ:<br/>" . json_encode($ar_KITResult, JSON_UNESCAPED_UNICODE) . "<p/>";
    } else {
        //echo '<pre>';
        //print_r($ar_result);
        //echo '</pre>';
        echo json_encode($ar_result, JSON_UNESCAPED_UNICODE);
    }
} else {
    echo '<p>Скрипту переданы не все параметры. Пример запроса: <b>calc.php?city_to=Новосибирск&weight=10&volume=0.16&quantity=1</b><br>Для вывода результата в виде оформленного HTML используйте необязательный парметр <b>isHTMLResult=true</b></br></p>';
}
