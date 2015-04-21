<?php

include_once 'ClHandler.php';
include_once 'XMLParser.php';
include_once 'JSONParser.php';
include_once 'DBSaver.php';
include_once 'CSVSaver.php';

class Competition {
    private $XMLParser;
    private $JSONParser;
    private $activeParser;
    
    private $DBSaver;
    private $CSVSaver;
    private $activeSaver;
    
    private $participants;
    
    function __construct() {
        $this->XMLParser = new XMLParser();
        $this->JSONParser = new JSONParser();
        $this->DBSaver = new DBSaver();
        $this->CSVSaver = new CSVSaver();
    }
    
    //echo будет заменено на логирование и возвраты ошибок
    public function GetParticipantsFromURL($inputType, $url) {
        switch ($inputType) {
            case "xml":
                $this->activeParser =& $this->XMLParser;
                break;
            case "json":
                $this->activeParser =& $this->JSONParser;
                break;
            default:
                echo "Неверный входной формат";
                return false;
                break;
        }
        try {
            $this->participants = $this->activeParser->ParseFromUrl($url);
            return true;
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
            return false;
        }
    }
    
    public function CreateConnectionToDB() {
        if (!$this->DBSaver->ConnectToDB()) {
            echo "Ошибка подключения к базе данных: ". $this->DBSaver->GetLastError();
        }
    }
    
    public function CreateConnectionToSpecificDB($servername, $username, $password) {
        if (!$this->DBSaver->ConnectToSpecificBD($servername, $username, $password)) {
            echo "Ошибка подключения к базе данных: ". $this->DBSaver->GetLastError();
        }
    }
    
    public function SaveParticipants($destination) {
        switch ($destination) {
            case "db":
                $this->activeSaver =& $this->DBSaver;
                if (!$this->activeSaver->GetConnectionStatus()) {
                    echo "Нет соединения с базой данных";
                    return FALSE;
                }
                break;
            case "json":
                $this->activeSaver =& $this->CSVSaver;
                break;
            default:
                echo "Неверный формат сохранения";
                return false;
                break;
        }
        try {
            $this->activeSaver->SetParticipants($this->participants);
            if (!$this->activeSaver->Save()) {
                echo "Ошибка сохранения: ". $this->activeSaver->GetLastError();
            }
            return true;
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
            return false;
        }
    }
}

?>