<?php

abstract class ParserAbstract {

    private $lastError;

    public function parseFromUrl($url) {
        $xmlString = FilesLoader::LoadFile($url);
        return $this->parseFromString($xmlString);
    }

    public function getLastError() {
        return $this->lastError;
    }

    protected function VerifyArray($array) {
        $unique_sports_id = array();
        $unique_teams_id = array();
        $unique_participant_id = array();
        $unique_participant_to_team_id = array();

        //Проверяем уникальность ID и наличие данных в каждом поле
        foreach ($array["sports_kinds"] as $value) {
            if (!isset($value["id"])) {
                $this->lastError = "Есть вид спорта без id";
                return FALSE;
            }
            if (isset($unique_sports_id[$value["id"]])) {
                $this->lastError = "Среди видов спорта есть неуникальный id (" . $value["id"] . ")";
                return FALSE;
            } else {
                $unique_sports_id[$value["id"]] = 1;
            }
            if (!$value["name"]) {
                $this->lastError = "У вида спорта с id " . $value["id"] . " нет имени";
                return FALSE;
            }
        }

        foreach ($array["teams"] as $value) {
            if (!isset($value["id"])) {
                $this->lastError = "Есть команда без id";
                return FALSE;
            }
            if (isset($unique_teams_id[$value["id"]])) {
                $this->lastError = "Среди команд есть неуникальный id (" . $value["id"] . ")";
                return FALSE;
            } else {
                $unique_teams_id[$value["id"]] = 1;
            }
            if (!$value["name"]) {
                $this->lastError = "У команды с id " . $value["id"] . " нет имени";
                return FALSE;
            }
            if (!$value["sports_kind_id"]) {
                $this->lastError = "Для команды с id " . $value["id"] . " не закреплен вид спорта";
                return FALSE;
            }
            if (!isset($unique_sports_id[$value["sports_kind_id"]])) {
                $this->lastError = "Для команды с id " . $value["id"] . " не существует вида спорта с id = " . $value["sports_kind_id"];
                return FALSE;
            }
        }

        foreach ($array["participants"] as $key => $value) {
            $unique_participant_to_team_id[$key] = array();
            if (!isset($value["id"])) {
                $this->lastError = "Есть учасник без id";
                return FALSE;
            }
            if (isset($unique_participant_id[$value["id"]])) {
                $this->lastError = "Среди команд есть неуникальный id (" . $value["id"] . ")";
                return FALSE;
            } else {
                $unique_participant_id[$value["id"]] = 1;
            }
            if (!$value["name"]) {
                $this->lastError = "У участника с id " . $value["id"] . " нет имени";
                return FALSE;
            }
            if (count($value["teams"]) == 0) {
                $this->lastError = "Участник с id " . $value["id"] . " не закреплен ни за одной командой";
                return FALSE;
            }
            foreach ($value["teams"] as $team_id) {
                if ($team_id == "") {
                    $this->lastError = "Среди команд участника с id " . $value["id"] . "есть пустые записи";
                    return FALSE;
                }
                if (!isset($unique_teams_id[$team_id])) {
                    $this->lastError = "Участник с id " . $value["id"] . " закреплен за несуществующей командой с id " . $team_id;
                    return FALSE;
                }
                if (isset($unique_participant_to_team_id[$key][$team_id])) {
                    $this->lastError = "Участник с id " . $value["id"] . " дважды прикреплен к команде с id " . $team_id;
                    return FALSE;
                } else {
                    $unique_participant_to_team_id[$key][$team_id] = 1;
                }
            }
        }
        return TRUE;
    }

    abstract public function parseFromString($xmlString);
}

?>