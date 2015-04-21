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

        $result["sports_kinds"] = $result["sports_kinds"]["sports_kind"];
        $result["teams"] = $result["teams"]["team"];
        $result["participants"] = $result["participants"]["participant"];
        foreach ($result["participants"] as $key => $value) {
            if (count($value["teams"]["team_id"]) == 1)
                $result["participants"][$key]["teams"] = array($value["teams"]["team_id"]);
            else
                $result["participants"][$key]["teams"] = $value["teams"]["team_id"];
        }
        if (!parent::VerifyArray($result)) {
            trigger_error($this->lastError, E_USER_ERROR);
        }
        return $result;
    }

}

?>