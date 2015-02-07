<?php
//  Stage 3 - The Recognizer
//
//
$start_3 = microtime(true);

require_once "../../../include/db.php";
require_once "../../../include/general.php";
require_once "../../../include/authenticate.php";
require_once "../../../include/resource_functions.php";
require_once "../include/ocrstream_functions.php";
spl_autoload_register(function ($class) {
include '../lib/Process/' . $class . '.php';
});

$ID = filter_input(INPUT_GET, 'ref', FILTER_VALIDATE_INT);
$ocr_lang = filter_input(INPUT_GET, 'ocr_lang');
$ocr_psm = filter_input(INPUT_GET, 'ocr_psm');
$param_1 = filter_input(INPUT_GET, 'param_1');

if (is_session_started() === FALSE) {
    session_start();
}

if ($_SESSION["ocr_stage_" . $ID] !== 2) {
    session_unset();
    exit(json_encode(array("error" => $lang['ocr_error_stage_2'])));
}

// Get number of pages
$resource = get_resource_data($ID);
$pg_num = get_page_count($resource, -1);

$ocr_temp_dir = $_SESSION['ocr_temp_dir'];

// Check for language override flag
if ($_SESSION["ocr_force_language_" . $ID] === 1){
    $ocr_lang = trim($ocr_global_language);
}

// OCR multi pages, processed, tesseract > v3.0.3
if ($pg_num > 1 && tesseract_version_is_old() === false) {
    $mode = 'multipage_new';
}
// OCR multi pages, processed, tesseract < v3.0.3
elseif ($pg_num > 1 && tesseract_version_is_old() === true) { 
    $mode = 'multipage_old';
}
// OCR single page processed
elseif (($param_1 === 'pre_1' && $pg_num === '1') || ($_SESSION["ocr_force_processing_" . $ID] === 1 && $pg_num === '1')) {
    $mode = 'single_processed';
}
// OCR single page original
elseif ($param_1 === 'none' && $_SESSION["ocr_force_processing_" . $ID] !== 1) {
    $mode = 'single_original';
}

tesseract_processing($ID, $ocr_lang , $ocr_psm, $ocr_temp_dir, $mode, $pg_num);

$_SESSION["ocr_stage_" . $ID] = 3;

// Measure execution time for stage 3
$elapsed_3 = round((microtime(true) - $start_3), 3);
$_SESSION["ocr_stage_3_time"] = $elapsed_3;

$debug = ('OCR Stage ' . $_SESSION["ocr_stage_" . $ID] . '/4 completed: ' .$ID. ' lang: ' .$ocr_lang. ' PSM: '.$ocr_psm . ' Time: ' . $elapsed_3);

echo json_encode(array($ID, $debug));