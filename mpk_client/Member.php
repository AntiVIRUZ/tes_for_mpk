<?php


/**
 * Модель данных для хранения участников команд
 * @author Vasiliy Yatsevitch <zwtdbx@yandex.ru>
 */
class Member {
     
    /**
     * Имя человека
     * @access public
     * @var string
     */
    public $name;
    /**
     * Пасспорт человека
     * @access public
     * @var string
     */
    public $passport;
    /**
     * Название группы, в которой состоит человек. Интерфейс передачи и установки пока не используется
     * @access private
     * @var string
     */
    private $team;
    
    /**
     * Метод-конструктор. Устанавливает имя и номер паспорта человека
     * @param type $name Имя человека
     * @param type $passport Номер паспорта
     */
    function __construct($name, $passport) {
        $this->name = $name;
        $this->passport = $passport;
    }
    
    /**
     * Возвращает группу, в которой состоит человек
     * @access public
     * @return string Название команды
     */
    public function getTeamName() {
        return $this->team;
    }
    
    /**
     * Устанавливает имя команды, в которой состоит человек
     * @access public
     * @param string $name Название команды
     */
    public function changeTeam($name) {
        $this->team = $name;
    }
    
}
