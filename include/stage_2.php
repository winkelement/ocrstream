<?php
//  Stage 2 - The Converter
//
//
$start_2 = microtime(true);

if (!isset($_SESSION["ocr_start"])) {
    require_once "../../../include/db.php";
    require_once "../../../include/general.php";
    require_once "../../../include/authenticate.php";
    require_once "../../../include/resource_functions.php";
    require_once "../include/ocrstream_functions.php";
    spl_autoload_register(function ($class) {
    include '../lib/Process/' . $class . '.php';
    });
    $param_1 = filter_input(INPUT_GET, 'param_1');
    $im_preset_1_crop_w = filter_input(INPUT_GET, 'w');
    $im_preset_1_crop_h = filter_input(INPUT_GET, 'h');
    $im_preset_1_crop_x = filter_input(INPUT_GET, 'x');
    $im_preset_1_crop_y = filter_input(INPUT_GET, 'y');
    $ref = filter_input(INPUT_GET, 'ref', FILTER_VALIDATE_INT);
}
else {
    require_once "../include/db.php";
    require_once "../include/general.php";
//    require_once "../include/authenticate.php";
    require_once "../include/resource_functions.php";
    require_once "../plugins/ocrstream/include/ocrstream_functions.php";
    spl_autoload_register(function ($class) {
    include '../plugins/ocrstream/lib/Process/' . $class . '.php';
    });
    $param_1 = 'none';
    $im_preset_1_crop_w = 0;
    $im_preset_1_crop_h = 0;
    $im_preset_1_crop_x = 0;
    $im_preset_1_crop_y = 0;
}

if (is_session_started() === FALSE) {
    session_start();
}

global $imagemagick_path;

if ($_SESSION["ocr_stage_" . $ref] !== 1) {
    exit(json_encode('Error: stage 1 not completed.'));
}

// Get original file extension from stage 1
$ext = $_SESSION['ocr_file_extension_' . $ref];

// Build IM-Preset Array
$im_preset_1 = array(
    'colorspace' => ('-colorspace gray'),
    'type' => ('-type grayscale'),
    'density' => ('-density ' . $im_preset_1_density),
    'geometry' => ('-geometry ' . $im_preset_1_geometry),
    'crop' => ('-crop ' . $im_preset_1_crop_w . 'x' . $im_preset_1_crop_h . '+' . $im_preset_1_crop_x . '+' . $im_preset_1_crop_y),
    'quality' => ('-quality ' . $im_preset_1_quality),
    'trim' => ('-trim'),
    'deskew' => ('-deskew ' . $im_preset_1_deskew . '%'),
    'normalize' => ('-normalize'),
    'sharpen' => ('-sharpen ' . $im_preset_1_sharpen_r . 'x' . $im_preset_1_sharpen_s),
//    'depth'     => ('-depth 8'),
);

// Create intermediate image(s) for OCR
$ocr_temp_dir = get_temp_dir();
$_SESSION['ocr_temp_dir'] = $ocr_temp_dir;

// Image processing with Preset 1 settings
if ($param_1 === 'pre_1' || $_SESSION["ocr_force_processing_" . $ref] === 1) {
    set_time_limit(1800);
    $convert_fullpath = get_utility_path("im-convert");
    $resource_path = $_SESSION['ocr_resource_path_' . $ref];
    $im_ocr_cmd = $convert_fullpath . " " . implode(' ', $im_preset_1) . ' ' . escapeshellarg($resource_path) . ' ' . escapeshellarg($ocr_temp_dir . '/im_tempfile_' . $ref . '.jpg');
    debug("CLI command: $im_ocr_cmd");
    $process = new Process($im_ocr_cmd);
    $process->setTimeout(3600);
    $process->run();
    debug ("CLI output: " . $process->getOutput());
    debug ("CLI errors: " . trim($process->getErrorOutput()));
    if (!$process->isSuccessful()) {
        throw new \RuntimeException($process->getErrorOutput());
    }
    // run_command($im_ocr_cmd);
    // Checking if temp image(s) were created
    if (!file_exists($ocr_temp_dir . '/im_tempfile_' . $ref . '.jpg') && !file_exists($ocr_temp_dir . '/im_tempfile_' . $ref . '-0.jpg')) {
        exit(json_encode('ocr image processing error (stage 2)'));
    }
    $_SESSION["ocr_stage_" . $ref] = 2;
    // Measure execution time for stage 2
    $elapsed_2 = round((microtime(true) - $start_2), 3);
    $_SESSION["ocr_stage_2_time"] = $elapsed_2;
    $debug = json_encode('OCR Stage ' . $_SESSION["ocr_stage_" . $ref] . '/4 completed: ' . $ref . ' ext: ' . $ext . ' im_preset: ' . $param_1 . ' Time: ' . $elapsed_2);
} else {
    $_SESSION["ocr_stage_" . $ref] = 2;
    // Measure execution time for stage 2
    $elapsed_2 = round((microtime(true) - $start_2), 3);
    $_SESSION["ocr_stage_2_time"] = $elapsed_2;
    $debug = json_encode('OCR Stage ' . $_SESSION["ocr_stage_" . $ref] . '/4 skipped: ' . $ref . ' im_preset: ' . $param_1 . ' Time: ' . $elapsed_2);
}

echo $debug; //debug
//return($debug);