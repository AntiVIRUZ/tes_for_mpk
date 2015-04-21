<?php

include_once 'iSaver.php';
include_once 'DBSettings.php';

class DBSaver implements iSaver {

    private $participants;
    private $lastError;
    private $pdo;
    private $connectionStatus;
    private $dbSettings;
    
    private $primaryKeyStartValue = array();
    
    private $tables = array( "sports_kinds", "participants", "teams", "participants_teams");
    
    function __construct() {
        $this->connectionStatus = false;
        try {
            $this->dbSettings = new DBSettings();
        } catch (Exception $e) {
            //@TODO Добавить логирование
            die($e->getMessage());
        }
    }
    
    public function GetConnectionStatus() {
        return $this->connectionStatus;
    }
    
    public function GetLastError() {
        return $this->lastError;
    }

    public function SetParticipants($participants) {
        $this->participants = $participants;
    }

    public function Save() {
        $sql = "INSERT INTO sports_kinds (id, name)\n" .
                "VALUES\n";
        
        //Запоминаем количество записей, чтобы после последней поставить ;
        $recordsCount = count($this->participants["sports_kinds"]);
        foreach ($this->participants["sports_kinds"] as $key => $value){
            $sql .= "(".$value["id"].", '".$value["name"]."'),\n";
        }
        $sql[strlen($sql)-2] = ";";
        $this->SentQuery($sql);
        
        $sql = "INSERT INTO teams (id, name, sports_kind_id)\n" .
                "VALUES\n";
        
        $recordsCount = count($this->participants["teams"]);
        foreach ($this->participants["teams"] as $key => $value){
            $sql .= "(".$value["id"].", '".$value["name"]."', ".$value["sports_kind_id"]."),\n";
        }
        $sql[strlen($sql)-2] = ";";
        $this->SentQuery($sql);
        
        $sql = "INSERT INTO participants (id, name)\n" .
                "VALUES\n";
        
        $recordsCount = count($this->participants["participants"]);
        foreach ($this->participants["participants"] as $key => $value){
            $sql .= "(".$value["id"].", '".$value["name"]."'),\n";
        }
        $sql[strlen($sql)-2] = ";";
        $this->SentQuery($sql);
        
        $sql = "INSERT INTO participants_teams (id, participant_id, team_id)\n" .
                "VALUES\n";
        
        $i = 1;
        foreach ($this->participants["participants"] as $key => $value)
        foreach ($value["teams"] as $team_key => $team_value) {
            $sql .= "(".$i.", ".$value["id"].", ".$team_value."),\n";
            $i++;
        }
        $sql[strlen($sql)-2] = ";";
        return $this->SentQuery($sql);
    }
    
    public function ConnectToDB () {
        return $this->ConnectToSpecificBD($this->dbSettings->getDbType(), $this->dbSettings->getServername(), $this->dbSettings->getUsername(), $this->dbSettings->getPassword(), $this->dbSettings->getDatabase);
    }
    
    public function ConnectToSpecificBD($dbType, $servername, $username, $password, $database)
    {
        if (!checkSupportingDBDriver($this->dbSettings->getDbType())) {
            trigger_error("Драйвер базы данных не поддерживается или указан неверно", E_USER_ERROR);
        }
        
        if ($this->connectionStatus) {
            $this->DisconnectFromDB();
        }
        
        $connectionString = $this->dbSettings->getDbType() . ":";
        $connectionString .= "host=" . $servername;
        
        try {
            $this->pdo = new PDO($connectionString, $username, $password);
        } catch (Exception $e) {
            //@TODO Добавить логирование
            $this->lastError = $e->getMessage();
            return FAlSE;
        }
        
        if ( $this->CreateDatabaseIfNotExists($database) ) {
            $this->SelectDatabase($database);
            $this->CreateTables();
        } else {
            return false;
        }
        $this->connectionStatus = true;
        return true;
    }
    
    public function SelectDatabase($name) {
        return $this->SentQuery("use " . $name . ";");
    }
    
    private function checkSupportingDBDriver($driverName) {
        $avaiableDrivers = PDO::getAvailableDrivers();
        if (in_array($driverName, $avaiableDrivers)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    private function DisconnectFromDB () {
        $this->pdo = NULL;
    }
    
    private function CreateDatabaseIfNotExists($database) {
        return SentQuery("CREATE DATABASE IF NOT EXISTS `". $database . "` CHARACTER SET utf8 COLLATE utf8_general_ci;");
    }
    
    private function CreateTables() {
        
        $sql = "CREATE TABLE IF NOT EXISTS `sports_kinds` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `name` varchar(50) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $this->SentQuery($sql);
        
        $sql = "CREATE TABLE IF NOT EXISTS `participants` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `name` varchar(50) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $this->SentQuery($sql);
        
        $sql = "CREATE TABLE IF NOT EXISTS `teams` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `name` varchar(50) NOT NULL,
                  `sports_kind_id` int(11) NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `t2sk` (`sports_kind_id`),
                  CONSTRAINT `t2sk` FOREIGN KEY (`sports_kind_id`) REFERENCES `sports_kinds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $this->SentQuery($sql);
        
        $sql = "CREATE TABLE IF NOT EXISTS `participants_teams` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `participant_id` int(11) NOT NULL,
                  `team_id` int(11) NOT NULL,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `participant_id_team_id` (`participant_id`,`team_id`),
                  KEY `pt2t` (`team_id`),
                  CONSTRAINT `pt2p` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                  CONSTRAINT `pt2t` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $this->SentMultyQuery($sql);
    }
    
    private function SentQuery($sql) {
        $sth = $this->pdo->prepare($sql);
        $result = $sth->execute();
        if (!$result) {
            $this->lastError = $this->pdo->errorInfo();
        }
        return $result;
    }
    
    function __destruct() {
        $this->DisconnectFromDB();
    }

}
