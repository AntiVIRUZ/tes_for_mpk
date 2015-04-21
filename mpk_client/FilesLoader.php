<?php

class FilesLoader {

    static function LoadFile($url) {
        $file = file_get_contents($url);
        if ($file === FALSE){
            trigger_error("Запрошенный файл не существует", E_USER_ERROR);
            return FALSE;
        }
        return $file;
    }

}
