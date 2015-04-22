<?php

include_once 'Team.php';
include_once 'Member.php';

abstract class ParserAbstract {

    private $lastError;
    private $memberList;
    private $memberCount;

    public function parseFromUrl($url) {
        $xmlString = FilesLoader::LoadFile($url);
        return $this->parseFromString($xmlString);
    }

    public function getLastError() {
        return $this->lastError;
    }

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
    
    private function VerifyTeam($team) {
        if ($team["name"] == "") {
            trigger_error("У группы нет имени", E_USER_ERROR);
        }
        if ($team["sports_kind"] == "") {
            trigger_error("У группы нет вида спорта", E_USER_ERROR);
        }
    }
    
    private function VerifyMember($member) {
        if ($member["name"] == "") {
            trigger_error("У участника нет имени", E_USER_ERROR);
        }
        if ($member["passport"] == "") {
            trigger_error("У участника ". $member["name"] . " не заданы паспортные данные", E_USER_ERROR);
        }
    }

    private function SearchByName($array, $name) {
        foreach ($array as $key => $value) {
            if ($value->name == $name)
                return $key;
        }
        return FALSE;
    }

    abstract public function parseFromString($xmlString);
}

?>