<?php

include_once "../../../include/db.php";
include_once "../../../include/general.php";
include_once "../../../include/resource_functions.php";

$ref = filter_input(INPUT_GET, 'ref');
$lang = filter_input(INPUT_GET, 'lang');
$param_1 = filter_input(INPUT_GET, 'param_1');

$test_output = ("Resource ID:".' '.$ref.' '."\nOCR-Language:".' '.$lang.' '."\nParameter:".' '.$param_1);
update_field($ref, 72 , $test_output); // write test_output text (string) to database (metadata field 72)
echo json_encode($test_output);
