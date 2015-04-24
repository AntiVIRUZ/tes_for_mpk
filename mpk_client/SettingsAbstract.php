<?php

/**
 * Абстрактный класс загрузки настроек
 *
 * @abstract
 * @author Vasiliy Yatsevitch <zwtdbx@yandex.ru>
 */

abstract class SettingsAbstract {
    
    /**
     * Путь к конфиг файлу
     * @var string
     */
    const CONFIG_FILE= "config.ini";
    
    /**
     * Описание последней ошибки
     * @var string
     */
    private $lastError;
    
    /**
     * Получить описание последней ошибки
     * 
     * @access public
     * @return string Описание ошибки
     */
    public function GetLastError() {
        return $this->lastError;
    }
    
    /**
     * Загружает данные из файла $configFile и помещает их в поля класса
     * @access public
     * @abstract
     */
    abstract public function LoadSettings();
    /**
     * Проверяет полноту файла настроек
     * @access private
     * @abstract
     * @param array $settings массив настроек
     * @return boolean TRUE если настройки заданны корректно, FALSE в ином случае
     */
    abstract protected function CheckIniFile($settings);
    
}
