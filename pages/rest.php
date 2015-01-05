<?php
require_once "../../../include/db.php";
require_once "../../../include/general.php";

$ref = filter_input(INPUT_GET, 'ref', FILTER_VALIDATE_INT);
$ocr_state = filter_input(INPUT_GET, 'ocr_state', FILTER_VALIDATE_INT);

if ($ref && $ocr_state) {
    sql_query("UPDATE resource SET ocr_state =  '$ocr_state' WHERE ref = '$ref'");
}