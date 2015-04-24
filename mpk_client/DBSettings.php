<?php

/**
 * Класс, загружающий и хранящий нстройки подключения к базе данных
 * @author Vasiliy Yatsevitch <zwtdbx@yandex.ru>
 */

include_once 'SettingsAbstract.php';

class DBSettings extends SettingsAbstract {

    /**
     * Тип драйвера базы данных
     * @access private
     * @var string 
     */
    private $dbType;
    /**
     * Адресс сервера СУБД
     * @access private
     * @var string
     */
    private $servername;
    /**
     * Логин для подключения к БД
     * @access private
     * @var string
     */
    private $username;
    /**
     * Пароль для подключения к БД
     * @access private
     * @var string
     */
    private $password;
    /**
     * Названия базы данных
     * @access private
     * @var string
     */
    private $database;
    
    /**
     * Загружает данные из файла $configFile и помещает их в поля класса
     * @access public
     * @return boolean TRUE если настройки успешно загружены, FALSE в ином случае
     */
    public function LoadSettings() {
        $settings = parse_ini_file(parent::CONFIG_FILE);
        if (!$this->CheckIniFile($settings)) {
            return false;
        }
        $this->dbType = $settings["dbType"];
        $this->servername = $settings["servername"];
        $this->username = $settings["username"];
        $this->password = $settings["password"];
        $this->database = $settings["database"];
        return true;
    }
    
    /**
     * Проверяет полноту файла настроек
     * @access private
     * @param array $settings массив настроек
     * @return boolean TRUE если настройки заданны корректно, FALSE в ином случае
     */
    protected function CheckIniFile($settings) {
        if ($settings === FALSE) {
            $this->lastError = "Отсутствует файл настроек";
            return FALSE;
        } else {
            if (!isset($settings["dbType"])) {
                $this->lastError = "Отсутствует название драйвера базы данных";
                return FALSE;
            }
            if (!isset($settings["servername"])) {
                $this->lastError = "Отсутствует адресс сервера";
                return FALSE;
            }
            if (!isset($settings["username"])) {
                $this->lastError = "Отсутствует логин";
                return FALSE;
            }
            if (!isset($settings["password"])) {
                $this->lastError = "Отсутствует пароль";
                return FALSE;
            }
        }
        return true;
    }

    /**
     * Возвращает логин для подключения к СУБД
     * @access public
     * @return string
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * Возвращает пароль для подключения к СУБД
     * @access public
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * Возвращает имя драйвера подключения к СУБД
     * @access public
     * @return string
     */
    public function getDbType() {
        return $this->dbType;
    }

    /**
     * Возвращает адресс сервера СУБД
     * @access public
     * @return string
     */
    public function getServername() {
        return $this->servername;
    }

    /**
     * Возвращает имя базы данных
     * @access public
     * @return string
     */
    public function getDatabase() {
        return $this->database;
    }
}
