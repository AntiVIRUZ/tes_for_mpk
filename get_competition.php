<?php

class LoadCompetition {

    //Код не является конечным, а лишь отражает логику работы программы
    
    private $safeType;

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
        $XMLString = LoadXML();
        $arr = XMLToArray($XMLString);
        ProcessString($arr);
        if ($safeType != "")
            $this->safeType = $safeType;
    }

    public function ProcessJSON($safeType = "") {
        //Функция, инициализирующая загрузку и обработку JSON документа
        //По умолчанию сохраняем в базу данных
        $JSONString = LoadJSON();
        $arr = JSONToArray($JSONString);
        ProcessString($arr);
        if ($safeType != "")
            $this->safeType = $safeType;
    }
    
    private function ProcessString ($arr) {
        if (!VerifyArray($arr)) echo "Ошибка";
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
    }

    private function LoadJSON() {
        //Загружаем JSON файл
    }

    private function XMLToArray($string) {
        //Парсим полученный XML в ассоциативный массив
    }

    private function JSONToArray($string) {
        //Парсим полученный JSON в ассоциативный массив
    }

    private function VerifyArray($arr) {
        //Функция, проверяющая корректность сформированного массива
    }
    
    private function SafeToDB($arr) {
        //Сохранение информации в базу данных
    }
    
    private function SafeToCSV($arr) {
        //Сохраняем информацию в CSV файл
    }
}

//Инициализируем класс, получая из командной строки аргумент обработки XML или JSON
//По умолчанию загружаем XML и соханяем в DB
if ($argc == 1) {
    $class = new LoadCompetition();
} 
elseif ($argc == 2) {
    $class = new LoadCompetition($argv[1]);
}
else {
    $class = new LoadCompetition($argv[1], $argv[2]);
}


?>