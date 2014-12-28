<?php

/** Configuration file for ResourceSpace plugin "ocrstream"
 * 
 * These are the default values. They can be overridden by using /plugins/sample/pages/setup.php
 * which is invoked by choosing Team Centre > Manage Plugins and then clicking on Options for the
 * ocrstream plugin once it has been activated.
 */
$ocr_global_language = '';
$tesseract_path = '/usr/bin';
$ocr_cronjob_enabled = false;
$ocr_allowed_extensions = array('tif', 'tiff', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'pdf');
$ocr_min_density = '300';
$ocr_max_density = '600';
$ocr_psm_global = '3';
$ocr_ftype_1 = 72;
$use_ocr_db_filter = false;
$ocr_db_filter_1 = "/[^a-zA-Züäöß\\s]/miu";
$ocr_db_filter_2 = "/(^|\\s)[a-zÜüäÄöÖß0-9](\\s|$)/miu";
$im_preset_1_density = '300';
$im_preset_1_geometry = '1024';
$im_preset_1_quality = '90';
$im_preset_1_deskew = '40';
$im_preset_1_sharpen_r = '0';
$im_preset_1_sharpen_s = '1';
$ocr_psm_array = array(
//    0 => 'Orientation and script detection (OSD) only.',
    1 => 'Automatic page segmentation with OSD.',
    2 => 'Automatic page segmentation, but no OSD, or OCR',
    3 => 'Fully automatic page segmentation, but no OSD. (Default)',
    4 => 'Assume a single column of text of variable sizes.',
    5 => 'Assume a single uniform block of vertically aligned text.',
    6 => 'Assume a single uniform block of text.',
    7 => 'Treat the image as a single text line.',
    8 => 'Treat the image as a single word.',
    9 => 'Treat the image as a single word in a circle.',
    10 => 'Treat the image as a single character.');

