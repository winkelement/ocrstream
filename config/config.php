<?php
#
# Configuration file for ResourceSpace plugin "ocrstream"
#
# These are the default values. They can be overridden by using /plugins/sample/pages/setup.php
# which is invoked by choosing Team Centre > Manage Plugins and then clicking on Options for the
# sample plugin once it has been activated.

$ocr_global_language = '';
$tesseract_path = '/usr/bin';
$ocr_allowed_extensions = array('tif','tiff','jpg','jpeg','png','gif','bmp','pdf');
$ocr_min_density = '300';
$ocr_max_density = '600';
$im_pre_1 = array(300, 1024, 100, 0, 15, 15, 10, 0, 'white', 'none', 40, '0x1', 'png');
//density, geometry, quality, contrast-stretch, lat, contrast-stretch, fill, opaque, deskew, sharpen, filetype