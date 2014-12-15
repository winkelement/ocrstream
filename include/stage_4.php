<?php
SESSION_START();

//  Stage 4 - The Texter
//
//
require_once "../../../include/db.php";
require_once "../../../include/general.php";
require_once "../../../include/resource_functions.php";
require_once "../include/ocrstream_functions.php";

// Get ID
$ref_id = filter_input(INPUT_GET, 'ref', FILTER_VALIDATE_INT);
if ($_SESSION["ocr_stage_" . $ref_id] != 3) {
    exit(json_encode('Error: stage 3 not completed.'));
}

$ocr_temp_dir = $_SESSION['ocr_temp_dir'];
$ocr_output_file = $ocr_temp_dir . '/ocr_output_file_' . $ref_id . '.txt';
$tess_content = trim(file_get_contents($ocr_output_file));

if ($use_ocr_db_filter == true) {
// Filter extracted content
    $filter1 = preg_replace($ocr_db_filter_1, '$1', $tess_content);
    $filter2 = preg_replace($ocr_db_filter_2, '$1', $filter1);
    update_field($ref_id, $ocr_ftype_1, $filter2);
} else {
    update_field($ref_id, $ocr_ftype_1, $tess_content);
}

update_xml_metadump($ref_id);

// Set OCR state flag
$ocr_state = 2;
sql_query("UPDATE resource SET ocr_state =  '$ocr_state' WHERE ref = '$ref_id'");

// Return extracted text as JSON
//echo json_encode($tess_content);

// Delete temp files
array_map('unlink', glob("$ocr_temp_dir/ocr*.*")); //debug, uncomment for productive system
array_map('unlink', glob("$ocr_temp_dir/im*")); //debug, uncomment for productive system

$_SESSION['ocr_stage_' . $ref_id] = 4;

$debug = json_encode('OCR Stage ' . $_SESSION["ocr_stage_" . $ref_id] . ' completed ' .$ref_id. ' ' .$_SESSION["ocr_force_processing_" . $ref_id].' '.$_SESSION["ocr_force_language_" . $ref_id]);
echo $debug; //debug

// Clear all SESSION Variables
session_unset();

exit();

