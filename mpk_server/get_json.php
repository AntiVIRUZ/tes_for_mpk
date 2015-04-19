<?php

header("Content-Type: application/json");
//Проверяем, что получили GET
$method = $_SERVER['REQUEST_METHOD'];
if ($method == 'GET') {
    //Загружаем JSON из файла и отправляем получателю
    $out = file_get_contents("json_out.txt");
    echo $out;
}
?>