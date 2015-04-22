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
        $array = json_decode($json, true);
        $array = $this->MakeResultLikeJSON($array);
        $result = parent::GetTeamsFromArray($array);
       
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