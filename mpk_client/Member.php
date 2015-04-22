<?php

class Member {
     
    public $name;
    public $passport;
    private $group;
     
    function __construct($name, $passport) {
        $this->name = $name;
        $this->passport = $passport;
    }
    
    public function getGroupName() {
        return $this->group;
    }
    
    public function changeGroup($name) {
        $this->group = $name;
    }
    
}
