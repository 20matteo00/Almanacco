<?php
include_once 'Class/Parser.php';

$comp = "Serie_A";
$annoI = 1929;
$annoF = 2025;

for ($anno = $annoI; $anno < $annoF; $anno++) {
    $stringa = $comp . '_' . $anno . "-" . $anno + 1;
    $parser = new Parser($stringa);
    $parser->esegui();
}