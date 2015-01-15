<?php
require_once "../plugins/ocrstream/include/ocrstream_functions.php";

function HookOcrstreamEditEditbeforeheader() {
    // Start Session for Single Resource Edit and Upload
    session_start();
    // Clear Session in case ocr processing failed before and old values are present
    session_unset();
}

function HookOcrstreamEditAfterfileoptions() {
    global $ref;
    global $lang;
    global $baseurl;
    global $ocr_global_language;
    global $ocr_allowed_extensions;
    global $ocr_psm_array;
    global $ocr_psm_global;
    global $ocr_cronjob_enabled;
    if (is_tesseract_installed()) {
        // Hide OCR options for filetypes not allowed
        $ext = get_file_extension($ref);
        if (in_array($ext, $ocr_allowed_extensions)) {
            $choices = get_tesseract_languages();
            ?>
            <script src="../plugins/ocrstream/lib/jcrop/js/jquery.Jcrop.min.js"></script>            
            <link rel="stylesheet" href="../plugins/ocrstream/lib/jcrop/css/jquery.Jcrop.css" type="text/css" />
            <script>
                // Initilaize Parameters
                resourceId = <?php echo $ref ?>;
                baseUrl = '<?php echo $baseurl ?>';
            </script>
            <script src="../plugins/ocrstream/lib/ocrstream.single.js"></script>
            <div id="question_ocr" style="font-weight: normal">
                <table>
                    <tr id = "ocr_start" style="height:37px">
                        <td><label for="ocr_single_resource"><?php echo $lang["ocr_single_resource"] ?></label></td>
                        <td><input type="button" name="ocr_start" style="width:90px" value="<?php echo $lang["ocr_start"] ?>"></td>
                        <td id="ocr_status_anim"></td>
                        <td><div id = "ocr_status_text" style="width:400px"></div><span></span></td>
                    </tr>
                    <tr>
                        <td><label for="ocr_language_select"><?php echo $lang["ocr_language_select"] ?></label></td>
                        <td><select name="ocr_lang" id="ocr_lang" style="width:90px" onchange="setLanguage(this.form.ocr_lang.options[this.form.ocr_lang.selectedIndex].value);">
                                <?php
                                $usekeys_lang = false;
                                foreach ($choices as $key => $choice) {
                                    $value = $usekeys_lang ? $key : $choice;
                                    echo '    <option value="' . $value . '"' . (($ocr_global_language == $value) ? ' selected' : '') . ">$choice</option>";
                                }
                                ?>
                            </select></td>
                    </tr>
                    <tr>
                        <td><label for="ocr_psm_select"><?php echo $lang["ocr_psm"] ?></label></td>
                        <td><select name="ocr_psm" id="ocr_psm" style="width:90px" onchange="setPsm(this.form.ocr_psm.options[this.form.ocr_psm.selectedIndex].value);">
                                <?php
                                $usekeys_psm = true;
                                foreach ($ocr_psm_array as $key => $choice) {
                                    $value = $usekeys_psm ? $key : $choice;
                                    echo '    <option value="' . $value . '"' . (($ocr_psm_global == $value) ? ' selected' : '') . ">$choice</option>";
                                }
                                ?>
                            </select></td>
                    </tr>
                    <tr>
                        <td><label for="im_preset_select"><?php echo $lang["im_preset_select"] ?></label></td>
                        <td><select name="im_preset" id="im_preset" style="width:90px" onchange="setParams(this.form.im_preset.options[this.form.im_preset.selectedIndex].value);">
                                <?php
                                // Force PDF documents to be processed (Autoselect Preset 1)
                                if ($ext === 'pdf') {
                                    ?>
                                    <option value="pre_1" selected>Preset 1</option>
                                    <?php
                                } else {
                                    ?>
                                    <option value="none" selected>none</option>
                                    <option value="pre_1">Preset 1</option>
                                    <?php
                                }
                                ?>
                            </select></td>
                    </tr>
                    <?php if ($ocr_cronjob_enabled == true){?>
                    <tr id = "ocr_cron" style="height:37px">
                        <td><label for="ocr_upload_cronjob"><?php echo $lang["ocr_upload_cronjob"] ?></label></td>
                        <td><input type="checkbox" name="ocr_cron_start" id= "ocr_cron_start" onchange="setOCRCron(<?php echo $ref; ?>);"></td>
                    </tr>
                    <?php } ?>
                </table>
            </div>
            <?php
        }
    }
}

