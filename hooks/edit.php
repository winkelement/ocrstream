<?php
function HookOcrstreamEditAfterfileoptions (){ // Hook for OCR on single resource (resource edit)

    global $ref;
    global $lang;

    ?>
    <div class="Question" id="question_ocr">
    <label for="ocr_single_resource"><?php echo $lang["ocr_single_resource"]?></label>

    <input type="button" name="ocr_start" value="<?php echo $lang["ocr_start"]?>">
    </div><!-- end of question_copyfrom -->
    <script>
        jQuery('[name="ocr_start"]').click(function(){

            jQuery.get('http://192.168.1.110/rsam/plugins/ocrstream/pages/scan.php?ref=5',function(data){
                console.log(JSON.parse(data));
            })

        });
    </script>
    <?php
    $resource_path = get_resource_path($ref,true,"",false,"");
    echo $resource_path; // debug
}

function HookOcrstreamEditBeforeaccessselector () {
    echo 'blubbblibubbli';
}
