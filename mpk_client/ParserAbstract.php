<?php

/**
 * Абстрактный класс парсера входных данных
 * 
 * Содержит методы загрузки файла с данными с URL и помещения полученных данных
 * в массив экземпляров класса Team
 * @abstract
 * @author Vasiliy Yatsevitch <zwtdbx@yandex.ru>
 */

include_once 'Team.php';
include_once 'Member.php';

abstract class ParserAbstract {

    /**
     * Массив уникальных людей, полученных из входных данных
     * @access private
     * @var array
     */
    private $memberList;
    /**
     * количество уникальных людей.
     * @access private
     * @var int
     */
    private $memberCount;
    /**
     * Хранит описание последней возникшей ошибки
     * @access private
     * @var string
     */
    private $lastError;

    /**
     * Загружает файл с $url и инициализирует парсинг
     * @access public
     * @param string $url URL загружаемого файла
     * @return array массив экзепляров класса Team
     */
    public function parseFromUrl($url) {
        $xmlString = FilesLoader::LoadFile($url);
        if ($xmlString === FALSE) {
            $this->lastError = "Запрошенный файл не существует";
        }
        return $this->parseFromString($xmlString);
    }

    /**
     * Формирует массив экземпляров класса Team из ассоциативного массива,
     * полученного из XML или JSON
     * @access protected
     * @param array $array Ассоциативный массив с данными
     * @return mixed array Массив экземпляров класса Team, FALSE в случае некорректных входных данных
     */
    protected function GetTeamsFromArray($array) {
        $teamsCount = 0;
        $result = array();
        
        $this->memberList = array();
        $this->memberCount = 0;
        
        foreach ($array["teams"] as $team) {
            if (!$this->VerifyTeam($team)) {
                return FALSE;
            }
            $members =& $this->FormMembersArray($team["members"]);
            $result[$teamsCount] = new Team($team["name"], $team["sports_kind"], $members, $team["motto"]);
            $teamsCount++;
        }
        return $result;
    }
    
    /**
     * Формирует массив участников конкретной команды из ассоциативного массива
     * 
     * Если человек с таким именем уже существует, то функция помещает в массив ссылку на него
     * @access private
     * @param array $members Ассоциативный массив с информацией о участниках конкретной группы
     * @return mixed  &array Массив ссылок на экземпляры класса Member, FALSE в случае некорректных входных данных
     */
    private function &FormMembersArray($members) {
        $result = array();
        $teamMemberCount = 0;
        foreach ($members as $member) {
            if (!$this->VerifyMember($member)) {
                return FALSE;
            }
            $memberNumber = $this->SearchByName($this->memberList, $member["name"]);
            if ($memberNumber === FALSE) {
                $this->memberList[$this->memberCount] = new Member($member["name"], $member["passport"]);
                $result[$teamMemberCount] =& $this->memberList[$this->memberCount];
                $this->memberCount++;
                $teamMemberCount++;
            } else {
                $result[$teamMemberCount] =& $this->memberList[$memberNumber];
                $teamMemberCount++;
            }
        }
        return $result;
    }
    
    /**
     * Проверяет полноту данных команды
     * @access private
     * @param boolean TRUE если ошибок нет, FALSE в ином случае
     */
    private function VerifyTeam($team) {
        if ($team["name"] == "") {
            $this->lastError = "У группы нет имени (ParserAbstract::VerifyTeam)";
            return FALSE;
        }
        if ($team["sports_kind"] == "") {
            $this->lastError = "У группы нет вида спорта (ParserAbstract::VerifyTeam)";
            return FALSE;
        }
        return true;
    }
    
    /**
     * Проверяет полноту данных человека
     * @access private
     * @param array $team ассоциатиынй массив данных команды
     */
    private function VerifyMember($member) {
        if ($member["name"] == "") {
            $this->lastError = "У участника нет имени (ParserAbstract::VerifyMember)";
            return FALSE;
        }
        if ($member["passport"] == "") {
            $this->lastError = "У участника ". $member["name"] . " не заданы паспортные данные (ParserAbstract::VerifyMember)";
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Ищет в массиве объект со значением $name в поле name
     * @access private
     * @param array $array Массив объектов
     * @param string $name значение поля $name
     * @return mixed ключ массива, если элемент найден, FALSE в ином случае
     */
    private function SearchByName($array, $name) {
        foreach ($array as $key => $value) {
            if ($value->name == $name)
                return $key;
        }
        return FALSE;
    }
    
    /**
     * Возвращает описание последней возникшей ошибки
     * @access public
     * @return string
     */
    public function GetLastError() {
        return $this->lastError;
    }

    /**
     * Функция парсинга строки в массив экземпляров класса Team.
     * @access public
     * @abstract
     */
    abstract public function parseFromString($string);
}

?>