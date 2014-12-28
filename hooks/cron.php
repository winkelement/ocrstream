<?php

function HookOcrstreamCronAddplugincronjob() {
    global $ocr_cronjob_enabled;
    if ($ocr_cronjob_enabled == true) {
        echo 'OCR cronjob test' . PHP_EOL;
        $ocr_flagged = sql_array("SELECT ref value from resource WHERE ocr_state = '1'", '');
        $n = count($ocr_flagged);
        foreach($ocr_flagged as $val) {
            echo "<html><br> Resource ID: $val</html>";
            }
        echo "<html><br>===============================</html>";
        echo "<html><br>Resources flagged for OCR processing: $n</html>";
    }
    
}
