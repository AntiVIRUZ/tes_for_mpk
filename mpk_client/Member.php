<?php

class Member {
     
    public $name;
    public $passport;
    private $group;
     
    function __construct($name, $passport, $group) {
        $this->name = $name;
        $this->passport = $passport;
        $this->group = $group;
    }
    
    public function getGroupName() {
        return $this->group;
    }
    
    public function changeGroup($name) {
        $this->group = $name;
    }
    
}
