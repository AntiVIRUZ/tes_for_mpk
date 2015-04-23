<?php

/**
 * Класс, загружающий и хранящий нстройки подключения к базе данных
 * @author Vasiliy Yatsevitch <zwtdbx@yandex.ru>
 */
class DBSettings {

    /**
     * Путь к конфиг файлу
     * @access private
     * @var string
     */
    private $configFile = "config.ini";
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
     * Метод-конструктор. Загружает данные из файла
     */
    public function __construct() {
        $this->LoadSettings();
    }

    /**
     * Загружает данные из файла $configFile и помещает их в поля класса
     */
    public function LoadSettings() {
        $settings = parse_ini_file($this->configFile);
        $this->CheckIniFile($settings);
        $this->dbType = $settings["dbType"];
        $this->servername = $settings["servername"];
        $this->username = $settings["username"];
        $this->password = $settings["password"];
        $this->database = $settings["database"];
    }

    /**
     * Проверяет полноту файла настроек
     * @access private
     * @param array $settings массив настроек
     */
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
