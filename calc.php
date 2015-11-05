<?php

require_once 'nrg_helper.php';
require_once 'dellin_helper.php';
require_once 'kit_helper.php';
require_once 'pec_helper.php';
require_once 'jde_helper.php';

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

/**
 * Функция форматирует результат от калькулятора ТК в читабельном HTML
 * @param string $title Название ТК
 * @param string $city_to Город, куда отправляем
 * @param Array $ar_calc_result Массив ответа от калькулятора ТК
 * @return string
 */
function CalcResultToHTML($title, $city_to, $ar_calc_result) {
//пример ответа от ТК - Array ( [status] => ok [cost] => 1208 [minDays] => 5 [maxDays] => 5 [pickupCost] => 250 [additionalInfo] => Array ( [1] => Терминала в Нягань нет. Стоимость перевозки до Ханты-Мансийск: 408 + доставка до Нягань: 800. [2] => Страховка груза: 75 руб. [4] => Возможна доставка самолетом: 6752 руб. ) )
    $cost = array_key_exists('cost', $ar_calc_result) ? $ar_calc_result['cost'] : '-';
    $minDays = array_key_exists('minDays', $ar_calc_result) ? $ar_calc_result['minDays'] : '-';
    $maxDays = array_key_exists('maxDays', $ar_calc_result) ? $ar_calc_result['maxDays'] : '-';
    $pickupCost = array_key_exists('pickupCost', $ar_calc_result) ? $ar_calc_result['pickupCost'] : '-';
    $deliveryCost = array_key_exists('deliveryCost', $ar_calc_result) ? $ar_calc_result['deliveryCost'] : '-';

    $result = "";
    if ($cost == '-') {
        $result = "$result<font color='gray'>";
    }
    $result = "$result<p>" . $title . ": <b>" . $cost . " руб.</b><p/>";

    $response = json_encode($ar_calc_result, JSON_UNESCAPED_UNICODE);
    $result = "$result<ul title='response is: $response'>";

    $result = $result . "<li>Дней: $minDays - $maxDays</li>";
    $result = $result . "<li>Забор груза в Самаре: $pickupCost руб.</li>";
    $result = $result . "<li>Доставка в $city_to: $deliveryCost руб.</li>";
    if (array_key_exists('additionalInfo', $ar_calc_result)) {
        if ($ar_calc_result['additionalInfo'] > 0) {
            //$result = "$result<p>Дополнительно</p>";
            foreach ($ar_calc_result['additionalInfo'] as $row) {
                $result = $result . "<li>$row</li>";
            }
        }
    }
    $result = "$result</ul>";
    if ($cost == '-') {
        $result = "$result</font>";
    }

    return $result;
}

if (!is_null($city_to) and ! is_null($weight) and ! is_null($volume) and ! is_null($quantity)) {
    $ar_NRGResult = NRG_Calculate("Самара", $city_to, $weight, $volume, $quantity);
    $ar_DELLINResult = DELLIN_Calculate("Самара", $city_to, $weight, $volume, $quantity);
    $ar_PECResult = PEC_Calculate("Самара", $city_to, $weight, $volume, $quantity);
    $ar_KITResult = KIT_Calculate("Самара", $city_to, $weight, $volume, $quantity);
    $ar_JDEResult = JDE_Calculate("Самара", $city_to, $weight, $volume, $quantity);

    if ($isHTMLResult) {
        echo "<h3>Доставка в Новосибирск</h3>";
        echo CalcResultToHTML("Энергия", $city_to, $ar_NRGResult);
        echo CalcResultToHTML("Деловые Линии", $city_to, $ar_DELLINResult);
        echo CalcResultToHTML("ПЭК", $city_to, $ar_PECResult);
        echo CalcResultToHTML("КИТ", $city_to, $ar_KITResult);
        echo CalcResultToHTML("ЖелДорЭкспедиция", $city_to, $ar_JDEResult);
    } else {
        //echo '<pre>';
        //print_r($ar_result);
        //echo '</pre>';
        $ar_result['nrg'] = $ar_NRGResult;
        $ar_result['dellin'] = $ar_DELLINResult;
        $ar_result['pec'] = $ar_PECResult;
        $ar_result['kit'] = $ar_KITResult;
        $ar_result['jde'] = $ar_JDEResult;
        echo json_encode($ar_result, JSON_UNESCAPED_UNICODE);
    }
} else {
    echo '<p>Скрипту переданы не все параметры. Пример запроса: <b>calc.php?city_to=Новосибирск&weight=10&volume=0.16&quantity=1</b><br>Для вывода результата в виде оформленного HTML используйте необязательный парметр <b>isHTMLResult=true</b></br></p>';
}
