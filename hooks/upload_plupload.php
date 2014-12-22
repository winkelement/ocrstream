<?php

function HookOcrstreamUpload_pluploadAfterpluploadfile() {
    global $ref;
    global $baseurl;
    if (isset($_POST['ocr_upload_start'])){
    $ocr_lang=getvalescaped("ocr_lang","");
    $ocr_psm=getvalescaped("ocr_psm","");
        ?>
        <script>
            jQuery.get('<?php echo $baseurl ?>/plugins/ocrstream/include/stage_1.php', {ref: '<?php echo $ref ?>', ocr_lang: '<?php echo $_GET['ocr_lang'] ?>'}, function (data)
                {
                var1 = data;
                        console.log(JSON.parse(var1)); // debug
                        if (JSON.parse(var1) === 'ocr_error_4') {
                            alert('<?php echo $lang["ocr_error_4"] ?>');
                            return;
                        }
                        console.log('stage 2 started'); // debug
                        jQuery.get('<?php echo $baseurl ?>/plugins/ocrstream/include/stage_2.php', {ref: '<?php echo $ref ?>', ocr_lang: '<?php echo $_GET['ocr_lang'] ?>', ocr_psm: '<?php echo $_GET['ocr_psm'] ?>'}, function (data)
                        {
                            var2 = data;
                            console.log(JSON.parse(var2)); // debug
                            console.log('stage 3 started'); // debug
                            jQuery.get('<?php echo $baseurl ?>/plugins/ocrstream/include/stage_3.php', {ref: '<?php echo $ref ?>', ocr_lang: '<?php echo $_GET['ocr_lang'] ?>', ocr_psm: '<?php echo $_GET['ocr_psm'] ?>'}, function (data)
                            {
                                var3 = data;
                                console.log(JSON.parse(var3)); // debug
                                console.log('stage 4 started'); // debug
                                jQuery.get('<?php echo $baseurl ?>/plugins/ocrstream/include/stage_4.php', {ref: '<?php echo $ref ?>'}, function (data)
                                {
                                    var4 = data;
                                    console.log(JSON.parse(var4)); // debug
                                });
                            });                 
                        });
                    });
        </script>
        <?php
    }
//    update_field($ref, 72, 'Halloooofofo');
}

