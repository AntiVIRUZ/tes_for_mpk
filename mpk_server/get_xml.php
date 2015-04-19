<?php

header("Content-Type: application/xml");
//Проверяем, что получили GET
$method = $_SERVER['REQUEST_METHOD'];
if ($method == 'GET') {
    //Загружаем XML из файла и отправляем получателю
    $out = file_get_contents("xml_out.txt");
    echo $out;
}
?>