<?php

include_once "../../../include/db.php";
include_once "../../../include/general.php";
include_once "../../../include/resource_functions.php";
include_once "../include/ocrstream_functions.php";

global $imagemagick_path;

# Checking if Resource ID is valid INTEGER and exists in database
$ref = filter_input(INPUT_GET, 'ref', FILTER_VALIDATE_INT);
if ($ref == NULL || $ref < 1 || $ref > sql_value("SELECT ref value FROM resource ORDER BY ref DESC LIMIT 1",''))
        {
        echo json_encode('ocr_error_1');  
        exit();
        }

# Check if file extension is allowed for ocr processing
$ext = sql_value("select file_extension value from resource where ref = '$ref'",'');
if (!in_array($ext, $ocr_allowed_extensions))
        {
        echo json_encode('ocr_error_2');  
        exit();
        }        
        
# Check if density (dpi) is in margin for ocr processing 
$resource_path = get_resource_path($ref, true, "", false, $ext);
$density = shell_exec($imagemagick_path.'/identify -format "%y" '.''.$resource_path.' 2>&1');
$density = trim($density);
if (intval($density) < $ocr_min_density)
        {
        echo json_encode('ocr_error_3');  
        exit();
        }        
if (intval($density) > $ocr_max_density)
        {
        echo json_encode('ocr_error_4'); // Placeholder   
        exit();
        }    

# If language parameter is not valid, choose global ocr language setting
$ocr_lang = filter_input(INPUT_GET, 'ocr_lang');
$tesseract_languages = get_tesseract_languages();
if (array_search($ocr_lang, $tesseract_languages) == FALSE)
        {
        $ocr_lang = $ocr_global_language;  
        }

$param_1 = filter_input(INPUT_GET, 'param_1');

# Do OCR and read the textfile 
$tesseract_fullpath = get_tesseract_fullpath();
$ocr_temp_dir = get_temp_dir();
$tess_cmd = ($tesseract_fullpath . ' ' . $resource_path . ' ' . escapeshellarg($ocr_temp_dir . '/ocrtempfile_'.$ref).' -l ' . $ocr_lang);
shell_exec($tess_cmd);
$ocr_temp_file = ($ocr_temp_dir . '/ocrtempfile_'.$ref.'.txt');
$tess_content = trim(file_get_contents($ocr_temp_file));
//$test_output = ("Resource ID:".' '.$ref.' '."\nOCR-Language:".' '.$ocr_lang.' '."\nParameter:".' '.$param_1.''."\nPath to resource:".' '.$resource_path.''."\nDensity:".' '.$density);
update_field($ref, 72 , $tess_content); // write output text (string) to database (metadata field 72)
update_xml_metadump($ref);
unlink($ocr_temp_file);
echo json_encode($tess_content);
exit();

//$dim = sql_query("select width, height from resource_dimensions where resource='$ref'");
//$image_dimensions = $dim[0];
//$w = $image_dimensions['width'];
//$h = $image_dimensions['height'];
