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
     * Загружает файл с $url и инициализирует парсинг
     * @access public
     * @param string $url URL загружаемого файла
     * @return array массив экзепляров класса Team
     */
    public function parseFromUrl($url) {
        $xmlString = FilesLoader::LoadFile($url);
        return $this->parseFromString($xmlString);
    }

    /**
     * Формирует массив экземпляров класса Team из ассоциативного массива,
     * полученного из XML или JSON
     * @access protected
     * @param array $array Ассоциативный массив с данными
     * @return array Массив экземпляров класса Team
     */
    protected function GetTeamsFromArray($array) {
        $teamsCount = 0;
        $result = array();
        
        $this->memberList = array();
        $this->memberCount = 0;
        
        foreach ($array["teams"] as $team) {
            $this->VerifyTeam($team);
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
     * @return &array Массив ссылок на экземпляры класса Member
     */
    private function &FormMembersArray($members) {
        $result = array();
        $teamMemberCount = 0;
        foreach ($members as $member) {
            $this->VerifyMember($member);
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
     * @param array $team ассоциатиынй массив данных команды
     */
    private function VerifyTeam($team) {
        if ($team["name"] == "") {
            trigger_error("У группы нет имени", E_USER_ERROR);
        }
        if ($team["sports_kind"] == "") {
            trigger_error("У группы нет вида спорта", E_USER_ERROR);
        }
    }
    
    /**
     * Проверяет полноту данных человека
     * @access private
     * @param array $team ассоциатиынй массив данных команды
     */
    private function VerifyMember($member) {
        if ($member["name"] == "") {
            trigger_error("У участника нет имени", E_USER_ERROR);
        }
        if ($member["passport"] == "") {
            trigger_error("У участника ". $member["name"] . " не заданы паспортные данные", E_USER_ERROR);
        }
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
     * Функция парсинга строки в массив экземпляров класса Team.
     * @access public
     * @abstract
     */
    abstract public function parseFromString($string);
}

?>