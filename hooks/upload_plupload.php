<?php

function HookOcrstreamUpload_pluploadAfterpluploadfile() {
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
    session_start();
    if (isset($_SESSION["ocr_start"])) {
        require "../plugins/ocrstream/include/stage_1.php";
        require "../plugins/ocrstream/include/stage_2.php";
        require "../plugins/ocrstream/include/stage_3.php";
        require "../plugins/ocrstream/include/stage_4.php";
    }
    if (isset($_SESSION["ocr_cron"])) {
        $ocr_state = 1;
        sql_query("UPDATE resource SET ocr_state =  '$ocr_state' WHERE ref = '$ref'");
        echo "OCR state 1: $ref";
    }
}

