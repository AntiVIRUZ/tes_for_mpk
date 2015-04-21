<?php

class DBSettings {

    private $configFile = "config.ini";
    private $dbType;
    private $servername;
    private $username;
    private $password;
    private $database;

    public function __construct() {
        $this->LoadSettings();
    }

    public function LoadSettings() {
        $settings = parse_ini_file($this->configFile);
        CheckIniFile($settings);
        $this->dbType = $settings["dbType"];
        $this->servername = $settings["servername"];
        $this->username = $settings["username"];
        $this->password = $settings["password"];
        $this->database = $settings["database"];
    }

    private function CheckIniFile($settings) {
        if ($settings === FALSE) {
            trigger_error("Отсутствует файл настроек", E_USER_ERROR);
        } else {
            if (!isset($settings["dbType"])) {
                trigger_error("Отсутствует название драйвера базы данных", E_USER_ERROR);
            }
            if (!isset($settings["servername"])) {
                trigger_error("Отсутствует адресс сервера", E_USER_ERROR);
            }
            if (!isset($settings["username"])) {
                trigger_error("Отсутствует логин", E_USER_ERROR);
            }
            if (!isset($settings["password"])) {
                trigger_error("Отсутствует пароль", E_USER_ERROR);
            }
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
