<?php

include_once "../../../include/db.php";
include_once "../../../include/general.php";
include_once "../../../include/resource_functions.php";

$ref = filter_input(INPUT_GET, 'ref');
$lang = filter_input(INPUT_GET, 'lang');
$param_1 = filter_input(INPUT_GET, 'param_1');

$resource_data = get_resource_data($ref, true);
$ext = $resource_data["file_extension"];
if (!in_array($ext, $ocr_allowed_extensions))
        {
        echo json_encode('Error: OCR not allowed for this filetype');  
        exit();
        }
$resource_path = get_resource_path($ref, true, "", false, $ext);
$test_output = ("Resource ID:".' '.$ref.' '."\nOCR-Language:".' '.$lang.' '."\nParameter:".' '.$param_1.''."\nPath to resource:".' '.$resource_path);
update_field($ref, 72 , $test_output); // write test_output text (string) to database (metadata field 72)
update_xml_metadump($ref);
echo json_encode($test_output);