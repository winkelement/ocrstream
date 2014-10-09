<?php

// @todo clean up, make separate functions where possible
// @todo implement filter for unwanted content/characters/whitespaces to prevent excessive load on db
// @todo implement tesseract page segmenation mode select

include_once "../../../include/db.php";
include_once "../../../include/general.php";
include_once "../../../include/resource_functions.php";
include_once "../include/ocrstream_functions.php";

global $imagemagick_path;

# Get parameter variables
/* @var $ref ResourceID */
$ref_id = filter_input(INPUT_GET, 'ref', FILTER_VALIDATE_INT);
//$ref_id = getvalescaped("ref_id","",true);
/* @var $ext FileExtension */   
$ext = sql_value("select file_extension value from resource where ref = '$ref_id'", '');
/* @var $ocr_lang TesseractLanguage */
$ocr_lang = filter_input(INPUT_GET, 'ocr_lang');
/* @var $param_1 ImagemagickPreset */
$param_1 = filter_input(INPUT_GET, 'param_1');
// Get cropping values width, height, offset_x, offset_y
$im_preset_1_crop_w = filter_input(INPUT_GET, 'w');
$im_preset_1_crop_h = filter_input(INPUT_GET, 'h');
$im_preset_1_crop_x = filter_input(INPUT_GET, 'x');
$im_preset_1_crop_y = filter_input(INPUT_GET, 'y');

# Initialize Preset 1
$im_preset_1 = array(
    'density'   => ('-density ' . $im_preset_1_density),
    'geometry'  => ('-geometry ' . $im_preset_1_geometry),
    'crop'      => ('-crop ' . $im_preset_1_crop_w . 'x' . $im_preset_1_crop_h . '+' . $im_preset_1_crop_x . '+' . $im_preset_1_crop_y),
    'quality'   => ('-quality ' . $im_preset_1_quality),
    'trim'      => ('-trim'),
    'deskew'    => ('-deskew ' . $im_preset_1_deskew . '%'),
    'normalize' => ('-normalize'),
    'sharpen'   => ('-adaptive-sharpen ' . $im_preset_1_sharpen_r . 'x' . $im_preset_1_sharpen_s),
   );
// For debug return parameters and exit here
//echo json_encode($ref.' '.$ext.' '.$ocr_lang.' '.$param_1. ' '.$w.' '.$h.' '.$x.' '.$y . implode(' ', $im_preset_1)); //debug
//exit();
//
# Checking if Resource ID is valid INTEGER and exists in database
if ($ref_id == NULL || $ref_id < 1 || $ref_id > sql_value("SELECT ref value FROM resource ORDER BY ref DESC LIMIT 1", '')) {
    exit(json_encode('ocr_error_1'));
}

# Check if file extension is allowed for ocr processing
if (!in_array($ext, $ocr_allowed_extensions)) {
    exit(json_encode('ocr_error_2'));
}

# Check if density (dpi) is in margin for ocr processing, skip for pdf 
// @todo check units (inch/centimeter) to prevent false detection
/* @var $resource_path Path */
/* @var $density ImageProperties */
$resource_path = get_resource_path($ref_id, true, "", false, $ext); // get complete path to original file with extension
if ($ext != 'pdf') {
    $density = shell_exec($imagemagick_path . '/identify -format "%y" ' . '' . $resource_path . ' 2>&1');
//    $density = trim($density);
    if (intval($density) < $ocr_min_density) {
        exit(json_encode('ocr_error_3'));
    }
    if (intval($density) > $ocr_max_density) {
        $param_1 = 'pre_1'; // Force image procesing if density too high 
    }
}

# If language parameter is not valid, choose global ocr language setting
$tesseract_languages = get_tesseract_languages();
if (array_search($ocr_lang, $tesseract_languages) == FALSE) {
    $ocr_lang = $ocr_global_language;
}

# Create intermediate image(s) for OCR if needed and run tesseract on it
# 
// @todo optimized multi page support for new (>3.0.3) versions of tesseract
$ocr_temp_dir = get_temp_dir();
$tesseract_fullpath = get_tesseract_fullpath();
// Get number of pages
$resource = get_resource_data($ref_id);
$pg_num = get_page_count($resource, -1);
// Image processing with Preset 1 settings
if ($param_1 === 'pre_1') {
    $convert_fullpath = get_utility_path("im-convert");
    $im_ocr_cmd = $convert_fullpath . " " . implode(' ', $im_preset_1) . ' ' . escapeshellarg($resource_path) . ' ' . escapeshellarg($ocr_temp_dir . '/im_tempfile_' . $ref_id . '.png');
    run_command($im_ocr_cmd);
}
// Make sure to remove old ocr_output_file
array_map('unlink', glob("$ocr_temp_dir/ocr_output_file*.txt"));
// OCR multi pages processed
if ($pg_num > 1){ 
    $i = 0;
    while ($i < $pg_num){
        $ocr_input_file = ($ocr_temp_dir . '/im_tempfile_' . $ref_id . '-' . $i . '.png');
        $tess_cmd = ($tesseract_fullpath . ' ' . $ocr_input_file . ' ' . escapeshellarg($ocr_temp_dir . '/ocrtempfile_' . $ref_id) . ' -l ' . $ocr_lang);
        run_command($tess_cmd);
        file_put_contents($ocr_temp_dir . '/ocr_output_file_' . $ref_id . '.txt', file_get_contents($ocr_temp_dir . '/ocrtempfile_' . $ref_id . '.txt'), FILE_APPEND);
        $i ++;
    }
}
// OCR single page processed
if ($param_1 === 'pre_1' && $pg_num === '1') {
    $ocr_input_file = ($ocr_temp_dir . '/im_tempfile_' . $ref_id . '.png');
    $tess_cmd = ($tesseract_fullpath . ' ' . $ocr_input_file . ' ' . escapeshellarg($ocr_temp_dir . '/ocr_output_file_' . $ref_id) . ' -l ' . $ocr_lang);
    run_command($tess_cmd);    
}
// OCR single page original
if ($param_1 === 'none') {
    $tess_cmd = ($tesseract_fullpath . ' ' . $resource_path . ' ' . escapeshellarg($ocr_temp_dir . '/ocr_output_file_' . $ref_id) . ' -l ' . $ocr_lang);
    run_command($tess_cmd);     
}
$ocr_output_file = $ocr_temp_dir . '/ocr_output_file_' . $ref_id . '.txt';
$tess_content = trim(file_get_contents($ocr_output_file));

# Write output text (string) to database (metadata field 72) and metadata.xml
update_field($ref_id, 72, $tess_content);
update_xml_metadump($ref_id);

# Delete temp files
//array_map('unlink', glob("$ocr_temp_dir/ocr*.txt")); //debug, uncomment for productive system
//array_map('unlink', glob("$ocr_temp_dir/im*.png")); //debug, uncomment for productive system

# Return extracted text as JSON
echo json_encode($tess_content);
exit();

