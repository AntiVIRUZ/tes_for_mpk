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
     * @return mixed содержимое файла, если он успешно загружен, FALSE в ином случае
     */
    static function LoadFile($url) {
        $file = file_get_contents($url);
        return $file;
    }

}
