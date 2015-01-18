<?php
//  Stage 4 - The Texter
//
//
$start_4 = microtime(true);

require_once "../../../include/db.php";
require_once "../../../include/authenticate.php";
require_once "../../../include/general.php";
require_once "../../../include/resource_functions.php";
require_once "../include/ocrstream_functions.php";
$ID = filter_input(INPUT_GET, 'ref', FILTER_VALIDATE_INT);

if (is_session_started() === FALSE) {
    session_start();
}

if ($_SESSION["ocr_stage_" . $ID] !== 3) {
    session_unset();
    exit(json_encode(array("error" => $lang['ocr_error_stage_3'])));
}

$ocr_temp_dir = $_SESSION['ocr_temp_dir'];
$ocr_output_file = $ocr_temp_dir . '/ocr_output_file_' . $ID . '.txt';
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
    update_field($ID, $ocr_ftype_1, $filter2);
} else {
    update_field($ID, $ocr_ftype_1, $tess_content);
}

update_xml_metadump($ID);

//  Set OCR state flag
//  ocr_state = 1 : file flagged for ocr processing
//  ocr_state = 2 : ocr on this file has been completed
$ocr_state = 2;
sql_query("UPDATE resource SET ocr_state =  '$ocr_state' WHERE ref = '$ID'");

// Delete temp files
array_map('unlink', glob("$ocr_temp_dir/ocr_output_file_$ID.txt"));
array_map('unlink', glob("$ocr_temp_dir/im_tempfile_$ID.*"));

$_SESSION['ocr_stage_' . $ID] = 4;

// Measure execution time for stage 3
$elapsed_4 = round((microtime(true) - $start_4), 3);
$_SESSION["ocr_stage_4_time"] = $elapsed_4;
$ocr_total_time = ($_SESSION["ocr_stage_1_time"]) + ($_SESSION["ocr_stage_2_time"]) + ($_SESSION["ocr_stage_3_time"]) + ($_SESSION["ocr_stage_4_time"]);
$_SESSION["ocr_total_time"] = $ocr_total_time;

$nextID = ($ID +1);

if (isset($_SESSION['ocr_stage_' . $nextID])) {
    if (($_SESSION['ocr_stage_' . $nextID]) < 4) {
        $end_of_queue = false;
    } else {
        $end_of_queue = true;
    }
} else {
    $end_of_queue = true;
}

$debug = ('OCR Stage ' . $_SESSION["ocr_stage_" . $ID] . '/4 completed: ' .$ID . ' Time: ' . $elapsed_4 . ' Total Time: ' . $ocr_total_time . ' End of Queue: ' .$end_of_queue);
echo json_encode(array($end_of_queue, $debug));

// Clear all SESSION Variables for single resource OCR or if all resources in queue are completed

if ($end_of_queue == true || !isset($_SESSION["ocr_start"])){
    session_unset();
}