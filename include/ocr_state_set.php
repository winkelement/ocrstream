<?php
require_once "../../../include/db.php";
require_once "../../../include/general.php";

$ref = filter_input(INPUT_GET, 'ref', FILTER_VALIDATE_INT);
$ocr_state = filter_input(INPUT_GET, 'ocr_state', FILTER_VALIDATE_INT);

sql_query("UPDATE resource SET ocr_state =  '$ocr_state' WHERE ref = '$ref'");

$new_state = sql_value("SELECT ocr_state value FROM resource WHERE ref = '$ref'", '');

echo ($new_state);
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

