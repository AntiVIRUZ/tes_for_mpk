<?php

include_once 'DBLikeSaverAbstract.php';
include_once 'CSVSettings.php';

/**
 * Класс для сохранения списка участников в CSV файл
 * 
 * @author Vasiliy Yatsevitch <zwtdbx@yandex.ru>
 */
class CSVSaver extends DBLikeSaverAbstract {
    
    /**
     * Сохраняет список участников соревнования в CSV файлы
     * 
     * Создаются 4 файла по аналогии с SQL таблицами. Метод будет переработан
     * @access public
     */
    public function Save() {
        $SKfile = fopen($this->dbSettings->GetCsvPath()."sports_kinds.csv", "w");
        foreach ($this->participants["sports_kinds"] as $fields) {
            fputcsv($SKfile, $fields, $this->dbSettings->GetDelimeter());
        }
        fclose($SKfile);
        
        $Tfile = fopen($this->dbSettings->GetCsvPath()."teams.csv", "w");
        foreach ($this->participants["teams"] as $fields) {
            fputcsv($Tfile, $fields, $this->dbSettings->GetDelimeter());
        }
        fclose($Tfile);
        
        $Pfile = fopen($this->dbSettings->GetCsvPath()."members.csv", "w");
        foreach ($this->participants["members"] as $fields) {
            fputcsv($Pfile, $fields, $this->dbSettings->GetDelimeter());
        }
        fclose($Pfile);
        
        $MTfile = fopen($this->dbSettings->GetCsvPath()."members_teams.csv", "w");
        foreach ($this->participants["members_teams"] as $fields) {
            fputcsv($MTfile, $fields, $this->dbSettings->GetDelimeter());
        }
        fclose($MTfile);
    }
}