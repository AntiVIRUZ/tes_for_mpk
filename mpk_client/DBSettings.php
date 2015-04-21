<?php

class DBSettings {
    
    private $configFile = "config.ini";
    
    private $dbType;
    private $servername;
    private $username;
    private $password;
    
    public function __construct() {
        $this->LoadSettings();
    }
    
    public function LoadSettings() {
        $settings = parse_ini_file($this->configFile);
        if ($settings === FALSE) {
            trigger_error("Отсутствует файл настроек", E_USER_ERROR);
        }
        if (isset($settings["dbType"])) {
            $this->dbType = $settings["dbType"];
        }
        if (isset($settings["servername"])) {
            $this->servername = $settings["servername"];
        }
        if (isset($settings["username"])) {
            $this->username = $settings["username"];
        }
        if (isset($settings["password"])) {
            $this->password = $settings["password"];
        }
    }
    
    public function getUsername() {
        return $this->username;
    }
    
    public function getPassword() {
        return $this->password;
    }
    
    public function getDbType() {
        return $this->dbType;
    }
    
    public function getServername() {
        return $this->servername;
    }
}
