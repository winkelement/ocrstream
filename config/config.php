<?php

#
# Configuration file for ResourceSpace plugin "ocrstream"
#
# These are the default values. They can be overridden by using /plugins/sample/pages/setup.php
# which is invoked by choosing Team Centre > Manage Plugins and then clicking on Options for the
# ocrstream plugin once it has been activated.

$ocr_global_language = '';
$tesseract_path = '/usr/bin';
$ocr_allowed_extensions = array('tif', 'tiff', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'pdf');
$ocr_min_density = '300';
$ocr_max_density = '600';
$im_preset_1_density = '300';
$im_preset_1_geometry = '1024';
$im_preset_1_quality = '90';
$im_preset_1_shave_w = '0';
$im_preset_1_shave_h = '0';
$im_preset_1_deskew = '40';
$im_preset_1_sharpen_r = '0';
$im_preset_1_sharpen_s = '1';
$im_preset_1 = [
    'density'   => ('-density ' . $im_preset_1_density),
    'geometry'  => ('-geometry ' . $im_preset_1_geometry),
    'shave'     => ('-shave ' . $im_preset_1_shave_w . 'x' . $im_preset_1_shave_h),
    'quality'   => ('-quality ' . $im_preset_1_quality),
    'trim'      => ('-trim'),
    'deskew'    => ('-deskew ' . $im_preset_1_deskew . '%'),
    'normalize' => ('-normalize'),
    'sharpen'   => ('-adaptive-sharpen ' . $im_preset_1_sharpen_r . 'x' . $im_preset_1_sharpen_s),
   ];