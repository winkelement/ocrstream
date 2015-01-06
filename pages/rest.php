<?php
require_once "../../../include/db.php";
require_once "../../../include/general.php";

$ref = filter_input(INPUT_GET, 'ref', FILTER_VALIDATE_INT);
$ocr_state = filter_input(INPUT_GET, 'ocr_state', FILTER_VALIDATE_INT);
$ocr_state_query = filter_input(INPUT_GET, 'ocr_state_query', FILTER_VALIDATE_INT);

if (isset($ref) && isset($ocr_state)) {
    sql_query("UPDATE resource SET ocr_state =  '$ocr_state' WHERE ref = '$ref'");
}

if (isset($ref) && isset($ocr_state_query)) {
    $ocr_db_state =  sql_value("SELECT ocr_state value FROM resource WHERE ref = '$ref'", '');
    $ocr_db_state === '' ? $ocr_state = 0 : $ocr_state = $ocr_db_state;
    echo ($ocr_state);
}