<?php

/**
 * Модель данных для хранения команд
 * @author Vasiliy Yatsevitch <zwtdbx@yandex.ru>
 */

include_once 'Member.php';

class Team {

    /**
     * Название команды
     * @access public
     * @var string
     */
    public $name;
    /**
     * Вид спорта, в котором выступает команда
     * @access public
     * @var string
     */
    public $sportsKind;
    /**
     * Девиз команды
     * @access public
     * @var string
     */
    public $motto;
    
    /**
     * Предопределенный массив полей, содержащихся в классе Member
     * @access private
     * @var array
     */
    private $memberFielList = array("name", "passport");
    
    /**
     * Массив членов данной команды (Member)
     * @access private
     * @var array
     */
    private $members;

    /**
     * Метод-конструктор. Помещает в поля класса информацию о команде
     * @param string $name Название команды
     * @param string $sportsKind Вид спорта, в котором выступает команда
     * @param array $members Массив членов команды (Member)
     * @param string $motto Девиз команды
     */
    function __construct($name, $sportsKind, &$members = null, $motto = "") {
        $this->name = $name;
        $this->sportsKind = $sportsKind;
        $this->members =& $members;
        $this->motto = $motto;
    }
    
    /**
     * Добавляет участника в группу
     * @access public
     * @param Member &$member Ссылка на экземпляр класса участника
     * @return boolean TRUE если $member - экземпляр класса Member, FALSE в противном случае
     */
    public function AddMemberToGroup(&$member) {
        if (get_class($member) == "Member") {
            $this->members[count($this->members)] =& $member;
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Удаляет участника из группы по значению указанного поля
     * @access public
     * @param string $string Значение поля
     * @param string $field Имя поля
     * @return boolean TRUE если участник с указанными данными существуетб FALSE в ином случае
     */
    public function DeleteMemberFromGroup($string, $field) {
        $key = $this->SearchMemberByName($string, $field);
        if ($key === FALSE) {
            return FALSE;
        } else {
            unset($this->members[$key]);
            return TRUE;
        }
    }
    
    /**
     * Возвращает ссылку на участника группы по значению указанного поля
     * @access public
     * @param type $string Значение поля
     * @param type $field Имя поля
     * @return mixed &Member, если найден, FALSE в противном случае
     */
    public function &GetMemberBy($string, $field) {
        $key = $this->SearchMemberBy($string, $field);
                
        if ($key === FALSE) {
            return FALSE;
        } else {
            return $this->members[$key];
        }
    }
    
    /**
     * Возвращает список участников соревнования
     * @access public
     * @return array Массив имен участников соревнования
     */
    public function GetMembersList() {
        $result = array();
        foreach ($this->members as $member) {
            array_push($result, $member->name);
        }
        return $result;
    }
   
    /**
     * Возвращает ключ массива $members участника группы по значению указанного поля
     * @access private
     * @param type $string Значение поля
     * @param type $field Имя поля
     * @return mixed ключ массива, если найден, FALSE в противном случае
     */
    private function SearchMemberBy($string, $field) {
        if (in_array($field, $this->memberFielList)) {
            foreach($this->members as $key => $member) {
                if ($member->$field == $string) {
                    return $key;
                }
            }
        }
        return FALSE;
    }
}
