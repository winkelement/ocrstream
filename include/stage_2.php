<?php

require_once "../../../include/db.php";

SESSION_START();

if ($_SESSION["ocr_stage"] != 1){
    exit(json_encode('Error: stage 1 not completed.'));
}
$ref_id = filter_input(INPUT_GET, 'ref', FILTER_VALIDATE_INT);
$ocr_lang = filter_input(INPUT_GET, 'ocr_lang');
$ocr_psm = filter_input(INPUT_GET, 'ocr_psm');
/* @var $param_1 ImagemagickPreset */
$param_1 = filter_input(INPUT_GET, 'param_1');
// Get cropping values width, height, offset_x, offset_y
$im_preset_1_crop_w = filter_input(INPUT_GET, 'w');
$im_preset_1_crop_h = filter_input(INPUT_GET, 'h');
$im_preset_1_crop_x = filter_input(INPUT_GET, 'x');
$im_preset_1_crop_y = filter_input(INPUT_GET, 'y');
$ext = sql_value("select file_extension value from resource where ref = '$ref_id'", '');
sleep(3);
$_SESSION["ocr_stage"] = 2;
$debug = json_encode('OCR Stage ' . $_SESSION["ocr_stage"] . ' completed ' .$ref_id. ' ' .$ext. ' ' .$ocr_lang. ' ' .$ocr_psm. ' '.$param_1. ' '.$im_preset_1_crop_w.' '.$im_preset_1_crop_h.' '.$im_preset_1_crop_x.' '.$im_preset_1_crop_y);
echo $debug; //debug
exit();

