<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <?php
        require_once 'nrg_helper.php';
        echo '<pre>';
        $start = microtime(true);
        $places = '{"weight": 10,"width": 0.4,"height": 0.4,"length": 1},{"weight": 10,"width": 0.4,"height": 0.4,"length": 1}';
        print_r(NRG_Calculate('Самара', 'Москва', $places));
        echo "Время выполнения скрипта: " . (microtime(true) - $start);
        echo '</pre>';
        ?>
    </body>
</html>
