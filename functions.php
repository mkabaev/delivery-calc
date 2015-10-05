<?php

function curl_get_contents($options) {
    $ch = curl_init();
    curl_setopt_array($ch, $options);
    $data = curl_exec($ch);
    // ???curl_multi_getcontent($ch)
    //var_dump(curl_getinfo($ch));
    curl_close($ch);
    return $data;
}

function GetResponse_get($url_request) {
    $curl_options = [CURLOPT_URL => $url_request,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_PROXY => '10.254.30.3:8080',
        CURLOPT_PROXYUSERPWD => 'eame\maxim_kabaev:RJHJKMfhneh17'
    ];
    return curl_get_contents($curl_options);
}

function GetResponse_post($url_request, $ar_request) {
    $curl_options = [CURLOPT_URL => $url_request,
        CURLOPT_RETURNTRANSFER => 1,
        //CURLOPT_HEADER => 0,
        CURLOPT_PROXY => '10.254.30.3:8080',
        CURLOPT_PROXYUSERPWD => 'eame\maxim_kabaev:RJHJKMfhneh17',
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        //CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => json_encode($ar_request),
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_SSL_VERIFYHOST => 0,
            //CURLOPT_HTTPHEADER => array('Expect:')
    ];
    return curl_get_contents($curl_options);
}

function PrepareReponseArray($responseStatus, $cost_at, $minDays_at, $maxDays_at, $cost_av, $minDays_av, $maxDays_av, $cost_rw, $minDays_rw, $maxDays_rw, $pickupCost, $deliveryCost, $additionalInfo) {
    $arr_auto = [
        "cost" => $cost_at,
        "minDays" => $minDays_at,
        "maxDays" => $maxDays_at,
    ];

    $arr_avia = [
        "cost" => $cost_av,
        "minDays" => $minDays_av,
        "maxDays" => $maxDays_av,
    ];

    $arr_rw = [
        "cost" => $cost_rw,
        "minDays" => $minDays_rw,
        "maxDays" => $maxDays_rw,
    ];

    $result = ["status" => $responseStatus];
    if ($cost_at>0) {
        $result['auto']=$arr_auto;
    }
    if ($cost_av>0) {
        $result['avia']=$arr_avia;
    }
    if ($cost_rw>0) {
        $result['rw']=$arr_rw;
    }
    if ($additionalInfo!="") {
        $result['addinfo']=$additionalInfo;
    }
    return $result;
}
