<?php
require_once 'nrg_helper.php';
require_once 'dellin_helper.php';
require_once 'kit_helper.php';

$city_to = filter_input(INPUT_GET, 'city_to');
$weight = filter_input(INPUT_GET, 'weight');
$volume = filter_input(INPUT_GET, 'volume');
$quantity = filter_input(INPUT_GET, '$quantity');

//if(empty($_GET["city_to"]) || empty($_GET["weight"]) || empty($_GET["volume"]) || empty($_GET["place"])) {
if (empty($city_to) || empty($weight) || empty($volume) || empty($place)) {
    $result["status"] = "err";
    $result["text"] = "Введены не все данные";
} else {
    $ar_NRG_result = NRG_Calculate("Самара", $city_to, $weight, $volume, $quantity);
    $ar_DELLIN_result = DELLIN_Calculate("Самара", $city_to, $weight, $volume, $quantity);
    $result = array_merge_recursive($ar_NRG_result, $ar_DELLIN_result);
}
//echo json_encode($result);
//echo "<p>NRG:<br/>" . json_encode(NRG_Calculate("Самара", "Москва", 10, 0.16, 1), JSON_UNESCAPED_UNICODE) . "<p/>";
//echo "<p>Dellin:<br/>" . json_encode(DELLIN_Calculate("Самара", "Москва", 10, 0.16, 1), JSON_UNESCAPED_UNICODE) . "<p/>";
//echo "<p>KIT:<br/>" . json_encode(KIT_Calculate("Самара", "Москва", 10, 0.16, 1), JSON_UNESCAPED_UNICODE) . "<p/>";
////Create Database connection

$mysqli = new mysqli('localhost', 'root', '', 'dbcalc');
if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}
/* Select запросы возвращают результирующий набор */
mysqli_query($mysqli, "SET NAMES utf8");
//if ($result = $mysqli->query("SELECT searchString, name FROM cls_cities where searchString like 'Сама%' and code like '%00000000000000000' limit 100")) {
$searchstring = 'ниж';
if ($result = $mysqli->query("SELECT code,name FROM `class_okato` WHERE name LIKE '%" . $searchstring . "%' and substring(code,3,1)=4 and substring(code,4,2)!=00 and length(code)<8")) {
    printf("Select вернул %d строк.\n", $result->num_rows);

    //$data=  mysqli_fetch_assoc($result);
    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
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




//
//    $db = mysql_connect("localhost","root","");
//    if (!$db) {
//        die('Could not connect to db: ' . mysql_error());
//    }
//echo 'Успешно соединились';
////    //Select the Database
//    mysql_select_db("dbcalc",$db);
////    //Replace * in the query with the column names.
//    $result = mysql_query("select count(*) as count from cls_cities", $db);
//    
//    //echo mysql_num_rows($result);
//    $data=mysql_fetch_assoc($result);
//    echo $data['count'];
////    //Create an array
////    $json_response = array();
////    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
////        $row_array['id_employee'] = $row['id_employee'];
////        $row_array['emp_name'] = $row['emp_name'];
////        $row_array['designation'] = $row['designation'];
////        $row_array['date_joined'] = $row['date_joined'];
////        $row_array['salary'] = $row['salary'];
////        $row_array['id_dept'] = $row['id_dept'];
////         
////        //push the values in the array
////        array_push($json_response,$row_array);
////    }
////    echo json_encode($json_response);
////     
////    //Close the database connection
//    fclose($db);