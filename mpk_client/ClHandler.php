<?php

class ClHandler {

    public $inputType;
    public $destination;
    public $url;
    
    private $argv;
    private $inputTypes = array("xml" => 1, "json" => 1);
    private $destinations = array("db" => 1, "csv" => 1, "dbcsv" => 1);
    
    function __construct() {
    }
    
    public function setNewArguments($argv) {
        $this->argv = $argv;
        if (!$this->CheckCount() ) {
            return FALSE;
        }
        
        $this->PlaceArguments();
        
        if ($this->CheckInputType() && $this->CheckDestination()) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    private function CheckCount() {
        $argc = count($this->argv);
        if ($argc < 4) {
            $this->ShowHelp();
            return FALSE;
        } else {
            return TRUE;
        }
    }
    
    private function PlaceArguments() {
        $this->inputType    = strtolower($this->argv[1]);
        $this->destination  = strtolower($this->argv[2]);
        $this->url          = strtolower($this->argv[3]);
    }
    
    private function CheckInputType() {
        if (!isset($this->inputTypes[$this->inputType])) {
            $this->ShowHelp();
            return FALSE;
        } else {
            return TRUE;
        }
    }
    
    private function CheckDestination() {
        if (!isset($this->destinations[$this->destination])) {
            $this->ShowHelp();
            return FALSE;
        } else {
            return TRUE;
        }
    }
    
    private function ShowHelp() {
        $helpString = "Первый аргумент: \n".
            "    xml - получение xml файла\n".
            "    json - получение json файла\n".
            "Второй аргумент: \n".
            "    db - сохранение в базу данных\n".
            "    csv - сохранение в CSV файл\n".
            "    dbcsv - сохранение и в базу данных, и в CSV файл".
            "Третий аргумент (не обязательный):".
            "    <url> - адресс, с которого нужно загрузить файл XML или JSON";
        echo iconv('utf-8', 'CP866', $helpString);
    }

}