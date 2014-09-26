<?php
function HookOcrstreamEditAfterfileoptions()
        { // Hook for OCR on single resource (resource edit)
        global $ref;
//        $ref = 500; // testing inavlid values
        global $lang;
        global $baseurl;
        global $ocr_global_language;    
        $param_1 = 'X'; // placeholder for optional processing parameter for tesseract    
        ?>
        <div class="Question" id="question_ocr">
        <label for="ocr_single_resource"><?php echo $lang["ocr_single_resource"]?></label>
        <input type="button" name="ocr_start" value="<?php echo $lang["ocr_start"]?>">
        </div><!-- end of question_copyfrom -->
        <script>
        jQuery('[name="ocr_start"]').click(function()
                {
                jQuery.get('<?php echo $baseurl ?>/plugins/ocrstream/pages/scan.php', { ref: '<?php echo $ref ?>', ocr_lang : '<?php echo $ocr_global_language ?>', param_1 : '<?php echo $param_1 ?>'}, function(data)
                        {
                        var1 = data;
                        console.log(JSON.parse(var1)); // debug
                        alert (JSON.parse(var1)); // debug 
                        });
                });
        </script>
        <?php
//        $resource_path = get_resource_path($ref,true,"",false,"");
//        echo $resource_path; // debug
        }

function HookOcrstreamEditBeforeaccessselector () {
    echo 'blubbblibubbli';
}
