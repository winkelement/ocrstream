<?php
include_once "../plugins/ocrstream/include/ocrstream_functions.php";
function HookOcrstreamEditAfterfileoptions()
        { // Hook for OCR on single resource (resource edit)
        global $ref;
//        $ref = 500; // testing inavlid values
        global $lang;
        global $baseurl;
        global $ocr_global_language;
//        global $ocr_global_language;    
//        $ocr_lang = 'fra'; // testing inavlid values
        $param_1 = 'X'; // placeholder for optional processing parameter for tesseract
        $usekeys = FALSE;
        $choices = get_tesseract_languages();
        ?>
        <script>
        function setLanguage(selectedLanguage){
                ocr_lang = selectedLanguage;
                return ocr_lang;
                }     
        jQuery('[name="ocr_start"]').click(function()
                {
                jQuery.get('<?php echo $baseurl ?>/plugins/ocrstream/pages/scan.php', { ref: '<?php echo $ref ?>', ocr_lang : (ocr_lang), param_1 : '<?php echo $param_1 ?>'}, function(data)
                        {
                        var1 = data;
                        console.log(JSON.parse(var1)); // debug
                        alert (JSON.parse(var1)); // debug 
                        });
                });
        </script>
        <div class="Question" id="question_ocr">
        <label for="ocr_single_resource"><?php echo $lang["ocr_single_resource"]?></label>
        <input type="button" name="ocr_start" value="<?php echo $lang["ocr_start"]?>">
        </div><!-- end of question_copyfrom -->
        <div class="Question" id="ocr_language_select">
        <label for="ocr_language_select"><?php echo $lang["ocr_language_select"]?></label>
        <select name="ocr_lang" id="ocr_lang" style="width:90px" onchange="setLanguage(this.form.ocr_lang.options[this.form.ocr_lang.selectedIndex].value);">
        <?php
        foreach($choices as $key => $choice)
                {
                $value=$usekeys?$key:$choice;
                echo '    <option value="' . $value . '"' . (($ocr_global_language==$value)?' selected':'') . ">$choice</option>";
                }
        ?>
        </select>            
        </div>
        <?php
        }

function HookOcrstreamEditBeforeaccessselector () {
    echo 'blubbblibubbli';
}
