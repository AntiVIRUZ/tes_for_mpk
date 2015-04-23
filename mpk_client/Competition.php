<?php

/**
 * Основной класс, представляющий соревнование.
 * 
 * Предоставляет возможность загружать обрабатывать и сохранять ход и результаты соревнования.
 * На данный момент реализована загрузка участников из XML или JSON
 * и сохранение их в базу данных или CSV файл
 * @author Vasiliy Yatsevitch <zwtdbx@yandex.ru>
 */

include_once 'ClHandler.php';
include_once 'XMLParser.php';
include_once 'JSONParser.php';
include_once 'DBSaver.php';
include_once 'CSVSaver.php';

require_once 'KLogger.php';

class Competition {
    
    /**
     * Переменная, хранящая экземпляр парсера XML
     * @access private
     * @var XMLParser
     */
    private $XMLParser;
    /**
     * Переменная, хранящая экземпляр парсера JSON
     * @access private
     * @var JSONParser
     */
    private $JSONParser;
    /**
     * Переменная, хранящая ссылку на активный парсер
     * @access private
     * @var mixed
     */
    private $activeParser;
    
    /**
     * Переменная, хранящая экземпляр класса для сохранения данных в базу данных
     * @access private
     * @var DBSaver
     */
    private $DBSaver;
    /**
     * Переменная, хранящая экземпляр класса для сохранения данных в CSV формате
     * @access private
     * @var CSVSaver
     */
    private $CSVSaver;
    /**
     * Пермеменная, хранящая ссылку на активный экземпляр класса для сохранения информаии
     * @access private
     * @var mixed
     */
    private $activeSaver;
    
    /**
     * Массив, хранящий список команд-участниц соревнования
     * Хранит экземпляры класса Team
     * @access private
     * @var array
     */
    private $participants;
    /**
     * Экземпляр класса записи логов
     * @var KLogger
     */
    private $log;
    /**
     * Константа - путь к лог файлу
     */
    const DEFAULT_LOG_FILE_PATH = "log.txt";
    /**
     * Уровень логирования по умолчанию. Равен KLogger::DEBUG
     */
    const DEFAULT_LOG_LEVEL = KLogger::DEBUG;
    
    /**
     * Метод-конструктор. Инициализируем переменные получения и сохранения данных, систему логирования
     */
    function __construct() {
        $this->XMLParser = new XMLParser();
        $this->JSONParser = new JSONParser();
        $this->DBSaver = new DBSaver();
        $this->CSVSaver = new CSVSaver();
        $this->log = new KLogger(self::DEFAULT_LOG_FILE_PATH, self::DEFAULT_LOG_LEVEL);
    }
    
    /**
     * Получает и обрабатывает список команд-участниц соревнования
     * @access public
     * @param string $inputType тип получаемого файла (xml или json)
     * @param string $url URL для загрузки файла участников
     * @return boolean TRUE если данные успешно загружены, FALSE в ином случае
     */
    public function GetParticipantsFromURL($inputType, $url) {
        switch ($inputType) {
            case "xml":
                $this->activeParser =& $this->XMLParser;
                break;
            case "json":
                $this->activeParser =& $this->JSONParser;
                break;
            default :
                $this->log->LogError("Ошибка: неверный формат входного файла (Competition::GetParticipantsFromURL)");
                return FALSE;
                break;
        }
        $result = $this->activeParser->ParseFromUrl($url);
        if ($result !== false) {
            $this->participants = $result;
        } else {
            $this->log->LogError($this->activeParser->GetLastError());
            return false;
        }
        return true;
    }
    
    /**
     * Создает подключение к базе данных. Параметры подключения берутся из лог файла
     * @access public
     */
    public function CreateConnectionToDB() {
        if (!$this->DBSaver->ConnectToDB()) {
            $this->log->LogError("Ошибка подключения к СУБД: SQL". $this->DBSaver->GetLastSqlError());
            $this->log->LogError("Объяснение: ".$this->DBSaver->GetLastError());
            return FALSE;
        }
        return TRUE;
    }
    
    /**
     * Создает подключение к базе данных с заданными параметрами
     * @access public
     * @param string $dbType тип СУБД
     * @param string $servername адресс сервера
     * @param string $username логин
     * @param string $password пароль
     * @param string $database название базы данных
     * @return boolean TRUE если соединение установлено, FALSE в ином случае
     */
    public function CreateConnectionToSpecificDB($dbType, $servername, $username, $password, $database) {
        if (!$this->DBSaver->ConnectToSpecificBD($dbType, $servername, $username, $password, $database)) {
            $this->log->LogError("Ошибка подключения к СУБД: SQL". $this->DBSaver->GetLastSqlError());
            $this->log->LogError("Объяснение: ".$this->DBSaver->GetLastError());
            return false;
        }
        return true;
    }
    
    /**
     * Сохраняет участников в базу данных или CSV фалй
     * @access public
     * @param string $destination путь сохранения (db или csv)
     * @return boolean TRUE если сохранение успешно, FALSE в ином случае
     */
    public function SaveParticipants($destination) {
        switch ($destination) {
            case "db":
                $this->activeSaver =& $this->DBSaver;
                if (!$this->activeSaver->GetConnectionStatus()) {
                    $this->log->LogError("Нет соединения с базой данных");
                    return FALSE;
                }
                break;
            case "json":
                $this->activeSaver =& $this->CSVSaver;
                break;
            default :
                $this->log->LogError("Ошибка: неверный формат пути сохранения");
                return FALSE;
                break;
        }
        
        $this->activeSaver->SetParticipants($this->participants);
        if (!$this->activeSaver->Save()) {
            $this->log->LogError("Ошибка сохранения: SQL". $this->DBSaver->GetLastSqlError());
            $this->log->LogError("Объяснение: ".$this->DBSaver->GetLastError());
            return false;
        }
        return true;
    }
}

?>