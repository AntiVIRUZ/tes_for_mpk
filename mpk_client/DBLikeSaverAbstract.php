<?php

/**
 * Абстрактный класс для сохранения полученных данных в формате таблиц баз данных
 * @abstract
 */

include_once 'iSaver.php';

abstract class DBLikeSaverAbstract implements iSaver {
    
    /**
     * Экземпляр класса настроек подключения по умолчанию
     * @access protected
     * @var SettingsAbstract
     */
    protected $dbSettings;
    /**
     * Список участников соревнования
     * 
     * Хранится в формате, соответствующем формату БД
     * @access protected
     * @var array
     */
    protected $participants;
    /**
     * Человеческое объяснение места возникновения последней ошибки
     * @access protected
     * @var string
     */
    protected $lastError;
    /**
     * Список таблиц в порядке занесения значений (для сохранения целостности ключей)
     * @access protected
     * @var array
     */
    protected $tables = array( "sports_kinds", "members", "teams", "members_teams");
    
    /**
     * Получить человеческое объяснение последней ошибки
     * 
     * @access public
     * @return string Описание ошибки
     */
    public function GetLastError() {
        return $this->lastError;
    }
    
    /**
     * Парсит список участников.
     * 
     * Парсит из массива команд (Team) в представление, соответствующее представлению базы данных
     * @access protected
     * @param array $array Массив команд (Team)
     * @return array массив соответствующий по структуре БД 
     */
    protected function ParseFromTeams($array) {
        $sportsKindsNumber = 0;
        $membersNumber = 0;
        $teamsNumber = 0;
        $membersToTeamsNumber = 0;
        $sportsKindsId;
        $memberList;

        $result = array();
        foreach ($this->tables as $value) {
            $result[$value] = array();
        }

        foreach ($array as $value) {
            $sportsKindsId = $this->SearchByName($result["sports_kinds"], $value->sportsKind);
            if ($sportsKindsId === FALSE) {
                $sportsKindsNumber++;
                $sportsKindsId = $sportsKindsNumber;
                $result["sports_kinds"][$sportsKindsNumber] = array("id" => $sportsKindsNumber, "name" => $value->sportsKind);
            }

            $teamsNumber++;
            $result["teams"][$teamsNumber] = array("id" => $teamsNumber, "name" => $value->name, "sports_kind_id" => $sportsKindsId, "motto" => $value->motto);

            $memberList = $value->GetMembersList();
            foreach ($memberList as $memberName) {
                $memberId = $this->SearchByName($result["members"], $memberName);
                if ($memberId === FALSE) {
                    $member = $value->GetMemberBy($memberName, "name");
                    $membersNumber++;
                    $memberId = $membersNumber;
                    $result["members"][$membersNumber] = array("id" => $membersNumber, "name" => $member->name, "passport" => $member->passport);
                }

                $membersToTeamsNumber++;
                $result["members_teams"][$membersToTeamsNumber] = array("id" => $membersToTeamsNumber, "member_id" => $memberId, "team_id" => $teamsNumber);
            }
        }
        return $result;
    }

    /**
     * Ищет среди массива элемента со значением $name в поле "name"
     * 
     * Используйте строгое (===) сравнения для проверки найден элемент, или нет, так как возвращаемый ключ может быть равен нулю
     * @access protected
     * @param array $array Массив для поиска
     * @param string $name Значение поля
     * @return mixed ключ элемента, если найден, FALSE, если не найден
     */
    protected function SearchByName($array, $name) {
        foreach ($array as $key => $value) {
            if ($value["name"] == $name)
                return $key;
        }
        return FALSE;
    }
    
    /**
     * Создает экземпляр класса настроек и загружает настройки
     * @access public
     * @return boolean TRUE если настройки успешно загружены, FALSE в ином случае
     */
    public function LoadSettingsFile($type) {
        switch ($type) {
            case "db":
                $this->dbSettings = new DBSettings();
                break;
            case "csv":
                $this->dbSettings = new CSVSettings();
                break;
        }
        if (!$this->dbSettings->LoadSettings()) {
            $this->lastError = "Ошибка файла настроек. " . $this->dbSettings->GetLastError() . " Доступно сохранение только по пользовательскому соединению (DBLikiSaverAbstract::LoadSettigns)";
            return false;
        }
        return true;
    }
    
    /**
     * Устанавливает список участников для дальнейшего сохранения
     * @access public
     * @param array $participants Массив команд (экземпляров класса Team)
     */
    public function SetParticipants($participants) {
        $this->participants = $this->ParseFromTeams($participants);
    }
    
    /**
     * Функция, сохраняющая данные
     * @abstract
     * @access public
     */
    abstract public function Save();
    
}