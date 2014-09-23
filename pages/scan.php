<?php

$ref_id = filter_input(INPUT_GET, 'ref');
$param1 = filter_input(INPUT_GET, 'param1');
echo json_encode("Resource ID:" . ' ' . $ref_id . ' ' . "Parameter:" . ' ' . $param1);

