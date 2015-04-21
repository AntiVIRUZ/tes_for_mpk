<?php

include_once 'ParserAbstract.php';
include_once 'FilesLoader.php';

class JSONParser extends ParserAbstract {
    
    public function ParseFromString($xmlString) {
        $result = json_decode($xmlString, true);
        if ($result === null) {
            trigger_error("Ошибка, неверно сформирован полученный файл\nJSON имеет синтаксические ошибки", E_USER_ERROR);
        }
        
        if (!parent::VerifyArray($result)) {
            trigger_error($this->lastError, E_USER_ERROR);
        }
        
        return $result;
    }
    
}