function HookOcrstreamEditReplaceuploadoptions() {
    global $lang;
    global $ref;
    global $ocr_global_language;
    global $ocr_psm_array;
    global $ocr_psm_global;
    global $ocr_cronjob_enabled;
    $choices = get_tesseract_languages();
    if (($ref < 0) && (is_tesseract_installed())){
        ?>
        <script src="../plugins/ocrstream/lib/ocrstream.upload.options.js"></script>
        <div><h2 class="CollapsibleSectionHead"><?php echo $lang["ocr-upload-options"] ?></h2>
            <div class="CollapsibleSection" id="OCROptionsSection">
                <div class="Question" id="question_ocr" style="font-weight: normal">
                    <table>
                        <tr id = "ocr_start" style="height:37px">
                            <td><label for="ocr_single_resource"><?php echo $lang["ocr_single_resource"] ?></label></td>
                            <td><input type="checkbox" name="ocr_upload_start" id= "ocr_upload_start" onchange="setOCRStart();"></td>
                        </tr>
                        <tr id = "ocr_language_select">
                            <td><label for="ocr_language_select"><?php echo $lang["ocr_language_select"] ?></label></td>
                            <td><select name="ocr_lang" id="ocr_lang" style="width:90px" onchange="setLanguage_1(this.form.ocr_lang.options[this.form.ocr_lang.selectedIndex].value);">
                                    <?php
                                    $usekeys_lang = false;
                                    foreach ($choices as $key => $choice) {
                                        $value = $usekeys_lang ? $key : $choice;
                                        echo '    <option value="' . $value . '"' . (($ocr_global_language == $value) ? ' selected' : '') . ">$choice</option>";
                                    }
                                    ?>
                                </select></td>
                        </tr>
                        <tr id = "ocr_psm_select">
                            <td><label for="ocr_psm_select"><?php echo $lang["ocr_psm"] ?></label></td>
                            <td><select name="ocr_psm" id="ocr_psm" style="width:414px" onchange="setPsm_1(this.form.ocr_psm.options[this.form.ocr_psm.selectedIndex].value);">
                                    <?php
                                    $usekeys_psm = true;
                                    foreach ($ocr_psm_array as $key => $choice) {
                                        $value = $usekeys_psm ? $key : $choice;
                                        echo '    <option value="' . $value . '"' . (($ocr_psm_global == $value) ? ' selected' : '') . ">$choice</option>";
                                    }
                                    ?>
                                </select></td>
                        </tr>
                        <?php if ($ocr_cronjob_enabled == true){?>
                        <tr id = "ocr_cron" style="height:37px">
                            <td><label for="ocr_upload_cronjob"><?php echo $lang["ocr_upload_cronjob"] ?></label></td>
                            <td><input type="checkbox" name="ocr_cron_start" id= "ocr_cron_start" onchange="setOCRCron();"></td>
                        </tr>
                        <?php } ?>
                    </table>
                </div>
            </div>
        </div>
    <?php

    }
}
function HookOcrstreamEditEditbeforesave() {
    if (isset($_POST['ocr_upload_start'])) {
        $_SESSION['ocr_lang'] = $_POST['ocr_lang'];
        $_SESSION['ocr_start'] = $_POST['ocr_upload_start'];
        $_SESSION['ocr_psm'] = $_POST['ocr_psm'];
    }
    if (isset($_POST['ocr_cron_start'])) {
        $_SESSION['ocr_cron'] = $_POST['ocr_cron_start'];
    }
}
