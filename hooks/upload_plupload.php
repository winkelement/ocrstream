<?php
require_once "../plugins/ocrstream/include/ocrstream_functions.php";

function HookOcrstreamUpload_pluploadUpload_page_top() {
    global $lang;
    ?>
    <link rel="stylesheet" href="../plugins/ocrstream/lib/ocrstream.css" type="text/css" />
    <div id="ocr_status_anim"><div><img src="../plugins/ocrstream/gfx/ocrstream_loader.gif" alt="Loading..." /><p><?php echo $lang['ocr_in_progress']?></p></div></div>
    <?php
}

function HookOcrstreamUpload_pluploadAfterpluploadfile() {
    global $ref;
    global $baseurl;
    if (is_session_started() === FALSE) {
        session_start();
    }
    if (isset($_SESSION["ocr_start"])) {
        ?>
        <script>
            resourceId = <?php echo $ref ?>;
            baseUrl = '<?php echo $baseurl ?>';
            ocr_lang = '<?php echo $_SESSION['ocr_lang'] ?>';
            ocr_psm = '<?php echo $_SESSION['ocr_psm'] ?>';
        </script>
        <script src="../plugins/ocrstream/lib/ocrstream.upload.js"></script> <?php
    }
    // @todo ######## Remove ############
    if (isset($_SESSION["ocr_cron"])) {
        $ocr_state = 1;
        set_ocr_state($ref, $ocr_state);
        echo "OCR state set to 1 for ID: $ref";
    }
}

