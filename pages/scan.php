<?php

include_once "../../../include/db.php";
include_once "../../../include/general.php";
include_once "../../../include/resource_functions.php";

$ref = filter_input(INPUT_GET, 'ref');
$param1 = filter_input(INPUT_GET, 'param1');

$test_output = ("Resource ID:" . ' ' . $ref . ' ' . "Parameter:" . ' ' . $param1);
update_field($ref, 72 , $test_output);
echo json_encode($test_output);
