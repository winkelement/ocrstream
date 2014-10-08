<?php
include_once "../plugins/ocrstream/include/ocrstream_functions.php";

// Hook for OCR on single resource (resource edit)
function HookOcrstreamEditAfterfileoptions() {
    global $ref;
    global $lang;
    global $baseurl;
    global $ocr_global_language;
    global $ocr_allowed_extensions;
    global $im_preset_1_geometry;
    if (is_tesseract_installed()){
    // Hide OCR options for filetypes not allowed
    $ext = sql_value("select file_extension value from resource where ref = '$ref'", '');
    if (in_array($ext, $ocr_allowed_extensions)) {
        // Get aspect ratio of image for calculating crop size
        $w_thumb = sql_value("select thumb_width value from resource where ref = '$ref'", '');
        $h_thumb = sql_value("select thumb_height value from resource where ref = '$ref'", '');
        $ar = ($w_thumb/$h_thumb);
        $w = $im_preset_1_geometry;
        $h = ($w/$ar);
        $usekeys = FALSE;
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
            function setParams(selectedParam) {
                param_1 = selectedParam;
                if (param_1 === 'pre_1') {
                    ocr_crop();
                }
                return param_1;
            }
            function showLoadingImage() {
                jQuery('#ocr_start').append('<td id="loading-image"><img src="../gfx/interface/loading.gif" alt="Loading..."  style="margin-left:17px" /></td>');
            }
            function hideLoadingImage() {
                jQuery('#loading-image').remove();
            }
            // Initilaize Parameters
            ocr_lang = jQuery('#ocr_lang :selected').text();
            param_1 = jQuery('#im_preset :selected').val();
            x = '0'; y = '0'; w = '0'; h = '0';
            // Only show jCrop when image is going to be processed
            if (param_1 === 'pre_1') {
                ocr_crop();
            }
            // Send parameters to scan.php and get result
            jQuery('[name="ocr_start"]').click(function ()
            {
                jQuery.get('<?php echo $baseurl ?>/plugins/ocrstream/pages/scan.php', {ref: '<?php echo $ref ?>', ocr_lang: (ocr_lang), param_1: (param_1), w: (w), h: (h), x: (x), y:(y)}, function (data)
                {
                    var1 = data;
                    console.log(JSON.parse(var1)); // debug
                    hideLoadingImage();
                    if (JSON.parse(var1) === 'ocr_error_1') {
                        alert('<?php echo $lang["ocr_error_1"] ?>');
                        return;
                    }
                    if (JSON.parse(var1) === 'ocr_error_2') {
                        alert('<?php echo $lang["ocr_error_2"] ?>');
                        return;
                    }
                    if (JSON.parse(var1) === 'ocr_error_3') {
                        alert('<?php echo $lang["ocr_error_3"] ?>');
                        return;
                    }
                    if (JSON.parse(var1) === 'ocr_error_4') {
                        alert('<?php echo $lang["ocr_error_4"] ?>');
                        return;
                    }
                    else
                    {
                        //@todo find a way to update 'Extracted text' field wihout reloading whole page
                        window.location.reload(true);
                    }
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
                            foreach ($choices as $key => $choice) {
                                $value = $usekeys ? $key : $choice;
                                echo '    <option value="' . $value . '"' . (($ocr_global_language == $value) ? ' selected' : '') . ">$choice</option>";
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
                            <?php }
                            ?>
                        </select></td>            
                </tr>
            </table>
            <div id="coords">
                <input type="hidden" id="x1" name="x1" />
                <input type="hidden" id="y1" name="y1" />
                <input type="hidden" id="w" name="w" />
                <input type="hidden" id="h" name="h" />
            </div>
        </div>
        <?php
    }
    }
}
