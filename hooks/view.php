<?php
require_once dirname(__DIR__) . "/include/ocrstream_functions.php";

function HookOcrstreamViewAfterresourceactions ()
    {
    global $ref,$lang,$baseurl_short,$ocr_allowed_extensions,$ocr_rtype,$ocr_dev;
    $ext = get_file_extension($ref);
    $res_type = get_res_type ($ref);
    if (in_array($ext, $ocr_allowed_extensions) && $res_type === $ocr_rtype && $ocr_dev)
        {
        ?>
        <li><a onClick='return CentralSpaceLoad(this,true);' href='<?php echo $baseurl_short;?>plugins/ocrstream/pages/ocr.php?ref=<?php echo $ref?>'>
        <?php echo "<i class='fa fa-barcode'></i>&nbsp;" . 'OCR';?>
        </a></li>
        <?php
        return true;
        }
    }
?>
