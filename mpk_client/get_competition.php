<?php

include 'ClHandler.php';
include 'Competition.php';

$clParser = new ClHandler();
if (!$clParser->setNewArguments($argv)) {
    die;
}

$competition = new Competition();
$competition->SetParticipantsFromURL($clParser->inputType, $clParser->url);
if ($clParser->destination == "db" || $clParser->destination == "dbcsv") {
    if ($competition->CreateConnectionToDB()) {
        if (!$competition->SaveParticipants("db")) echo "ERROR in saving\n";
    } else echo "ERROR in connection\n";
} else {
    $competition->SaveParticipants($clParser->destination);
}