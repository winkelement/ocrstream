<?php

// Do the include and authorization checking ritual -- don't change this section.
include '../../../include/db.php';
include '../../../include/authenticate.php'; if (!checkperm('a')) {exit ($lang['error-permissiondenied']);}
include '../../../include/general.php';

include_once "../include/ocrstream_functions.php";

// Specify the name of this plugin, the heading to display for the page and the
// optional introductory text. Set $page_intro to "" for no intro text
// Change to match your plugin.
$plugin_name = 'ocrstream';
$page_heading = $lang['ocrstream_title'];
$page_intro = '<p>' . $lang['ocrstream_intro'] . '</p>';

$tesseract_version = get_tesseract_version();
$leptonica_version = get_leptonica_version();
$tesseract_languages = get_tesseract_languages();

// Build the $page_def array of descriptions of each configuration variable the plugin uses.
// Each element of $page_def describes one configuration variable. Each description is
// created by one of the config_add_xxxx helper functions. See their definitions and
// descriptions in include/plugin_functions for more information.

$page_def[] = config_add_text_input('tesseract_path', $lang['tesseract_path_info']);
$page_def[] = config_add_html("<p style=font-size:14px;>$tesseract_version<br>$leptonica_version</p>");
$page_def[] = config_add_multi_select('ocr_global_languages', $lang['ocrstream_language_select'], $tesseract_languages, $usekeys = false);

// Do the page generation ritual -- don't change this section.
$upload_status = config_gen_setup_post($page_def, $plugin_name);
include '../../../include/header.php';
config_gen_setup_html($page_def, $plugin_name, $upload_status, $page_heading, $page_intro);
include '../../../include/footer.php';
