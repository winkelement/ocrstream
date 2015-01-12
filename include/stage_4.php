<?php
//  Stage 4 - The Texter
//
//
$start_4 = microtime(true);

if (!isset($_SESSION["ocr_start"])) {
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
//    require_once "../include/authenticate.php";
    require_once "../include/resource_functions.php";
    require_once "../plugins/ocrstream/include/ocrstream_functions.php";
}

if (is_session_started() === FALSE) {
    session_start();
}

if ($_SESSION["ocr_stage_" . $ref] !== 3) {
    session_unset();
    exit(json_encode(array("error" => $lang['ocr_error_stage_3'])));
}

$ocr_temp_dir = $_SESSION['ocr_temp_dir'];
$ocr_output_file = $ocr_temp_dir . '/ocr_output_file_' . $ref . '.txt';
if (!file_exists($ocr_output_file)) {
    session_unset();
    exit(json_encode(array("error" => $lang['ocr_error_7'])));
}
$tess_content = trim(file_get_contents($ocr_output_file));

// Writing Keywords to database can take time so increase PHP timeout
set_time_limit(1800);

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

// Measure execution time for stage 3
$elapsed_4 = round((microtime(true) - $start_4), 3);
$_SESSION["ocr_stage_4_time"] = $elapsed_4;
$ocr_total_time = ($_SESSION["ocr_stage_1_time"]) + ($_SESSION["ocr_stage_2_time"]) + ($_SESSION["ocr_stage_3_time"]) + ($_SESSION["ocr_stage_4_time"]);
$_SESSION["ocr_total_time"] = $ocr_total_time;

$debug = json_encode('OCR Stage ' . $_SESSION["ocr_stage_" . $ref] . '/4 completed: ' .$ref . ' Time: ' . $elapsed_4 . ' Total Time: ' . $ocr_total_time);
echo $debug; //debug

// Clear all SESSION Variables for single resource OCR or if last file in queque has been uploaded
$end_of_queque = getval('lastqueued', '');
if ($end_of_queque == true || !isset($_SESSION["ocr_start"])){
    session_unset();
}