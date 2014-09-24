<?php

include_once "../../../include/db.php";
include_once "../../../include/general.php";
include_once "../../../include/resource_functions.php";

global $imagemagick_path;

$ref = filter_input(INPUT_GET, 'ref');
$lang = filter_input(INPUT_GET, 'lang');
$param_1 = filter_input(INPUT_GET, 'param_1');

// check if file extension is allowed for ocr processing
$resource_data = get_resource_data($ref, true);
$ext = $resource_data["file_extension"];
if (!in_array($ext, $ocr_allowed_extensions))
        {
        echo json_encode('Error: OCR not allowed for this filetype');  
        exit();
        }
        
// check if density (dpi) is high enough for ocr processing 
// 
//$dim = sql_query("select width, height from resource_dimensions where resource='$ref'");
//$image_dimensions = $dim[0];
//$w = $image_dimensions['width'];
//$h = $image_dimensions['height'];
$resource_path = get_resource_path($ref, true, "", false, $ext);
$density = shell_exec($imagemagick_path.'/identify -format "%y" '.''.$resource_path.' 2>&1');
if (intval($density) < $ocr_min_density)
        {
        echo json_encode('Error: Image density (dpi/ppi) too low for OCR processing');  
        exit();
        }        

$test_output = ("Resource ID:".' '.$ref.' '."\nOCR-Language:".' '.$lang.' '."\nParameter:".' '.$param_1.''."\nPath to resource:".' '.$resource_path.''."\nDensity:".' '.$density);
update_field($ref, 72 , $test_output); // write test_output text (string) to database (metadata field 72)
update_xml_metadump($ref);
echo json_encode($test_output);