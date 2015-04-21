<?php

include 'ParserAbstract.php';
include 'FilesLoader.php';

class JSONParser {
    
    public function parseFromString($xmlString) {
        $result = json_decode($xmlString, true);
        if ($result === null) {
            trigger_error("Ошибка, неверно сформирован полученный файл\nJSON имеет синтаксические ошибки");
        }
        
        if (!VerifyArray($result)) {
            trigger_error($this->lastError, E_USER_ERROR);
        }
        
        return $result;
    }
    
}