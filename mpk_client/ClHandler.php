<?php

/**
 * Класс для обработки аргументов командной строки
 * 
 * Добавляет поддержку запуска скрипта из командной строки
 * @author Vasiliy Yatsevitch <zwtdbx@yandex.ru>
 */
class ClHandler {

    /**
     * Тип входного формата для получения спика участников (XML или JSON)
     * @access public
     * @var string
     */
    public $inputType;
    /**
     * путь сохранения (db или csv)
     * @access public
     * @var sting
     */
    public $destination;
    /**
     * URL для загрузки файла участников
     * @access public
     * @var string 
     */
    public $url;
    
    /**
     * Массив аргументов коммандной строки
     * @access private
     * @var array
     */
    private $argv;
    /**
     * Предопределенный массив доступных типов входных форматов
     * @access private
     * @var array 
     */
    private $inputTypes = array("xml" => 1, "json" => 1);
    /**
     * Предопределенный массив доступных путей сохранения
     * @access private
     * @var array 
     */
    private $destinations = array("db" => 1, "csv" => 1, "dbcsv" => 1);
    
    
    /**
     * Устанавливает новые аргументы
     * 
     * Обрабатывает массив $argv и в зависимости от его содержамого
     * выводит справку или устанавливает новые значения в публичные поля
     * @access public
     * @param type $argv Массив аргументов командной строки
     * @return boolean TRUE если аргументы достаточны и корректны, FALSE в ином случае
     */
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
    
    /**
     * Проверяет достаточность количества аргументов
     * 
     * Выводит справку, если аргументов недостаточное количество
     * @access private
     * @return boolean TRUE если число аргументов достаточно, FALSE в противном случае
     */
    private function CheckCount() {
        $argc = count($this->argv);
        if ($argc < 4) {
            $this->ShowHelp();
            return FALSE;
        } else {
            return TRUE;
        }
    }
    
    /**
     * Размещает аргументы командной строки в публичные поля
     * @access private
     */
    private function PlaceArguments() {
        $this->inputType    = strtolower($this->argv[1]);
        $this->destination  = strtolower($this->argv[2]);
        $this->url          = strtolower($this->argv[3]);
    }
    
    /**
     * Проверяет корректность аргумента типа входного файла
     * 
     * Выводит справку, если аргумент некорректен
     * @access private
     * @return boolean TRUE если аргумент корректен, FALSE в противном случае
     */
    private function CheckInputType() {
        if (!isset($this->inputTypes[$this->inputType])) {
            $this->ShowHelp();
            return FALSE;
        } else {
            return TRUE;
        }
    }
    
    /**
     * Проверяет корректность аргумента пути сохранения данных
     * 
     * Выводит справку, если аргумент некорректен
     * @access private
     * @return boolean TRUE если аргумент корректен, FALSE в противном случае
     */
    private function CheckDestination() {
        if (!isset($this->destinations[$this->destination])) {
            $this->ShowHelp();
            return FALSE;
        } else {
            return TRUE;
        }
    }
    
    /**
     * Выводит справку в командную строку
     * @access private
     */
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