<?php
function HookOcrstreamEditCustompermshowfile (){ // Hook for OCR on single resource (resource edit)
    
    global $ref;
    global $lang;
    ?>
    <div class="Question" id="question_ocr">
    <label for="ocr_single_resource"><?php echo $lang["ocr_single_resource"]?></label>
    <input type="submit" name="ocr_start" value="<?php echo $lang["ocr_start"]?>" onClick="ocr_single_resource()">
    </div><!-- end of question_copyfrom -->
    <script>
    function ocr_single_resource() {
    document.getElementById("question_ocr").innerHTML = "Hello World";
    }
    </script>
    <?php
    $resource_path = get_resource_path($ref,true,"",false,"");
    echo $resource_path; // debug
}

function HookOcrstreamEditBeforeaccessselector () {
    echo 'blubbblibubbli';
}
