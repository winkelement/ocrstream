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
    global $im_preset_1_geometry;
    global $ocr_psm_array;
    global $ocr_psm_global;
    global $ocr_cronjob_enabled;
    if (is_tesseract_installed()) {
        // Hide OCR options for filetypes not allowed
        $ext = get_file_extension($ref);
        if (in_array($ext, $ocr_allowed_extensions)) {
            // Get aspect ratio of image for calculating crop size
            $w_thumb = sql_value("select thumb_width value from resource where ref = '$ref'", '');
            $h_thumb = sql_value("select thumb_height value from resource where ref = '$ref'", '');
            $ar = ($w_thumb / $h_thumb);
            $w = $im_preset_1_geometry;
            $h = ($w / $ar);
            $choices = get_tesseract_languages();
            ?>
            <script src="../plugins/ocrstream/lib/jcrop/js/jquery.Jcrop.min.js"></script>
            <script src="../plugins/ocrstream/lib/utilities.js"></script>
            <link rel="stylesheet" href="../plugins/ocrstream/lib/jcrop/css/jquery.Jcrop.css" type="text/css" />
            <script>
                function ocr_crop() {
                    jQuery(function ($) {
                        var jcrop_api;
                        $('.ImageBorder').Jcrop({
                            trueSize: ['<?php echo $w ?>', '<?php echo $h ?>'],
                            onChange: setCoords,
                            onSelect: setCoords,
                            onRelease: clearCoords
                        }, function () {
                            jcrop_api = this;
                        });
                        $('#coords').on('change', 'input', function (e) {
                            var x1 = $('#x1').val(),
                                    y1 = $('#y1').val();
                            jcrop_api.setSelect([x1, y1]);
                        });
                    });
                }
                function setCoords(c)
                {
                    jQuery('#x1').val(Math.round(c.x));
                    jQuery('#y1').val(Math.round(c.y));
                    jQuery('#w').val(Math.round(c.w));
                    jQuery('#h').val(Math.round(c.h));
                    x = (Math.round(c.x));
                    y = (Math.round(c.y));
                    w = (Math.round(c.w));
                    h = (Math.round(c.h));
                }
                ;
                function clearCoords()
                {
                    jQuery('#coords input').val('');
                }
                ;
                function setLanguage(selectedLanguage) {
                    ocr_lang = selectedLanguage;
                    return ocr_lang;
                }
                ;
                function setPsm(selectedPSM) {
                    ocr_psm = selectedPSM;
                    return ocr_psm;
                }
                ;
                function setParams(selectedParam) {
                    param_1 = selectedParam;
                    if (param_1 === 'pre_1') {
                        ocr_crop();
                    }
                    return param_1;
                };
                function setOCRCron(resourceId)
                {
                    ocr_cron = jQuery('#ocr_cron_start').attr('checked');
                    if (ocr_cron === 'checked') {
                        ocr_state = '1';
                        jQuery.get('<?php echo $baseurl ?>/plugins/ocrstream/pages/rest.php', {ref: resourceId, ocr_state: ocr_state}, function (data)
                        {
                            var1 = data;
                            console.log(var1); // debug
                        });
                    } else {
                        ocr_state = '0';
                        jQuery.get('<?php echo $baseurl ?>/plugins/ocrstream/pages/rest.php', {ref: resourceId, ocr_state: ocr_state}, function (data)
                        {
                            var2 = data;
                            console.log(var2); // debug
                        });
                    }
                    console.log(ocr_cron);// debug
                    return ocr_cron;
                }
                ;
                function showLoadingImage() {
                    jQuery('#ocr_status_anim').append('<div id="loading-image"><img src="../plugins/ocrstream/gfx/loader_2.gif" alt="Loading..."  style="margin-left:17px" /></div>');
                }
                ;
                function hideLoadingImage() {
                    jQuery('#loading-image').fadeOut(800);
                }
                ;

                // Send parameters to stage 1 - 4 for OCR processing
                jQuery( document ).ready(function() {

                    // Initilaize Parameters
                    resourceId = <?php echo $ref ?>;
                    baseUrl = '<?php echo $baseurl ?>';
                    jQuery.get(baseUrl + '/plugins/ocrstream/pages/rest.php', {ref: resourceId, ocr_state_query: 1}, function(data) {
                        ocr_state = data;
                        if (ocr_state == 1) {
                        jQuery('#ocr_cron_start').prop('checked', true);
                        }
                        console.log(ocr_state); // debug
                        return ocr_state;
                    });                    
                    ocr_lang = jQuery('#ocr_lang :selected').text();
                    ocr_psm = jQuery('#ocr_psm :selected').val();
                    param_1 = jQuery('#im_preset :selected').val();
                    x = '0';
                    y = '0';
                    w = '0';
                    h = '0';
                    status = 'OCR Stage ';
                    // Only show jCrop when image is going to be processed
                    if (param_1 === 'pre_1') {
                        ocr_crop();
                    }                  

                    jQuery('[name="ocr_start"]').click(function ()
                    {
                        console.log(status + '1/4 .'); // debug
                        jQuery('#ocr_status_text').html(status + '1/4 .');
                        jQuery.get(baseUrl + '/plugins/ocrstream/include/stage_1.php', {ref: resourceId, ocr_lang: ocr_lang}, function (data)
                        {
                            stageOneOutput = JSON.parse(data);
                            console.log((stageOneOutput)); // debug
                            if (stageOneOutput.hasOwnProperty("error")) {
                                hideLoadingImage();
                                jQuery('#ocr_status_text').fadeOut(800);
                                alert(stageOneOutput["error"]);
                                return;
                            }

                            console.log(status + '2/4 ..'); // debug
                            jQuery('#ocr_status_text').html(status + '2/4 ..');
                            jQuery.get(baseUrl + '/plugins/ocrstream/include/stage_2.php', {ref: resourceId, ocr_lang: ocr_lang, ocr_psm: ocr_psm, param_1: param_1, w: w, h: h, x: x, y: y}, function (data)
                            {
                                var2 = data;
                                console.log(JSON.parse(var2)); // debug
                                console.log(status + '3/4 ...'); // debug
                                jQuery('#ocr_status_text').html(status + '3/4 ...');
                                jQuery.get(baseUrl + '/plugins/ocrstream/include/stage_3.php', {ref: resourceId, ocr_lang: ocr_lang, ocr_psm: ocr_psm, param_1: param_1}, function (data)
                                {
                                    var3 = data;
                                    console.log(JSON.parse(var3)); // debug
                                    console.log(status + '4/4 ....'); // debug
                                    jQuery('#ocr_status_text').html(status + '4/4 ....');
                                    jQuery.get(baseUrl + '/plugins/ocrstream/include/stage_4.php', {ref: resourceId}, function (data)
                                    {
                                        var4 = data;
                                        console.log(JSON.parse(var4)); // debug
                                        jQuery('#ocr_status_text').html(JSON.parse(var4));
                                        hideLoadingImage();
                                        jQuery('#ocr_status_text').fadeOut(800);
                                    });
                                });

                            });
                            //{
                            //@todo find a way to update 'Extracted text' field wihout reloading whole page
                            //window.location.reload(true);
                            //}
                        });
                        showLoadingImage();

                    });
                    });
            </script>
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
    global $ocr_allowed_extensions;
    global $ocr_psm_array;
    global $ocr_psm_global;
    global $ocr_cronjob_enabled;
    global $baseurl;
    $choices = get_tesseract_languages();
    if (($ref < 0) && (is_tesseract_installed())){
        ?>
        <script>
            function setLanguage_1(selectedLanguage)
                {
                    ocr_lang = selectedLanguage;
                    return ocr_lang;
                }
                ;
            function setPsm_1(selectedPSM)
                {
                    ocr_psm = selectedPSM;
                    return ocr_psm;
                }
                ;
            function setOCRStart()
                {
                    ocr_start = jQuery('#ocr_upload_start').attr('checked');
                    if (ocr_start === 'checked') {
                        jQuery('#ocr_cron').fadeOut(200);
                    }
                    else {
                        jQuery('#ocr_cron').fadeIn(200);
                    }
                    return ocr_start;
                }
                ;
                function setOCRCron()
                {
                    ocr_cron = jQuery('#ocr_cron_start').attr('checked');
                    if (ocr_cron === 'checked') {
                        jQuery('#ocr_start').fadeOut(200);
                        jQuery('#ocr_language_select').fadeOut(200);
                        jQuery('#ocr_psm_select').fadeOut(200);
                    }
                    else {
                        jQuery('#ocr_start').fadeIn(200);
                        jQuery('#ocr_language_select').fadeIn(200);
                        jQuery('#ocr_psm_select').fadeIn(200);
                    }
                    return ocr_cron;
                }
                ;
            ocr_lang = jQuery('#ocr_lang :selected').text();
            ocr_psm = jQuery('#ocr_psm :selected').val();
            ocr_start = jQuery('#ocr_upload_start').attr('checked');
        </script>
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
