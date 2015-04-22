<?php

include_once 'ParserAbstract.php';
include_once 'FilesLoader.php';

class XMLParser extends ParserAbstract {

    public function ParseFromString($xmlString) {
        try {
            $xml = new SimpleXMLElement($xmlString);
        } catch (Exception $exc) {
            trigger_error("Ошибка, неверно сформирован полученный файл. XML имеет синтаксические ошибки", E_USER_ERROR);
            return FALSE;
        }
        $json = json_encode($xml);
        $result = json_decode($json, true);
        $result = $this->MakeResultLikeJSON($result);
        print_r($result);
        if (!parent::VerifyArray($result)) {
            trigger_error($this->lastError, E_USER_ERROR);
        }
        
        return $result;
    }
    
    private function MakeResultLikeJSON($result) {
        foreach ($result["team"] as $key => $team) {
            $result["team"][$key]["members"] = $team["members"]["member"];
        }
        $result["teams"] = $result["team"];
        unset($result["team"]);
        return $result;
    }

}

?>