<?php
//  Stage 1 - The Checker
//
//
require_once "../../../include/db.php";
require_once "../../../include/general.php";
require_once "../../../include/resource_functions.php";
require_once "../include/ocrstream_functions.php";

SESSION_START();

$_SESSION["ocr_force_processing"] = 0;
$_SESSION["ocr_force_language"] = 0;

// Get Input
$ref_id = filter_input(INPUT_GET, 'ref', FILTER_VALIDATE_INT);
$ocr_lang = filter_input(INPUT_GET, 'ocr_lang');

// Get original file extension
$ext = sql_value("select file_extension value from resource where ref = '$ref_id'", '');

// Checking if Resource ID is valid INTEGER and exists in database
if ($ref_id == null || $ref_id < 1 || $ref_id > sql_value("SELECT ref value FROM resource ORDER BY ref DESC LIMIT 1", '')) {
    exit(json_encode('ocr_error_1'));
}

// Check if file extension is allowed for ocr processing
if (!in_array($ext, $ocr_allowed_extensions)){
    exit(json_encode('ocr_error_2'));
}

// Check if resourcetype is document
if (sql_value("select resource_type value from resource where ref ='$ref_id'", '') != 2){
    exit(json_encode('ocr_error_4'));
}

// Check if density (dpi) is in margin for ocr processing, skip for pdf 
// @todo check units (inch/centimeter) to prevent false detection
/* @var $resource_path Path */
/* @var $density ImageProperties */
$resource_path = get_resource_path($ref_id, true, "", false, $ext); // get complete path to original file with extension
if ($ext != 'pdf') {
    $density = run_command($imagemagick_path . '/identify -format "%y" ' . '' . $resource_path . ' 2>&1');
    if (intval($density) < $ocr_min_density) {
        exit(json_encode('ocr_error_3'));
    }
    if (intval($density) > $ocr_max_density) {
        $_SESSION["ocr_force_processing"] = 1; // Force image procesing if density too high 
    }
}

// If language parameter is not valid, choose global ocr language setting
$tesseract_languages = get_tesseract_languages();
if (array_search($ocr_lang, $tesseract_languages) == false) {
    $_SESSION["ocr_force_language"] = 1;
}

$_SESSION["ocr_stage"] = 1;

$debug = json_encode('OCR Stage ' . $_SESSION["ocr_stage"] . ' completed ' .$ref_id. ' ' .$ext. ' ' .$ocr_lang. ' ' .$_SESSION["force_processing"].' '.$_SESSION["ocr_force_language"]);
echo $debug; //debug
exit();