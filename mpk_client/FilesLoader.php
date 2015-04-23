<?php

/**
 * Класс для загрузки файлов
 * @author Vasiliy Yatsevitch <zwtdbx@yandex.ru>
 */

class FilesLoader {

    /**
     * Загружает файл с заданного URL
     * @static
     * @param string $url Адресс загружаемого файла
     * @return boolean TRUE, если файл успешно загружен, FALSE в ином случае
     */
    static function LoadFile($url) {
        $file = file_get_contents($url);
        if ($file === FALSE){
            trigger_error("Запрошенный файл не существует", E_USER_ERROR);
            return FALSE;
        }
        return $file;
    }

}
