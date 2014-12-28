<?php
//  Stage 1 - The Checker
//
//
if (!isset($_SESSION["ocr_start"])) {
    SESSION_START();
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
    require_once "../include/authenticate.php";
    require_once "../include/resource_functions.php";
    require_once "../plugins/ocrstream/include/ocrstream_functions.php";
}

global $imagemagick_path;
global $ocr_min_density;
global $ocr_max_density;

// Get Input
//$ref = filter_input(INPUT_GET, 'ref', FILTER_VALIDATE_INT);
if (isset($_SESSION['ocr_lang'])) {
    $ocr_lang = $_SESSION['ocr_lang'];
} else {
    $ocr_lang = filter_input(INPUT_GET, 'ocr_lang');
}

$_SESSION["ocr_force_processing_" . $ref] = 0;
$_SESSION["ocr_force_language_" . $ref] = 0;
$_SESSION["ocr_stage_" . $ref] = 0;

// Get original file extension
$ext = sql_value("select file_extension value from resource where ref = '$ref'", '');
$_SESSION['ocr_file_extension_' . $ref] = $ext;

$resource_path = get_resource_path($ref, true, "", false, $ext); // get complete path to original file with extension
$_SESSION['ocr_resource_path_' . $ref] = $resource_path;

// Checking if Resource ID is valid INTEGER and exists in database
if ($ref == null || $ref < 0 || $ref > sql_value("SELECT ref value FROM resource ORDER BY ref DESC LIMIT 1", '')) {
    exit(json_encode('ocr_error_1'));
}

// Check if file extension is allowed for ocr processing
if (!in_array($ext, $ocr_allowed_extensions)){
    exit(json_encode('ocr_error_2'));
}

// Check if resourcetype is document
if (sql_value("select resource_type value from resource where ref ='$ref'", '') != 2){
    exit(json_encode('ocr_error_4'));
}

// Check if density (dpi) is in margin for ocr processing, skip for pdf 
// @todo check units (inch/centimeter) to prevent false detection
if ($ext != 'pdf') {
    $density = run_command($imagemagick_path . '/identify -format "%y" ' . '' . $resource_path . ' 2>&1');
    if (intval($density) < $ocr_min_density) {
        exit(json_encode('ocr_error_3'));
    }
    if (intval($density) > $ocr_max_density) {
        $_SESSION["ocr_force_processing_" . $ref] = 1; // Force image procesing if density too high 
    }
}

// If language parameter is not valid, choose global ocr language setting
$tesseract_languages = get_tesseract_languages();
if (array_search($ocr_lang, $tesseract_languages) == false) {
    $_SESSION["ocr_force_language_" . $ref] = 1;
}

$_SESSION["ocr_stage_" . $ref] = 1;

$debug = json_encode('OCR Stage ' . $_SESSION["ocr_stage_" . $ref] . ' completed: ' .$ref. ' ext: ' .$ext);
echo $debug; //debug
//return($debug);