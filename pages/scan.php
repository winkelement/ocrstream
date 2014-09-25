<?php

include_once "../../../include/db.php";
include_once "../../../include/general.php";
include_once "../../../include/resource_functions.php";
include_once "../include/ocrstream_functions.php";

global $imagemagick_path;

$ref = filter_input(INPUT_GET, 'ref');
$lang = filter_input(INPUT_GET, 'lang');
$param_1 = filter_input(INPUT_GET, 'param_1');

# Check if file extension is allowed for ocr processing
$resource_data = get_resource_data($ref, true);
$ext = $resource_data["file_extension"];
if (!in_array($ext, $ocr_allowed_extensions))
        {
        echo json_encode('Error: OCR not allowed for this filetype');  
        exit();
        }
        
# Check if density (dpi) is in margin for ocr processing 
$resource_path = get_resource_path($ref, true, "", false, $ext);
$density = shell_exec($imagemagick_path.'/identify -format "%y" '.''.$resource_path.' 2>&1');
if (intval($density) < $ocr_min_density)
        {
        echo json_encode('Error: Image density (dpi/ppi) too low for OCR processing');  
        exit();
        }        
if (intval($density) > $ocr_max_density)
        {
        echo json_encode('Density too high for OCR processing. Image needs to be converted (not yet implemented).'); // Placeholder   
        exit();
        }    
        
# Do OCR and read the textfile 
$tesseract_fullpath = get_tesseract_fullpath();
$ocr_temp_dir = get_temp_dir();
$tess_cmd = ($tesseract_fullpath . ' ' . $resource_path . ' ' . $ocr_temp_dir . '\ocr_'.$ref.' -l ' . $lang);
shell_exec($tess_cmd);
$ocr_temp_file = ($ocr_temp_dir . '\ocrtempfile_'.$ref.'.txt');
$tess_content = file_get_contents($ocr_temp_file);
//$test_output = ("Resource ID:".' '.$ref.' '."\nOCR-Language:".' '.$lang.' '."\nParameter:".' '.$param_1.''."\nPath to resource:".' '.$resource_path.''."\nDensity:".' '.$density);
update_field($ref, 72 , $tess_content); // write output text (string) to database (metadata field 72)
update_xml_metadump($ref);
unlink($ocr_temp_file);
echo json_encode($tess_content);

//$dim = sql_query("select width, height from resource_dimensions where resource='$ref'");
//$image_dimensions = $dim[0];
//$w = $image_dimensions['width'];
//$h = $image_dimensions['height'];