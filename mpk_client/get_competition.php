<?php

include 'ClHandler.php';
include 'Competition.php';

$clParser = new ClHandler();
if (!$clParser->setNewArguments($argv)) {
    die;
}

$competition = new Competition();
$competition->GetParticipantsFromURL($clParser->inputType, $clParser->url);
if ($clParser->destination == "db" || $clParser->destination == "dbcsv") {
    $competition->CreateConnectionToDB();
    $competition->SaveParticipants("db");
} else {
    $competition->SaveParticipants($clParser->destination);
}