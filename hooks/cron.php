<?php

function HookOcrstreamCronAddplugincronjob() {
    global $ocr_cronjob_enabled;
    if ($ocr_cronjob_enabled == true) {
        session_start();
        session_unset();
        $_SESSION['ocr_start'] = 'on';
        global $ref;  
        global $lang;
        global $baseurl;
        global $ocr_allowed_extensions;
        global $ocr_global_language;
        global $im_preset_1_density;
        global $im_preset_1_geometry;
        global $im_preset_1_quality;
        global $im_preset_1_deskew;
        global $im_preset_1_sharpen_r;
        global $im_preset_1_sharpen_s;
        global $use_ocr_db_filter;
        global $ocr_db_filter_1;
        global $ocr_db_filter_2;
        global $ocr_ftype_1;
        global $ocr_psm_global;
        echo 'OCR Cronjob' . PHP_EOL;
        echo "<html><br>___________________</html>";
        $ocr_flagged = sql_array("SELECT ref value from resource WHERE ocr_state = '1'", '');
        $n = count($ocr_flagged);
        if ($n === 0) {
            exit ('<html><br>No resources in queue</html>');
        }
        $total_time = 0.0;
        foreach($ocr_flagged as $ref) {
            echo "<html><br> Resource ID: $ref</html>";  
            require "../plugins/ocrstream/include/stage_1.php";
            require "../plugins/ocrstream/include/stage_2.php";
            require "../plugins/ocrstream/include/stage_3.php";
            require "../plugins/ocrstream/include/stage_4.php";
            $time = $_SESSION["ocr_total_time"];
            $total_time = $total_time + $time;
        }
        echo "<html><br>=======================</html>";
        echo "<html><br>Resources processed: $n</html>";
        echo "<html><br>Total processing time: $total_time</html>";
    }        
}