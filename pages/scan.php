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
$ref = filter_input(INPUT_GET, 'ref', FILTER_VALIDATE_INT);
/* @var $ext FileExtension */
$ext = sql_value("select file_extension value from resource where ref = '$ref'", '');
/* @var $ocr_lang TesseractLanguage */
$ocr_lang = filter_input(INPUT_GET, 'ocr_lang');
/* @var $param_1 ImagemagickPreset */
$param_1 = filter_input(INPUT_GET, 'param_1');
// Get cropping values width, height, offset_x, offset_y
$im_preset_1_crop_w = filter_input(INPUT_GET, 'w');
$im_preset_1_crop_h = filter_input(INPUT_GET, 'h');
$im_preset_1_crop_x = filter_input(INPUT_GET, 'x');
$im_preset_1_crop_y = filter_input(INPUT_GET, 'y');

# Iniatialize Preset 1
$im_preset_1 = [
    'density'   => ('-density ' . $im_preset_1_density),
    'geometry'  => ('-geometry ' . $im_preset_1_geometry),
    'crop'      => ('-crop ' . $im_preset_1_crop_w . 'x' . $im_preset_1_crop_h . '+' . $im_preset_1_crop_x . '+' . $im_preset_1_crop_y),
    'quality'   => ('-quality ' . $im_preset_1_quality),
    'trim'      => ('-trim'),
    'deskew'    => ('-deskew ' . $im_preset_1_deskew . '%'),
    'normalize' => ('-normalize'),
    'sharpen'   => ('-adaptive-sharpen ' . $im_preset_1_sharpen_r . 'x' . $im_preset_1_sharpen_s),
   ];
// For debug return parameters and exit here
//echo json_encode($ref.' '.$ext.' '.$ocr_lang.' '.$param_1. ' '.$w.' '.$h.' '.$x.' '.$y . implode(' ', $im_preset_1)); //debug
//exit();
//
# Checking if Resource ID is valid INTEGER and exists in database
if ($ref == NULL || $ref < 1 || $ref > sql_value("SELECT ref value FROM resource ORDER BY ref DESC LIMIT 1", '')) {
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
$resource_path = get_resource_path($ref, true, "", false, $ext); // get complete path to original file with extension
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
$pg_num = sql_value("select page_count value from resource_dimensions where resource = '$ref'", '');
// Image processing with Preset 1 settings
if ($param_1 === 'pre_1') {
    $convert_fullpath = get_utility_path("im-convert");
    $im_ocr_cmd = $convert_fullpath . " " . implode(' ', $im_preset_1) . ' ' . escapeshellarg($resource_path) . ' ' . escapeshellarg($ocr_temp_dir . '/im_tempfile_' . $ref . '.png');
    run_command($im_ocr_cmd);
} 
// OCR multi pages processed
if ($pg_num > 1){ 
    $i = 0;
    while ($i < $pg_num){
        $ocr_input_file = ($ocr_temp_dir . '/im_tempfile_' . $ref . '-' . $i . '.png');
        $tess_cmd = ($tesseract_fullpath . ' ' . $ocr_input_file . ' ' . escapeshellarg($ocr_temp_dir . '/ocrtempfile_' . $ref) . ' -l ' . $ocr_lang);
        shell_exec($tess_cmd);
        file_put_contents($ocr_temp_dir . '/ocr_output_file_' . $ref . '.txt', file_get_contents($ocr_temp_dir . '/ocrtempfile_' . $ref . '.txt'), FILE_APPEND);
        $i ++;
    }
}
// OCR single page processed
if ($param_1 === 'pre_1') {
    $ocr_input_file = ($ocr_temp_dir . '/im_tempfile_' . $ref . '.png');
     $tess_cmd = ($tesseract_fullpath . ' ' . $ocr_input_file . ' ' . escapeshellarg($ocr_temp_dir . '/ocr_output_file_' . $ref) . ' -l ' . $ocr_lang);
     shell_exec($tess_cmd);    
}
// OCR single page original
 else {
     $tess_cmd = ($tesseract_fullpath . ' ' . $resource_path . ' ' . escapeshellarg($ocr_temp_dir . '/ocr_output_file_' . $ref) . ' -l ' . $ocr_lang);
     shell_exec($tess_cmd);     
}
$ocr_output_file = $ocr_temp_dir . '/ocr_output_file_' . $ref . '.txt';
$tess_content = trim(file_get_contents($ocr_output_file));

# Write output text (string) to database (metadata field 72) and metadata.xml
update_field($ref, 72, $tess_content);
update_xml_metadump($ref);

# Delete temp files
// @todo delete all im_tempfiles for multipages
//unlink($ocr_temp_file);
//unlink($im_tempfile); // debug: don't delete files to evaluate image processing results

# Return extracted text as JSON
echo json_encode($tess_content);
exit();

