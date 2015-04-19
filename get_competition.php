<?php

class LoadCompetition {
    
    private $safeType;
    private $arr;

    function __construct($fileType = "help", $safeType = "help") {
        //Проверяем аргумент командной строки,
        //в зависимасти от него тянем XML или JSON
        $this->safeType = strtolower($safeType);
        $fileType = strtolower($fileType);

        //Если параметры не введены, или введены некорректно, выводим помощь
        if ($this->safeType != "db" && $this->safeType != "csv" && $fileType != "xml" && $fileType != "json") {
            echo $this->StringToConsole("Первый аргумент: \n");
            echo $this->StringToConsole("    xml - получение xml файла\n");
            echo $this->StringToConsole("    json - получение json файла\n");
            echo $this->StringToConsole("Второй аргумент: \n");
            echo $this->StringToConsole("    db - сохранение в базу данных\n");
            echo $this->StringToConsole("    csv - сохранение в CSV файл\n");
            echo $this->StringToConsole("    dbcsv - сохранение и в базу данных, и в CSV файл");
        }

        //Проверим корректность второго аргумента

        if ($fileType == "xml") {
            $this->ProcessXML();
        } elseif ($fileType == "json") {
            $this->ProcessJSON();
        }
    }

    private function StringToConsole($string) {
        //Меняем кодировку для корректного вывода кирилицы в консоль
        return iconv('utf-8', 'CP866', $string);
    }

    public function ProcessXML($safeType = "") {
        //Функция, инициализирующая загрузку и обработку XML документа
        //По умолчанию сохраняем в базу данных
        if ($safeType != "")
            $this->safeType = $safeType;
        $XMLString = $this->LoadXML();
        //Формируем из полученного документа ассоциативный массив
        $this->XMLToArray($XMLString);
        $this->ProcessString();
    }

    public function ProcessJSON($safeType = "") {
        //Функция, инициализирующая загрузку и обработку JSON документа
        //По умолчанию сохраняем в базу данных
        if ($safeType != "")
            $this->safeType = $safeType;
        $JSONString = $this->LoadJSON();
        //Формируем из полученного документа ассоциативный массив
        $this->JSONToArray($JSONString);
        $this->ProcessString();
    }

    private function ProcessString() {
        $error_str = $this->VerifyArray();
        if ($error_str != "ok") {
            echo $this->StringToConsole("Ошибка, неверно сформирован полученный файл\n" . $error_str);
            die;
        }
        die;
        if ($this->safeType == "db") {
            SafeToDB();
        } elseif ($this->safeType == "csv") {
            SafeToCSV();
        } else {
            SafeToDB();
            SafeToCSV();
        }
    }

    private function LoadXML() {
        //Загружаем XML файл
        $url = "http://mpk_server.local/get_xml.php";
        $response = file_get_contents($url);
        return $response;
    }

    private function LoadJSON() {
        //Загружаем JSON файл
        $url = "http://mpk_server.local/get_json.php";
        $response = file_get_contents($url);
        return $response;
    }

    private function XMLToArray($string) {
        //Парсим полученный XML в ассоциативный массив
        //Запаковать в JSON а потом достать из него - самый простой способ получить массив,
        //с такой же структурой, как при парсинге чистого JSON'a
        try {
            $xml = new SimpleXMLElement($string);
        } catch (Exception $exc) {
            echo $this->StringToConsole("Ошибка, неверно сформирован полученный файл\nXML имеет синтаксические ошибки");
            die;
        }
        $json = json_encode($xml);
        $this->arr = json_decode($json, true);
        if ($this->arr === NULL) {
            echo $this->StringToConsole("Ошибка, неверно сформирован полученный файл\nXML имеет синтаксические ошибки");
            die;
        }

        //Избавляемся от излишней вложенности, появляющейся из-за особенностей построения XML
        $this->arr["sports_kinds"] = $this->arr["sports_kinds"]["sports_kind"];
        $this->arr["teams"] = $this->arr["teams"]["team"];
        $this->arr["participants"] = $this->arr["participants"]["participant"];
    }

    private function JSONToArray($string) {
        //Парсим полученный JSON в ассоциативный массив
        $this->arr = json_decode($string, true);
        if ($this->arr === null) {
            echo $this->StringToConsole("Ошибка, неверно сформирован полученный файл\nJSON имеет синтаксические ошибки");
            die;
        }
    }

    private function VerifyArray() {
        //Функция, проверяющая корректность сформированного массива
        $unique_sports_id = array();
        $unique_teams_id = array();
        $unique_participant_id = array();
        $unique_participant_to_team_id = array();

        //Проверяем уникальность ID и наличие данных в каждом поле
        foreach ($this->arr["sports_kinds"] as $value) {
            if (!isset($value["id"]))
                return "Есть вид спорта без id";
            if (isset($unique_sports_id[$value["id"]])) {
                return "Среди видов спорта есть неуникальный id (" . $value["id"] . ")";
            } else
                $unique_sports_id[$value["id"]] = 1;
            if (!$value["name"])
                return "У вида спорта с id " . $value["id"] . " нет имени";
        }

        foreach ($this->arr["teams"] as $value) {
            if (!isset($value["id"]))
                return "Есть команда без id";
            if (isset($unique_teams_id[$value["id"]])) {
                return "Среди команд есть неуникальный id (" . $value["id"] . ")";
            } else
                $unique_teams_id[$value["id"]] = 1;
            if (!$value["name"])
                return "У команды с id " . $value["id"] . " нет имени";
            if (!$value["sports_kind_id"])
                return "Для команды с id " . $value["id"] . " не закреплен вид спорта";
            if (!isset($unique_sports_id[$value["sports_kind_id"]]))
                return "Для команды с id " . $value["id"] . " не существует вида спорта с id = " . $value["sports_kind_id"];
        }

        foreach ($this->arr["participants"] as $value) {
            if (!isset($value["id"]))
                return "Есть учасник без id";
            if (isset($unique_participant_id[$value["id"]])) {
                return "Среди команд есть неуникальный id (" . $value["id"] . ")";
            } else
                $unique_participant_id[$value["id"]] = 1;
            if (!$value["name"])
                return "У участника с id " . $value["id"] . " нет имени";
            if (count($value["teams"]) == 0)
                return "Участник с id " . $value["id"] . " не закреплен ни за одной командой";
            foreach ($value["teams"] as $team_id) {
                if ($team_id == "")
                    return "Среди команд участника с id " . $value["id"] . "есть пустые записи";
                if (!isset($unique_teams_id[$team_id]))
                    return "Участник с id " . $value["id"] . " закреплен за несуществующей командой с id " . $team_id;
                if (isset($unique_participant_to_team_id[$team_id]))
                    return "Участник с id " . $value["id"] . " дважды прикреплен к команде с id ". $team_id;
                else
                    $unique_participant_to_team_id[$team_id] = 1;
            }
        }
        return "ok";
    }

    private function SafeToDB() {
        //Сохранение информации в базу данных
    }

    private function SafeToCSV() {
        //Сохраняем информацию в CSV файл
    }

}

//Инициализируем класс, получая из командной строки аргумент обработки XML или JSON
//По умолчанию загружаем XML и соханяем в DB
if ($argc == 1) {
    $class = new LoadCompetition();
} elseif ($argc == 2) {
    $class = new LoadCompetition($argv[1]);
} else {
    $class = new LoadCompetition($argv[1], $argv[2]);
}
?>