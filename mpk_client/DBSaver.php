<?php

include_once 'iSaver.php';
include_once 'DBSettings.php';

class DBSaver implements iSaver {

    private $participants;
    private $lastError;
    private $pdo;
    private $connectionStatus;
    private $dbSettings;
    private $lastStatement;
    
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
        foreach ($this->tables as $table) {
            $this->SaveTable($table, $this->participants[$table]);
        }
    }
    
    private function SaveTable($name, $values) {
        $params = array();
        $sql = "INSERT INTO `" . $name . "` (";
        foreach ($values[0] as $key => $field) {
            $sql .= $key. ",";
        }
        $sql[strlen($sql)-1] = ") ";
        $sql .= "VALUES";
        
        foreach ($values as $field){
            $sql .= "(";
            foreach ($field as $value) {
                $sql .= "?,";
                array_push($params, $value);
            }
            $sql[strlen($sql)-1] = ")";
            $sql .= ",";
        }
        $sql[strlen($sql)-1] = ";";
        return $this->SentQuery($sql, $params);
    }
    
    private function IncrementIds(&$values, $num) {
        foreach ($values as $key => $value) {
            $values[$key]["id"] += $num;
        }
        echo "num = ".$num;
    }
    
    private function GetMaxIdFromTable($name) {
        $result = $this->SentQuery("SELECT MAX(id) FROM ". $name);
        if ($result) {
            $info = $this->lastStatement->fetch();
            return $info["MAX(id)"];
        }
    }
    
    public function ConnectToDB () {
        return $this->ConnectToSpecificBD($this->dbSettings->getDbType(), $this->dbSettings->getServername(), $this->dbSettings->getUsername(), $this->dbSettings->getPassword(), $this->dbSettings->getDatabase());
    }
    
    public function ConnectToSpecificBD($dbType, $servername, $username, $password, $database)
    {
        if (!$this->checkSupportingDBDriver($this->dbSettings->getDbType())) {
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
            $this->DeleteTables();
            $this->CreateTables();
        } else {
            return false;
        }
        $this->connectionStatus = true;
        return true;
    }
    
    public function SelectDatabase($name) {
        return $this->SentQuery("use `".$name."`;");
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
        return $this->SentQuery("CREATE DATABASE IF NOT EXISTS `".$database."` CHARACTER SET utf8 COLLATE utf8_general_ci;");
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
        $this->SentQuery($sql);
    }
    
    private function DeleteTables() {
        foreach (array_reverse($this->tables) as $table) {
            $sql = "DROP TABLE IF EXISTS `".$table."`;";
            if (!$this->SentQuery($sql)) {
                trigger_error("Ошибка сохранения базы данных", E_USER_ERROR);
            }
        }
    }
    
    private function SentQuery($sql, $input_params = NULL) {
        $sth = $this->pdo->prepare($sql);
        if ($input_params) {
            $result = $sth->execute($input_params);
        } else {
            $result = $sth->execute();
        }
        if (!$result) {
            print_r($sth->errorInfo());
            $this->lastError = $sth->errorInfo();
        } else {
            $this->lastStatement = $sth;
        }
        return $result;
    }
    
    function __destruct() {
        $this->DisconnectFromDB();
    }

}
