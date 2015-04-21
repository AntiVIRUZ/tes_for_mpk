<?php

class LoadCompetition {
    
    
    
    //Тип сохранения - в базу данных или в CSV
    private $saveType;
    
    //Массив с распарсеными XML или JSON
    private $arr;
    
    //Соединение к БД MySQL
    private $mysqli;
    
    //Адресс, с которого подтягивается файл
    private $url;
    
    function __construct($fileType = "help", $saveType = "help", $url = "") {
        //Проверяем аргумент командной строки,
        //в зависимасти от него тянем XML или JSON
        $this->saveType = strtolower($saveType);
        
        //Если сохраняем в MySQL, то сразу пытаемся создать соединениеЫ
        if ($this->saveType == "db" || $this->saveType == "dbcsv") $this->ConnectToDB();
        
        $fileType = strtolower($fileType);

        //Если параметры не введены, или введены некорректно, выводим помощь
        $helpString = "Первый аргумент: \n".
            "    xml - получение xml файла\n".
            "    json - получение json файла\n".
            "Второй аргумент: \n".
            "    db - сохранение в базу данных\n".
            "    csv - сохранение в CSV файл\n".
            "    dbcsv - сохранение и в базу данных, и в CSV файл".
            "Третий аргумент (не обязательный):".
            "    <url> - адресс, с которого нужно загрузить файл XML или JSON";
        if ($this->saveType != "db" && $this->saveType != "csv" && $fileType != "xml" && $fileType != "json") {
            echo $this->StringToConsole($helpString);
        }

        //Проверим корректность второго аргумента

        if ($fileType == "xml") {
            if (!$url) $this->url = "http://mpk_server.local/get_xml.php";
            else $this->url = $url;
            $this->ProcessXML();
        } elseif ($fileType == "json") {
            if (!$url) $this->url = "http://mpk_server.local/get_json.php";
            else $this->url = $url;
            $this->ProcessJSON();
        }
    }

    private function StringToConsole($string) {
        //Меняем кодировку для корректного вывода кирилицы в консоль
        return iconv('utf-8', 'CP866', $string);
    }

    public function ProcessXML($saveType = "") {
        //Функция, инициализирующая загрузку и обработку XML документа
        //По умолчанию сохраняем в базу данных
        if ($saveType != "")
            $this->saveType = $saveType;
        $XMLString = $this->LoadFile();
        //Формируем из полученного документа ассоциативный массив
        $this->XMLToArray($XMLString);
        $this->ProcessString();
    }

    public function ProcessJSON($saveType = "") {
        //Функция, инициализирующая загрузку и обработку JSON документа
        //По умолчанию сохраняем в базу данных
        if ($saveType != "")
            $this->saveType = $saveType;
        $JSONString = $this->LoadFile();
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
        if ($this->saveType == "db") {
            $this->SaveToDB();
        } elseif ($this->saveType == "csv") {
            $this->SaveToCSV();
        } else {
            $this->SaveToDB();
            $this->SaveToCSV();
        }
    }

