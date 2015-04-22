<?php

$type = $_GET["type"];

header("Content-Type: application/".$type);

$method = $_SERVER['REQUEST_METHOD'];
if ($method == 'GET') {
    
    $out = file_get_contents($type . "_in." . $type);
    echo $out;
}
?>