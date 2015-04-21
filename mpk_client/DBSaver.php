<?php

include 'iSaver.php';

class DBSaver extends iSaver {

    private $participants;
    private $lastError;
    private $mysqli;
    
    function __construct() {
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
        $this->SentQuery($sql);
    }
    
    public function ConnectToDB ($servername, $username, $password) {
        $this->DisconnectFromDB();
        
        $this->mysqli = new mysqli($servername, $username, $password);
        
        //Проверяем соединение
        if ($this->mysqli->connect_error) {
            $this->lastError = "Connection failed: " . $this->mysqli->connect_error;
            return false;
        }
        
        if ( $this->CreateDatabaseIfNotExists() ) {
            $this->mysqli->select_db("mpk_test");
            CreateTables();
        } else {
            return false;
        }
        
        return true;
    }
    
    private function DisconnectFromDB () {
        $this->mysqli->close();
    }
    
    private function CreateDatabaseIfNotExists() {
        $sql = "CREATE DATABASE IF NOT EXISTS mpk_test CHARACTER SET utf8 COLLATE utf8_general_ci;";
        return $this->SentQuery($sql);
        
    }
    
    private function CreateTables() {
        $sql = "DROP TABLE IF EXISTS `sports_kinds`;
                CREATE TABLE IF NOT EXISTS `sports_kinds` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `name` varchar(50) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $this->SentQuery($sql);
        
        $sql = "DROP TABLE IF EXISTS `participants`;
                CREATE TABLE IF NOT EXISTS `participants` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `name` varchar(50) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $this->SentQuery($sql);
        
        $sql = "DROP TABLE IF EXISTS `teams`;
                CREATE TABLE IF NOT EXISTS `teams` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `name` varchar(50) NOT NULL,
                  `sports_kind_id` int(11) NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `t2sk` (`sports_kind_id`),
                  CONSTRAINT `t2sk` FOREIGN KEY (`sports_kind_id`) REFERENCES `sports_kinds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $this->SentQuery($sql);
        
        $sql = "DROP TABLE IF EXISTS `participants_teams`;
                CREATE TABLE IF NOT EXISTS `participants_teams` (
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
    
    private function SentQuery($sql) {
        if ($this->mysqli->query($sql) === FALSE) {
            $this->lastError = $this->mysqli->error;
            return FALSE;
        } else {
            return true;
        }
    }
    
    function __destruct() {
        $this->DisconnectFromDB();
    }

}
