<?php
include_once "../plugins/ocrstream/include/ocrstream_functions.php";
// Hook for OCR on single resource (resource edit)
function HookOcrstreamEditAfterfileoptions()
        { 
        global $ref;
//        $ref = 500; // testing inavlid values
        global $lang;
        global $baseurl;
        global $ocr_global_language;
        global $ocr_allowed_extensions;
        // Don't show OCR options for filetypes not allowed
        $ext = sql_value("select file_extension value from resource where ref = '$ref'",'');
        if (in_array($ext, $ocr_allowed_extensions)) 
            {
//            $ocr_lang = 'fra'; // testing inavlid values
//            $param_1 = 'pre_1'; // placeholder for optional processing parameter for tesseract
            $usekeys = FALSE;
            $choices = get_tesseract_languages();
            ?>
            <script>
            function setLanguage(selectedLanguage){
                ocr_lang = selectedLanguage;
                return ocr_lang;
                }
            function setParams(selectedParam){
                param_1 = selectedParam;
                return param_1;
                }
            function showLoadingImage() {
                jQuery('#ocr_start').append('<td id="loading-image"><img src="../gfx/interface/loading.gif" alt="Loading..."  style="margin-left:17px" /></td>');
                }
            function hideLoadingImage() {
                jQuery('#loading-image').remove();
                }		
            ocr_lang = jQuery('#ocr_lang :selected').text();
            param_1 = jQuery('#im_preset :selected').val();
            jQuery('[name="ocr_start"]').click(function()
                {
                jQuery.get('<?php echo $baseurl ?>/plugins/ocrstream/pages/scan.php', { ref: '<?php echo $ref ?>', ocr_lang : (ocr_lang), param_1 : (param_1)}, function(data)
                        {
                        var1 = data;
                        console.log(JSON.parse(var1)); // debug
                        //alert (JSON.parse(var1)); // debug 
                        hideLoadingImage();
                        if (JSON.parse(var1) === 'ocr_error_1'){
                            alert ('<?php echo $lang["ocr_error_1"]?>');
                            return;
                            }
                        if (JSON.parse(var1) === 'ocr_error_2'){
                            alert ('<?php echo $lang["ocr_error_2"]?>');
                            return;
                            }
                        if (JSON.parse(var1) === 'ocr_error_3'){
                            alert ('<?php echo $lang["ocr_error_3"]?>');
                            return;
                            }
                        if (JSON.parse(var1) === 'ocr_error_4'){
                            alert ('<?php echo $lang["ocr_error_4"]?>');
                            return;
                            }                        
                        else
                            {
                            window.location.reload(true);
                            }
                        });
                showLoadingImage();
                });
            </script>
            <div class="Question" id="question_ocr" style="font-weight: normal">
            <table>
            <tr id = "ocr_start" style="height:37px">
                <td><label for="ocr_single_resource"><?php echo $lang["ocr_single_resource"]?></label></td>
                <td><input type="button" name="ocr_start" style="width:90px" value="<?php echo $lang["ocr_start"]?>"></td>
            </tr>
            <tr>
                <td><label for="ocr_language_select"><?php echo $lang["ocr_language_select"]?></label></td>
                <td><select name="ocr_lang" id="ocr_lang" style="width:90px" onchange="setLanguage(this.form.ocr_lang.options[this.form.ocr_lang.selectedIndex].value);">
                <?php
                foreach($choices as $key => $choice)
                    {
                    $value=$usekeys?$key:$choice;
                    echo '    <option value="' . $value . '"' . (($ocr_global_language==$value)?' selected':'') . ">$choice</option>";
                    }
                ?>
                </select></td>            
            </tr>
            <tr>
                <td><label for="im_preset_select"><?php echo $lang["im_preset_select"]?></label></td>
                <td><select name="im_preset" id="im_preset" style="width:90px" onchange="setParams(this.form.im_preset.options[this.form.im_preset.selectedIndex].value);">
                    <?php
                    if ($ext === 'pdf'){
                    ?>
                    <option value="pre_1" selected>Preset 1</option>
                    <?php }
                    else {
                    ?>
                    <option value="none" selected>none</option>  
                    <option value="pre_1">Preset 1</option>
                    <?php } 
                    ?>
                    </select></td>            
            </tr>
            </table>
            </div>
            <?php
            }
        }
