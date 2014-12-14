<?php
require_once "../plugins/ocrstream/include/ocrstream_functions.php";

/**
 * Hook for OCR on single resource (resource edit)
 * 
 * @global string $ref
 * @global string $lang
 * @global type $baseurl
 * @global string $ocr_global_language
 * @global array $ocr_allowed_extensions
 * @global type $im_preset_1_geometry
 */
function HookOcrstreamEditAfterfileoptions() {
    global $ref;
    global $lang;
    global $baseurl;
    global $ocr_global_language;
    global $ocr_allowed_extensions;
    global $im_preset_1_geometry;
    global $ocr_psm_array;
    global $ocr_psm_global;
    if (is_tesseract_installed()) {
        // Hide OCR options for filetypes not allowed
        $ext = sql_value("select file_extension value from resource where ref = '$ref'", '');
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
                }
                ;
                function showLoadingImage() {
                    jQuery('#ocr_start').append('<td id="loading-image"><img src="../gfx/interface/loading.gif" alt="Loading..."  style="margin-left:17px" /></td>');
                }
                ;
                function hideLoadingImage() {
                    jQuery('#loading-image').remove();
                }
                ;
                // Initilaize Parameters
                ocr_lang = jQuery('#ocr_lang :selected').text();
                ocr_psm = jQuery('#ocr_psm :selected').val();
                param_1 = jQuery('#im_preset :selected').val();
                x = '0';
                y = '0';
                w = '0';
                h = '0';
                // Only show jCrop when image is going to be processed
                if (param_1 === 'pre_1') {
                    ocr_crop();
                }
                // Send parameters to scan.php and get result
                jQuery('[name="ocr_start"]').click(function ()
                {
                    console.log('stage 1 started'); 
                    jQuery.get('<?php echo $baseurl ?>/plugins/ocrstream/include/stage_1.php', {ref: '<?php echo $ref ?>', ocr_lang: (ocr_lang)}, function (data)
                    {
                        var1 = data;
                        console.log(JSON.parse(var1)); // debug
                        if (JSON.parse(var1) === 'ocr_error_4') {
                            hideLoadingImage();
                            alert('<?php echo $lang["ocr_error_4"] ?>');
                            return;
                        }
                        console.log('stage 2 started');
                        jQuery.get('<?php echo $baseurl ?>/plugins/ocrstream/include/stage_2.php', {ref: '<?php echo $ref ?>', ocr_lang: (ocr_lang), ocr_psm: (ocr_psm), param_1: (param_1), w: (w), h: (h), x: (x), y: (y)}, function (data)
                        {
                            var2 = data;
                            console.log(JSON.parse(var2)); // debug
                            console.log('stage 3 started');
                            jQuery.get('<?php echo $baseurl ?>/plugins/ocrstream/include/stage_3.php', {ref: '<?php echo $ref ?>', ocr_lang: (ocr_lang), ocr_psm: (ocr_psm), param_1: (param_1), w: (w), h: (h), x: (x), y: (y)}, function (data)
                            {
                                var3 = data;
                                console.log(JSON.parse(var3)); // debug
                                console.log('stage 4 started');
                                jQuery.get('<?php echo $baseurl ?>/plugins/ocrstream/include/stage_4.php', {ref: '<?php echo $ref ?>'}, function (data)
                                {
                                    var4 = data;
                                    console.log(JSON.parse(var4)); // debug
                                    hideLoadingImage();
                                });
                                hideLoadingImage();
                            });
                 
                        });

//                        {
//                            //@todo find a way to update 'Extracted text' field wihout reloading whole page
////                            window.location.reload(true);
//                        }
                    });
                    showLoadingImage();
                    
                });
            </script>
            <div class="Question" id="question_ocr" style="font-weight: normal">
                <table>
                    <tr id = "ocr_start" style="height:37px">
                        <td><label for="ocr_single_resource"><?php echo $lang["ocr_single_resource"] ?></label></td>
                        <td><input type="button" name="ocr_start" style="width:90px" value="<?php echo $lang["ocr_start"] ?>"></td>
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
                        <td><select name="ocr_psm" id="ocr_psm" style="width:414px" onchange="setPsm(this.form.ocr_psm.options[this.form.ocr_psm.selectedIndex].value);">
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
                </table>
            </div>
            <?php
        }
    }
}

function HookOcrstreamEditReplaceuploadoptions() {
    global $lang;
    ?></div><h2 class="CollapsibleSectionHead"><?php echo $lang["ocr-upload-options"] ?></h2>
        <div class="CollapsibleSection" id="OCROptionsSection">
    <?php
}
