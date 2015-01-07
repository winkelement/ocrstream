<?php
//  Stage 1 - The Checker
//
//
$start_1 = microtime(true);

if (!isset($_SESSION["ocr_start"])) {
    require_once "../../../include/db.php";
    require_once "../../../include/general.php";
    require_once "../../../include/authenticate.php";
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

global $imagemagick_path;
global $ocr_min_density;
global $ocr_max_density;
global $ocr_min_geometry;
global $ocr_max_geometry;
global $lang;

// Checking if Resource ID is valid INTEGER and exists in database
if ($ref == null || $ref < 1 || $ref > sql_value("SELECT ref value FROM resource ORDER BY ref DESC LIMIT 1", '')) {
    exit(json_encode(array("error" => $lang['ocr_error_1'])));
}

if (isset($_SESSION['ocr_lang'])) {
    $ocr_lang = $_SESSION['ocr_lang'];
} else {
    $ocr_lang = filter_input(INPUT_GET, 'ocr_lang');
}

$_SESSION["ocr_force_processing_" . $ref] = 0;
$_SESSION["ocr_force_language_" . $ref] = 0;
$_SESSION["ocr_stage_" . $ref] = 0;

// Get original file extension
$ext = get_file_extension($ref);
$_SESSION['ocr_file_extension_' . $ref] = $ext;

$resource_path = get_resource_path($ref, true, "", false, $ext); // get complete path to original file with extension
$_SESSION['ocr_resource_path_' . $ref] = $resource_path;

// Check if file extension is allowed for ocr processing
if (!in_array($ext, $ocr_allowed_extensions)){
    exit(json_encode(array("error" => $lang['ocr_error_2'])));
}

// Check if resourcetype is document
if (sql_value("select resource_type value from resource where ref ='$ref'", '') != 2){
    exit(json_encode(array("error" => $lang['ocr_error_4'])));
}

// Check if density (dpi) and geometry (px) is in margin for ocr processing, skip for pdf
// Ignore 72 dpi values (Screen resolution)
// @todo check units (inch/centimeter) to prevent false detection
if ($ext !== 'pdf') {
    $density = run_command($imagemagick_path . '/identify -format "%y" ' . '' . $resource_path . ' 2>&1');
    if (intval($density) < $ocr_min_density && intval($density) !== 72) {
        exit(json_encode(array("error" => $lang['ocr_error_3'])));
    }
    if (intval($density) > $ocr_max_density) {
        $_SESSION["ocr_force_processing_" . $ref] = 1; // Force image procesing if density too high
    }
    $geometry = sql_value("SELECT width value FROM resource_dimensions WHERE resource ='$ref'", '');
    if (intval($geometry) < $ocr_min_geometry) {
        exit(json_encode(array("error" => $lang['ocr_error_5'])));
    }
    if (intval($geometry) > $ocr_max_geometry) {
        $_SESSION["ocr_force_processing_" . $ref] = 1; // Force image procesing if width too high
    }
}

// Force image processing if filetype is pdf
// Needs to be set for file uploads where param_1 = none
if ($ext === 'pdf') {
    $_SESSION["ocr_force_processing_" . $ref] = 1;
}
// If language parameter is not valid, choose global ocr language setting
$tesseract_languages = get_tesseract_languages();
if (array_search($ocr_lang, $tesseract_languages) == false) {
    $_SESSION["ocr_force_language_" . $ref] = 1;
}

$_SESSION["ocr_stage_" . $ref] = 1;

// Measure execution time for stage 1
$elapsed_1 = round((microtime(true) - $start_1), 3);
$_SESSION["ocr_stage_1_time"] = $elapsed_1;

$debug = json_encode('OCR Stage ' . $_SESSION["ocr_stage_" . $ref] . '/4 completed: ' .$ref. ' ext: ' .$ext . ' Time: ' . $elapsed_1);
echo $debug; //debug
//return($debug);