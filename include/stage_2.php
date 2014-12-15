<?php
SESSION_START();

//  Stage 2 - The Converter
//
//
require_once "../../../include/db.php";
require_once "../../../include/general.php";
require_once "../../../include/resource_functions.php";
require_once "../include/ocrstream_functions.php";

global $imagemagick_path;

// Get ID
$ref_id = filter_input(INPUT_GET, 'ref', FILTER_VALIDATE_INT);

if ($_SESSION["ocr_stage_" . $ref_id] != 1) {
    exit(json_encode('ocr_error_stage_1'));
}
/* @var $param_1 ImagemagickPreset */
$param_1 = filter_input(INPUT_GET, 'param_1');
$im_preset_1_crop_w = filter_input(INPUT_GET, 'w');
$im_preset_1_crop_h = filter_input(INPUT_GET, 'h');
$im_preset_1_crop_x = filter_input(INPUT_GET, 'x');
$im_preset_1_crop_y = filter_input(INPUT_GET, 'y');

// Get original file extension
$ext = $_SESSION['ocr_file_extension_' . $ref_id];

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
// 
$ocr_temp_dir = get_temp_dir();
$_SESSION['ocr_temp_dir'] = $ocr_temp_dir;

// Image processing with Preset 1 settings
if ($param_1 === 'pre_1' || $_SESSION["ocr_force_processing_" . $ref_id] === 1) {
    $convert_fullpath = get_utility_path("im-convert");
    $resource_path = $_SESSION['ocr_resource_path_' . $ref_id];
    $im_ocr_cmd = $convert_fullpath . " " . implode(' ', $im_preset_1) . ' ' . escapeshellarg($resource_path) . ' ' . escapeshellarg($ocr_temp_dir . '/im_tempfile_' . $ref_id . '.jpg');
//    $process_im = new Process($im_ocr_cmd);
//    $process_im->run();
    run_command($im_ocr_cmd);
    $_SESSION["ocr_stage_" . $ref_id] = 2;
    $debug = json_encode('OCR Stage ' . $_SESSION["ocr_stage_" . $ref_id] . ' completed ' . $ref_id . ' ' . $ext . ' ' . $param_1 . ' ' . $im_preset_1_crop_w . ' ' . $im_preset_1_crop_h . ' ' . $im_preset_1_crop_x . ' ' . $im_preset_1_crop_y);
} else {
    $_SESSION["ocr_stage_" . $ref_id] = 2;
    $debug = json_encode('OCR Stage ' . $_SESSION["ocr_stage_" . $ref_id] . ' skipped ' . $ref_id . ' ' . $ext . ' ' . $param_1 . ' ' . $im_preset_1_crop_w . ' ' . $im_preset_1_crop_h . ' ' . $im_preset_1_crop_x . ' ' . $im_preset_1_crop_y);
}

echo $debug; //debug
exit();