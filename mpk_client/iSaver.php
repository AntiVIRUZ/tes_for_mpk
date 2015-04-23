<?php

/**
 * Интерфейс класса для сохранения полученных данных
 * @abstract
 */

interface iSaver {
    
    /**
     * Функция, устанавливающая новый список участников в поле класса
     * @abstract
     * @access public
     * @param array $participants Массив команд - экземпляров класса Team
     */
    public function SetParticipants($participants);
    /**
     * Функция, сохраняющая данные
     * @abstract
     * @access public
     */
    public function Save();
    
}