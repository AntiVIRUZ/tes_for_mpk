<?php

class LoadCompetition {

    //Код не является конечным, а лишь отражает логику работы программы
    
    private $safeType;
            
    function __construct($fileType="XML", $safeType="DB") {
        //Проверяем аргумент командной строки,
        //в зависимасти от него тянем XML или JSON
        $this->safeType = $safeType;
        if ($fileType == "XML") ProcessXML();
        if ($fileType == "JSON") ProcessJSON();
    }
    
    public function ProcessXML($safeType = "") {
        //Функция, инициализирующая загрузку и обработку XML документа
        //По умолчанию сохраняем в базу данных
        if ($safeType != "") $this->safeType = $safeType;
        $XMLString = LoadXML();
        $arr = XMLToArray($XMLString);
        ProcessString($arr);
    }

    public function ProcessJSON($safeType = "") {
        //Функция, инициализирующая загрузку и обработку JSON документа
        //По умолчанию сохраняем в базу данных
        if ($safeType != "") $this->safeType = $safeType;
        $JSONString = LoadJSON();
        $arr = JSONToArray($JSONString);
        ProcessString($arr);
    }
    
    private function ProcessString ($arr) {
        if (!VerifyArray($arr)) echo "Ошибка";
        if ($this->safeType == "DB") {
            SafeToDB($arr);
        } elseif ($this->safeType == "CSV") {
            SafeToCSV($arr);
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