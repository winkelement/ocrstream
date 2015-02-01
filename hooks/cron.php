<?php

require_once (dirname(__FILE__) . "../../include/ocrstream_functions.php");

function HookOcrstreamCronAddplugincronjob() {
    global $ocr_cronjob_enabled;
    if ($ocr_cronjob_enabled == true) {        
        echo PHP_EOL . "OCR Cronjob dummy" . PHP_EOL;
        $ocr_flagged = sql_array("SELECT ref value from resource WHERE ocr_state = '1'", '');
        $n = count($ocr_flagged);
        if ($n === 0) {
            exit ('No resources in queue');
        }
        foreach($ocr_flagged as $ref) {
            echo "Resource ID: $ref" . PHP_EOL;  
        }
        echo "Resources flagged for OCR processing: $n" . PHP_EOL;
    }        
}