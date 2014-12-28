<?php
//  Stage 3 - The Recognizer
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
    $ocr_lang = filter_input(INPUT_GET, 'ocr_lang');
    $ocr_psm = filter_input(INPUT_GET, 'ocr_psm');
    $param_1 = filter_input(INPUT_GET, 'param_1');
}
else {
    require_once "../include/db.php";
    require_once "../include/general.php";
    require_once "../include/authenticate.php";
    require_once "../include/resource_functions.php";
    require_once "../plugins/ocrstream/include/ocrstream_functions.php";
    $ocr_lang = $_SESSION['ocr_lang'];
    $ocr_psm = $_SESSION['ocr_psm'];
    $param_1 = 'pre_1';
}

// Get Input Values
//$ref = filter_input(INPUT_GET, 'ref', FILTER_VALIDATE_INT);
if ($_SESSION["ocr_stage_" . $ref] != 2) {
    exit(json_encode('Error: stage 2 not completed.'));
}
//$ocr_lang = filter_input(INPUT_GET, 'ocr_lang');
//$ocr_psm = filter_input(INPUT_GET, 'ocr_psm');
//$param_1 = filter_input(INPUT_GET, 'param_1');

// Get number of pages
$resource = get_resource_data($ref);
$pg_num = get_page_count($resource, -1);

$ocr_temp_dir = $_SESSION['ocr_temp_dir'];
$tesseract_fullpath = get_tesseract_fullpath();
$_SESSION['ocr_tesseract_fullpath'] = $tesseract_fullpath;

// Check for language override flag
if ($_SESSION["ocr_force_language_" . $ref] === 1){
    $ocr_lang = $ocr_global_language;
}

// OCR multi pages, processed, tesseract > v3.0.3
if ($pg_num > 1 && tesseract_version_is_old() === false) { 
    $n = 0;
    while ($n < $pg_num) {
        file_put_contents($ocr_temp_dir . '/im_ocr_file_' . $ref, trim($ocr_temp_dir . '/im_tempfile_' . $ref . '-' . $n . '.jpg').PHP_EOL, FILE_APPEND);
        $n++;
    }
    $tess_cmd = ($tesseract_fullpath . ' ' . $ocr_temp_dir . '/im_ocr_file_' . $ref . ' ' . escapeshellarg($ocr_temp_dir . '/ocr_output_file_' . $ref) . ' -l ' . $ocr_lang.' -psm ' . $ocr_psm);
//    $process = new Process($tess_cmd);
//    $process->run();
    run_command($tess_cmd);
}
// OCR multi pages, processed, tesseract < v3.0.3
if ($pg_num > 1 && tesseract_version_is_old() === true) { 
    $i = 0;
    while ($i < $pg_num) {
        $ocr_input_file = ($ocr_temp_dir . '/im_tempfile_' . $ref . '-' . $i . '.jpg');
        $tess_cmd = ($tesseract_fullpath . ' ' . $ocr_input_file . ' ' . escapeshellarg($ocr_temp_dir . '/ocrtempfile_' . $ref) . ' -l ' . $ocr_lang.' -psm ' . $ocr_psm);
//        $process = new Process($tess_cmd);
//        $process->run();
        run_command($tess_cmd);
        file_put_contents($ocr_temp_dir . '/ocr_output_file_' . $ref . '.txt', file_get_contents($ocr_temp_dir . '/ocrtempfile_' . $ref . '.txt'), FILE_APPEND);
        $i ++;
    }
}
// OCR single page processed
if ($param_1 === 'pre_1' && $pg_num === '1') {
    $ocr_input_file = ($ocr_temp_dir . '/im_tempfile_' . $ref . '.jpg');
    $tess_cmd = ($tesseract_fullpath . ' ' . $ocr_input_file . ' ' . escapeshellarg($ocr_temp_dir . '/ocr_output_file_' . $ref) . ' -l ' . $ocr_lang.' -psm ' . $ocr_psm);
//    $process = new Process($tess_cmd);
//    $process->run();
    run_command($tess_cmd);
}
// OCR single page original
if ($param_1 === 'none' && $_SESSION["ocr_force_processing_" . $ref] != 1) {
    $ext = $_SESSION['ocr_file_extension_' . $ref];
    $resource_path = $_SESSION['ocr_resource_path_' . $ref];
    $tess_cmd = ($tesseract_fullpath . ' ' . $resource_path . ' ' . escapeshellarg($ocr_temp_dir . '/ocr_output_file_' . $ref) . ' -l ' . $ocr_lang.' -psm ' . $ocr_psm);
//    $process = new Process($tess_cmd);
//    $process->run();
    run_command($tess_cmd);
}

$_SESSION["ocr_stage_" . $ref] = 3;

$debug = json_encode('OCR Stage ' . $_SESSION["ocr_stage_" . $ref] . ' completed: ' .$ref. ' lang: ' .$ocr_lang. ' PSM: '.$ocr_psm);
echo $debug; //debug
//return($debug);