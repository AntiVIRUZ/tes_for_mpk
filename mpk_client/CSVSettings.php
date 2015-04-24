<?php

/**
 * Класс, загружающий и хранящий нстройки осхранения CSV файлов
 * @author Vasiliy Yatsevitch <zwtdbx@yandex.ru>
 */
class CSVSettings extends SettingsAbstract {
    
    /**
     * Разделитель CSV записи
     * @var string
     */
    private $delimeter;
    /**
     * Путь сохранения CSV файлов
     * @var string
     */
    private $cvs_path;
    
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
        $this->delimeter = $settings["delimeter"];
        $this->cvs_path  = $settings["cvs_path"];
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
            if (!isset($settings["delimeter"])) {
                $settings["delimeter"] = ";";
            }
            if (!isset($settings["cvs_path"])) {
                $settings["cvs_path"] = "csv\\";
            }
        }
        return true;
    }
    
    /**
     * Возвращает разделитель записей
     * @return string
     */
    public function GetDelimeter() {
        return $this->delimeter;
    }
    
    /**
     * Возвращает путь для сохранения CSV файлов
     * @return string
     */
    public function GetCsvPath() {
        return $this->cvs_path;
    }
}
