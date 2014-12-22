<?php
SESSION_START();

//  Stage 3 - The Recognizer
//
//
require_once "../../../include/db.php";
require_once "../../../include/general.php";
require_once "../../../include/resource_functions.php";
require_once "../include/ocrstream_functions.php";

// Get Input Values
$ref_id = filter_input(INPUT_GET, 'ref', FILTER_VALIDATE_INT);
if ($_SESSION["ocr_stage_" . $ref_id] != 2) {
    exit(json_encode('Error: stage 2 not completed.'));
}
$ocr_lang = filter_input(INPUT_GET, 'ocr_lang');
$ocr_psm = filter_input(INPUT_GET, 'ocr_psm');
$param_1 = filter_input(INPUT_GET, 'param_1');

// Get number of pages
$resource = get_resource_data($ref_id);
$pg_num = get_page_count($resource, -1);

$ocr_temp_dir = $_SESSION['ocr_temp_dir'];
$tesseract_fullpath = get_tesseract_fullpath();
$_SESSION['ocr_tesseract_fullpath'] = $tesseract_fullpath;

// Check for language override flag
if ($_SESSION["ocr_force_language_" . $ref_id] === 1){
    $ocr_lang = $ocr_global_language;
}

// OCR multi pages, processed, tesseract > v3.0.3
if ($pg_num > 1 && tesseract_version_is_old() === false) { 
    $n = 0;
    while ($n < $pg_num) {
        file_put_contents($ocr_temp_dir . '/im_ocr_file_' . $ref_id, trim($ocr_temp_dir . '/im_tempfile_' . $ref_id . '-' . $n . '.jpg').PHP_EOL, FILE_APPEND);
        $n++;
    }
    $tess_cmd = ($tesseract_fullpath . ' ' . $ocr_temp_dir . '/im_ocr_file_' . $ref_id . ' ' . escapeshellarg($ocr_temp_dir . '/ocr_output_file_' . $ref_id) . ' -l ' . $ocr_lang.' -psm ' . $ocr_psm);
//    $process = new Process($tess_cmd);
//    $process->run();
    run_command($tess_cmd);
}
// OCR multi pages, processed, tesseract < v3.0.3
if ($pg_num > 1 && tesseract_version_is_old() === true) { 
    $i = 0;
    while ($i < $pg_num) {
        $ocr_input_file = ($ocr_temp_dir . '/im_tempfile_' . $ref_id . '-' . $i . '.jpg');
        $tess_cmd = ($tesseract_fullpath . ' ' . $ocr_input_file . ' ' . escapeshellarg($ocr_temp_dir . '/ocrtempfile_' . $ref_id) . ' -l ' . $ocr_lang.' -psm ' . $ocr_psm);
//        $process = new Process($tess_cmd);
//        $process->run();
        run_command($tess_cmd);
        file_put_contents($ocr_temp_dir . '/ocr_output_file_' . $ref_id . '.txt', file_get_contents($ocr_temp_dir . '/ocrtempfile_' . $ref_id . '.txt'), FILE_APPEND);
        $i ++;
    }
}
// OCR single page processed
if ($param_1 === 'pre_1' && $pg_num === '1') {
    $ocr_input_file = ($ocr_temp_dir . '/im_tempfile_' . $ref_id . '.jpg');
    $tess_cmd = ($tesseract_fullpath . ' ' . $ocr_input_file . ' ' . escapeshellarg($ocr_temp_dir . '/ocr_output_file_' . $ref_id) . ' -l ' . $ocr_lang.' -psm ' . $ocr_psm);
//    $process = new Process($tess_cmd);
//    $process->run();
    run_command($tess_cmd);
}
// OCR single page original
if ($param_1 === 'none' && $_SESSION["ocr_force_processing_" . $ref_id] != 1) {
    $ext = $_SESSION['ocr_file_extension_' . $ref_id];
    $resource_path = $_SESSION['ocr_resource_path_' . $ref_id];
    $tess_cmd = ($tesseract_fullpath . ' ' . $resource_path . ' ' . escapeshellarg($ocr_temp_dir . '/ocr_output_file_' . $ref_id) . ' -l ' . $ocr_lang.' -psm ' . $ocr_psm);
//    $process = new Process($tess_cmd);
//    $process->run();
    run_command($tess_cmd);
}

$_SESSION["ocr_stage_" . $ref_id] = 3;

$debug = json_encode('OCR Stage ' . $_SESSION["ocr_stage_" . $ref_id] . ' completed ' .$ref_id. ' ' .$ocr_lang. ' ' .$_SESSION["ocr_force_processing_" . $ref_id].' '.$_SESSION["ocr_force_language_" . $ref_id]. ' '.$ocr_psm);
echo $debug; //debug
exit();