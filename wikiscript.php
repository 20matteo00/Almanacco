<?php
include_once 'Class/Parser.php';


function go ($comp, $annoI, $annoF) {
    for ($anno = $annoI; $anno < $annoF; $anno++) {
        $stringa = $comp . '_' . $anno . "-" . $anno + 1;
        $parser = new Parser($stringa, $comp);
        $parser->esegui();
    }
}

go("Serie_A", 1929, 2025);
go("Serie_B", 1929, 2025);