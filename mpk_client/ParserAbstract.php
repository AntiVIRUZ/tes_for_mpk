<?php

abstract class ParserAbstract {
    
    public function parseFromUrl($url) {
        $xmlString = FilesLoader::LoadFile($url);
        return $this->parseFromString($xmlString);
    }
    
    abstract public function parseFromString($xmlString);
}

?>