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

if (!is_null($city_to) and ! is_null($weight) and ! is_null($volume) and ! is_null($quantity)) {
    $ar_NRGResult = NRG_Calculate("Самара", $city_to, $weight, $volume, $quantity);
    $ar_DELLINResult = DELLIN_Calculate("Самара", $city_to, $weight, $volume, $quantity);
    $ar_PECResult = PEC_Calculate("Самара", $city_to, $weight, $volume, $quantity);
    $ar_KITResult = KIT_Calculate("Самара", $city_to, $weight, $volume, $quantity);
    $ar_JDEResult = JDE_Calculate("Самара", $city_to, $weight, $volume, $quantity);

    if ($isHTMLResult) {
        $nrg_cost = array_key_exists('cost', $ar_NRGResult) ? $ar_NRGResult['cost'] : '-';
        $dellin_cost = array_key_exists('cost', $ar_DELLINResult) ? $ar_DELLINResult['cost'] : '-';
        $pec_cost = array_key_exists('cost', $ar_PECResult) ? $ar_PECResult['cost'] : '-';
        $kit_cost = array_key_exists('cost', $ar_KITResult) ? $ar_KITResult['cost'] : '-';
        $jde_cost = array_key_exists('cost', $ar_JDEResult) ? $ar_JDEResult['cost'] : '-';
        echo '<h3>Доставка в ' . $city_to . '</h3>';

        if ($nrg_cost == '-') {
            echo "<font color='gray'>";
        }
        echo "<p>Энергия: <b>" . $nrg_cost . " руб.</b><br/>" . json_encode($ar_NRGResult, JSON_UNESCAPED_UNICODE) . "<p/>";
        if ($nrg_cost = '-') {
            echo "</font>";
        }

        if ($dellin_cost == '-') {
            echo "<font color='gray'>";
        }
        echo "<p>Деловые Линии: <b>" . $dellin_cost . " руб.</b><br/>" . json_encode($ar_DELLINResult, JSON_UNESCAPED_UNICODE) . "<p/>";
        if ($dellin_cost = '-') {
            echo "</font>";
        }

        if ($pec_cost == '-') {
            echo "<font color='gray'>";
        }
        echo "<p>ПЭК: <b>" . $pec_cost . " руб.</b><br/>" . json_encode($ar_PECResult, JSON_UNESCAPED_UNICODE) . "<p/>";
        if ($pec_cost = '-') {
            echo "</font>";
        }

        if ($kit_cost == '-') {
            echo "<font color='gray'>";
        }
        echo "<p>КИТ: <b>" . $kit_cost . " руб.</b><br/>" . json_encode($ar_KITResult, JSON_UNESCAPED_UNICODE) . "<p/>";
        if ($kit_cost = '-') {
            echo "</font>";
        }

        if ($jde_cost == '-') {
            echo "<font color='gray'>";
        }
        echo "<p>ЖелДорЭкспедиция: <b>" . $jde_cost . " руб.</b><br/>" . json_encode($ar_JDEResult, JSON_UNESCAPED_UNICODE) . "<p/>";
        if ($jde_cost = '-') {
            echo "</font>";
        }
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
