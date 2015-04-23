<?php

/**
 * Класс для парсинга XML файла
 * 
 * Разбивает переданную строку JSON в массив команд (Team)
 * @author Vasiliy Yatsevitch <zwtdbx@yandex.ru>
 */

include_once 'ParserAbstract.php';
include_once 'FilesLoader.php';

class XMLParser extends ParserAbstract {

    /**
     * Парсит XML строку в массив команд
     * @access public
     * @param string $xmlString
     * @return array Массив экземпляров класса Team
     */
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
    
    /**
     * Приводит ассоциативный массив с данными участников команд к виду,
     * аналогичному получаемому из JSON
     * @access private
     * @param array $result Массив, получаемый напрямую из XML
     * @return array Массив, эквивалентный по структуре получаемому из JSON
     */
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