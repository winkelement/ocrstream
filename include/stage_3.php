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
$tesseract_fullpath = get_tesseract_fullpath();
$_SESSION['ocr_tesseract_fullpath'] = $tesseract_fullpath;

// Check for language override flag
if ($_SESSION["ocr_force_language_" . $ID] === 1){
    $ocr_lang = $ocr_global_language;
}

// OCR multi pages, processed, tesseract > v3.0.3
if ($pg_num > 1 && tesseract_version_is_old() === false) { 
    $n = 0;
    set_time_limit(1800);
    while ($n < $pg_num) {
        file_put_contents($ocr_temp_dir . '/im_ocr_file_' . $ID, trim($ocr_temp_dir . '/im_tempfile_' . $ID . '-' . $n . '.jpg').PHP_EOL, FILE_APPEND);
        $n++;
    }
    $tess_cmd = ($tesseract_fullpath . ' ' . $ocr_temp_dir . '/im_ocr_file_' . $ID . ' ' . escapeshellarg($ocr_temp_dir . '/ocr_output_file_' . $ID) . ' -l ' . $ocr_lang.' -psm ' . $ocr_psm);
    debug("CLI command: $tess_cmd");
    $process = new Process($tess_cmd);
    $process->setTimeout(3600);
    $process->setIdleTimeout(600);
    $process->run();
    debug ("CLI output: " . $process->getOutput());
    debug ("CLI errors: " . trim($process->getErrorOutput()));
//    run_command($tess_cmd);
}
// OCR multi pages, processed, tesseract < v3.0.3
if ($pg_num > 1 && tesseract_version_is_old() === true) { 
    $i = 0;
    set_time_limit(1800);
    while ($i < $pg_num) {
        $ocr_input_file = ($ocr_temp_dir . '/im_tempfile_' . $ID . '-' . $i . '.jpg');
        $tess_cmd = ($tesseract_fullpath . ' ' . $ocr_input_file . ' ' . escapeshellarg($ocr_temp_dir . '/ocrtempfile_' . $ID) . ' -l ' . $ocr_lang.' -psm ' . $ocr_psm);
        debug("CLI command: $tess_cmd");
        $process = new Process($tess_cmd);
        $process->setTimeout(3600);
        $process->setIdleTimeout(600);
        $process->run();
        debug ("CLI output: " . $process->getOutput());
        debug ("CLI errors: " . trim($process->getErrorOutput()));
//        run_command($tess_cmd);
        file_put_contents($ocr_temp_dir . '/ocr_output_file_' . $ID . '.txt', file_get_contents($ocr_temp_dir . '/ocrtempfile_' . $ID . '.txt'), FILE_APPEND);
        $i ++;
    }
}
// OCR single page processed
if (($param_1 === 'pre_1' && $pg_num === '1') || ($_SESSION["ocr_force_processing_" . $ID] === 1 && $pg_num === '1')) {
    $ocr_input_file = ($ocr_temp_dir . '/im_tempfile_' . $ID . '.jpg');
    $tess_cmd = ($tesseract_fullpath . ' ' . $ocr_input_file . ' ' . escapeshellarg($ocr_temp_dir . '/ocr_output_file_' . $ID) . ' -l ' . $ocr_lang.' -psm ' . $ocr_psm);
    debug("CLI command: $tess_cmd");
    $process = new Process($tess_cmd);
    $process->run();
    debug ("CLI output: " . $process->getOutput());
    debug ("CLI errors: " . trim($process->getErrorOutput()));
//    run_command($tess_cmd);
}
// OCR single page original
if ($param_1 === 'none' && $_SESSION["ocr_force_processing_" . $ID] !== 1) {
    $ext = $_SESSION['ocr_file_extension_' . $ID];
    $resource_path = $_SESSION['ocr_resource_path_' . $ID];
    $tess_cmd = ($tesseract_fullpath . ' ' . $resource_path . ' ' . escapeshellarg($ocr_temp_dir . '/ocr_output_file_' . $ID) . ' -l ' . $ocr_lang.' -psm ' . $ocr_psm);
    debug("CLI command: $tess_cmd");
    $process = new Process($tess_cmd);
    $process->run();
    debug ("CLI output: " . $process->getOutput());
    debug ("CLI errors: " . trim($process->getErrorOutput()));
//    run_command($tess_cmd);
}

$_SESSION["ocr_stage_" . $ID] = 3;

// Measure execution time for stage 3
$elapsed_3 = round((microtime(true) - $start_3), 3);
$_SESSION["ocr_stage_3_time"] = $elapsed_3;

$debug = ('OCR Stage ' . $_SESSION["ocr_stage_" . $ID] . '/4 completed: ' .$ID. ' lang: ' .$ocr_lang. ' PSM: '.$ocr_psm . ' Time: ' . $elapsed_3);

echo json_encode(array($ID, $debug));