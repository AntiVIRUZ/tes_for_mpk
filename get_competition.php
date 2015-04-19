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
        $this->arr = $this->XMLToArray($XMLString);
        $this->ProcessString();
    }

    public function ProcessJSON($safeType = "") {
        //Функция, инициализирующая загрузку и обработку JSON документа
        //По умолчанию сохраняем в базу данных
        if ($safeType != "")
            $this->safeType = $safeType;
        $JSONString = $this->LoadJSON();
        //Формируем из полученного документа ассоциативный массив
        $this->arr = $this->JSONToArray($JSONString);
        $this->ProcessString();
    }

    private function ProcessString() {
        if (!$this->VerifyArray()) {
            echo $this->StringToConsole("Ошибка, неверно сформирован массив\nПолученный файл был отформатирован или заполнен данными некорректно\n");
            die;
        }
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
    }

    private function JSONToArray($string) {
        //Парсим полученный JSON в ассоциативный массив
    }

    private function VerifyArray() {
        //Функция, проверяющая корректность сформированного массива
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