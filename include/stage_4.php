<?php
//  Stage 4 - The Texter
//
//
if (!isset($_SESSION["ocr_start"])) {
    SESSION_START();
    require_once "../../../include/db.php";
    require_once "../../../include/authenticate.php";
    require_once "../../../include/general.php";
    require_once "../../../include/resource_functions.php";
    require_once "../include/ocrstream_functions.php";
    $ref = filter_input(INPUT_GET, 'ref', FILTER_VALIDATE_INT);
}
else {
    require_once "../include/db.php";
    require_once "../include/general.php";
    require_once "../include/authenticate.php";
    require_once "../include/resource_functions.php";
    require_once "../plugins/ocrstream/include/ocrstream_functions.php";
}

// Get ID
//$ref = filter_input(INPUT_GET, 'ref', FILTER_VALIDATE_INT);
if ($_SESSION["ocr_stage_" . $ref] != 3) {
    exit(json_encode('Error: stage 3 not completed.'));
}

$ocr_temp_dir = $_SESSION['ocr_temp_dir'];
$ocr_output_file = $ocr_temp_dir . '/ocr_output_file_' . $ref . '.txt';
$tess_content = trim(file_get_contents($ocr_output_file));

if ($use_ocr_db_filter == true) {
// Filter extracted content
    $filter1 = preg_replace($ocr_db_filter_1, '$1', $tess_content);
    $filter2 = preg_replace($ocr_db_filter_2, '$1', $filter1);
    update_field($ref, $ocr_ftype_1, $filter2);
} else {
    update_field($ref, $ocr_ftype_1, $tess_content);
}

update_xml_metadump($ref);

//  Set OCR state flag
//  ocr_state = 1 : file flagged for ocr processing
//  ocr_state = 2 : ocr on this file has been completed
$ocr_state = 2;
sql_query("UPDATE resource SET ocr_state =  '$ocr_state' WHERE ref = '$ref'");

// Return extracted text as JSON
//echo json_encode($tess_content);

// Delete temp files
array_map('unlink', glob("$ocr_temp_dir/ocr*.*")); //debug, uncomment for productive system
array_map('unlink', glob("$ocr_temp_dir/im*")); //debug, uncomment for productive system

$_SESSION['ocr_stage_' . $ref] = 4;

$debug = json_encode('OCR Stage ' . $_SESSION["ocr_stage_" . $ref] . ' completed: ' .$ref);
echo $debug; //debug

// Clear all SESSION Variables for single resource OCR or if last file in queque has been uploaded
$end_of_queque = getval('lastqueued', '');
if ($end_of_queque == true || !isset($_SESSION["ocr_start"])){
    session_unset();
}

//return($debug);