    private function LoadFile() {
        //Загружаем файл
        $response = file_get_contents($this->url);
        if ($response === FALSE){
            die($this->StringToConsole("Неверный URL"));
        }
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
        foreach ($this->arr["participants"] as $key => $value) {
            if (count($this->arr["participants"][$key]["teams"]["team_id"]) == 1)
                $this->arr["participants"][$key]["teams"] = array($this->arr["participants"][$key]["teams"]["team_id"]);
            else
                $this->arr["participants"][$key]["teams"] = $this->arr["participants"][$key]["teams"]["team_id"];
        }
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

        foreach ($this->arr["participants"] as $key => $value) {
            $unique_participant_to_team_id[$key] = array();
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
                if (isset($unique_participant_to_team_id[$key][$team_id]))
                    return "Участник с id " . $value["id"] . " дважды прикреплен к команде с id ". $team_id;
                else
                    $unique_participant_to_team_id[$key][$team_id] = 1;
            }
        }
        return "ok";
    }

    private function SaveToDB() {
        //Сохранение информации в базу данных
        //Добавляем записи в таблицы
        //Формирую инсерт харкодом, так как при такой малой таблице и конкретной задаче
        //делать расширяемый код общего вида, так сказать, считаю неэффективным
        
        $this->ClearTables();
        
        $sql = "INSERT INTO sports_kinds (id, name)\n" .
                "VALUES\n";
        
        //Запоминаем количество записей, чтобы после последней поставить ;
        $recordsCount = count($this->arr["sports_kinds"]);
        foreach ($this->arr["sports_kinds"] as $key => $value){
            $sql .= "(".$value["id"].", '".$value["name"]."')";
            if ($key + 1 == $recordsCount)
                $sql .= ";\n";
            else
                $sql .= ",\n";
        }
        $this->SentQuery($sql);
        
        $sql = "INSERT INTO teams (id, name, sports_kind_id)\n" .
                "VALUES\n";
        
        $recordsCount = count($this->arr["teams"]);
        foreach ($this->arr["teams"] as $key => $value){
            $sql .= "(".$value["id"].", '".$value["name"]."', ".$value["sports_kind_id"].")";
            if ($key + 1 == $recordsCount)
                $sql .= ";\n";
            else
                $sql .= ",\n";
        }
        $this->SentQuery($sql);
        
        $sql = "INSERT INTO participants (id, name)\n" .
                "VALUES\n";
        
        $recordsCount = count($this->arr["participants"]);
        foreach ($this->arr["participants"] as $key => $value){
            $sql .= "(".$value["id"].", '".$value["name"]."')";
            if ($key + 1 == $recordsCount)
                $sql .= ";\n";
            else
                $sql .= ",\n";
        }
        $this->SentQuery($sql);
        
        $sql = "INSERT INTO participants_teams (id, participant_id, team_id)\n" .
                "VALUES\n";
        
        $i = 1;
        foreach ($this->arr["participants"] as $key => $value)
        foreach ($value["teams"] as $team_key => $team_value) {
            $sql .= "(".$i.", ".$value["id"].", ".$team_value."),\n";
            $i++;
        }
        $sql[strlen($sql)-2] = ";";
        $this->SentQuery($sql);
    }
    
    private function ClearTables() {
        //Очистим таблицы, если там есть данные
        $sql = "DELETE FROM participants_teams;\n";
        $this->SentQuery($sql);
        
        $sql = "DELETE FROM participants;\n";
        $this->SentQuery($sql);
        
        $sql = "DELETE FROM teams;\n";
        $this->SentQuery($sql);
        
        $sql = "DELETE FROM sports_kinds;\n";
        $this->SentQuery($sql);
    }
         
    private function SentQuery($sql) {
        if ($this->mysqli->query($sql) === FALSE)
            die($this->StringToConsole($this->mysqli->error));
    }
    
    private function SaveToCSV() {
        //Сохраняем информацию в CSV файл
        //Сохраняю в 4 различных файла аналогично таблицам базы данных
        $SKfile = fopen("sports_kinds.csv", "w");
        foreach ($this->arr["sports_kinds"] as $key => $value){
            fwrite($SKfile, $value["id"].";\"".$value["name"]."\"\n");
        }
        fclose($SKfile);
        
        $Tfile = fopen("teams.csv", "w");
        foreach ($this->arr["teams"] as $key => $value){
            fwrite($Tfile, $value["id"].";\"".$value["name"]."\",".$value["sports_kind_id"]."\n");
        }
        fclose($Tfile);
        
        $Pfile = fopen("participants.csv", "w");
        foreach ($this->arr["participants"] as $key => $value){
            fwrite($Pfile, $value["id"].";\"".$value["name"]."\"\n");
        }
        fclose($Pfile);
        
        $PTfile = fopen("participants to teams.csv", "w");
        foreach ($this->arr["participants"] as $key => $value)
        foreach ($value["teams"] as $team_value) {
            fwrite($PTfile, $value["id"].";".$team_value."\n");
        }
        fclose($PTfile);
    }
    
    private function ConnectToDB() {
        $servername = "localhost";
        $username = "root";
        $password = "toor"; 
        
        // Создаем соединение
        $this->mysqli = new mysqli($servername, $username, $password);
        
        //Проверяем соединение
        if ($this->mysqli->connect_error) {
            die("Connection failed: " . $this->mysqli->connect_error);
        }
        
        $this->mysqli->select_db("mpk_test");
    }
}

//Инициализируем класс, получая из командной строки аргумент обработки XML или JSON
//По умолчанию загружаем XML и соханяем в DB
if ($argc < 3) {
    $class = new LoadCompetition();
} elseif ($argc == 3) {
    $class = new LoadCompetition($argv[1], $argv[2]);
} else {
    $class = new LoadCompetition($argv[1], $argv[2], $argv[3]);
}
?>