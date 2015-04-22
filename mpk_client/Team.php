<?php

include_once 'Member.php';

class Team {

    public $name;
    public $sportsKind;
    public $motto;
    
    private $fielList = array("name", "passport");
    
    private $members;

    function __construct($name, $sportsKind, &$members = null, $motto = "") {
        $this->name = $name;
        $this->sportsKind = $sportsKind;
        $this->members =& $members;
        $this->motto = $motto;
    }
    
    public function AddMemberToGroup($member) {
        if (get_class($member) == "Member") {
            array_push($this->members, $member);
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function DeleteMemberFromGroup($string, $field) {
        $key = $this->SearchMemberByName($string, $field);
        if ($key === FALSE) {
            return FALSE;
        } else {
            unset($this->members[$key]);
            return TRUE;
        }
    }
    
    public function &GetMemberBy($string, $field) {
        $key = $this->SearchMemberBy($string, $field);
                
        if ($key === FALSE) {
            return FALSE;
        } else {
            return $this->members[$key];
        }
    }
    
    public function GetMembersList() {
        $result = array();
        foreach ($this->members as $member) {
            array_push($result, $member->name);
        }
    }
    
    private function SearchMemberBy($string, $field) {
        if (in_array($field, $this->fielList)) {
            foreach($this->members as $key => $member) {
                if ($member->$field == $string) {
                    return $key;
                }
            }
        }
        return FALSE;
    }
}
