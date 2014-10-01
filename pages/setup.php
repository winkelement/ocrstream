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
$page_def = array();

if (is_tesseract_installed()) 
        {
        $tesseract_version = get_tesseract_version();
        $leptonica_version = get_leptonica_version();
        $tesseract_languages = get_tesseract_languages();
        $page_def[] = config_add_text_input('tesseract_path', $lang['tesseract_path_info']);
        $page_def[] = config_add_html("<p style=font-size:14px;>$tesseract_version<br>$leptonica_version</p>");
        if (tesseract_version_is_old())
                {
                $page_def[] = config_add_html($lang['tesseract_old_version_info']);
                $page_def[] = config_add_html("<p<br></p>");
                }
        $page_def[] = config_add_single_select('ocr_global_language', $lang['ocrstream_language_select'], $tesseract_languages, false, 90);
        $page_def[] = config_add_text_list_input('ocr_allowed_extensions', $lang['ocr_input_formats']);
        $page_def[] = config_add_text_input('ocr_min_density', $lang['ocr_min_density'], false, 45);
        $page_def[] = config_add_text_input('ocr_max_density', $lang['ocr_max_density'], false, 45);
        $page_def[] = config_add_section_header('Image processing settings', 'Some resources may need addtional image processing before doing OCR. A temporary file will be created, the original resource will stay unchanged. You can adjust the parameters for this process here.');
        $page_def[] = config_add_html("<p style=font-size:18px;>Preset 1</p>");
        $page_def[] = config_add_text_input('im_preset_1_density', $lang['im_preset_density'], false, 45);
        $page_def[] = config_add_html($lang['im_preset_density_help']);
        $page_def[] = config_add_text_input('im_preset_1_geometry', $lang['im_preset_geometry'], false, 45);
        $page_def[] = config_add_html($lang['im_preset_geometry_help']);
        $page_def[] = config_add_text_input('im_preset_1_quality', 'quality', false, 45);
        $page_def[] = config_add_text_input('im_preset_1_deskew', 'deskew %', false, 45);
        $page_def[] = config_add_text_input('im_preset_1_sharpen_r', 'sharpen radius', false, 45);
        $page_def[] = config_add_text_input('im_preset_1_sharpen_s', 'sharpen sigma', false, 45);
        $page_def[] = config_add_text_input('im_preset_1_shave_w', 'shave width', false, 45);
        $page_def[] = config_add_text_input('im_preset_1_shave_h', 'shave height', false, 45);
        }
else 
        {
        $page_def[] = config_add_text_input('tesseract_path', $lang['tesseract_path_input']);
        }


// Build the $page_def array of descriptions of each configuration variable the plugin uses.
// Each element of $page_def describes one configuration variable. Each description is
// created by one of the config_add_xxxx helper functions. See their definitions and
// descriptions in include/plugin_functions for more information.


// Do the page generation ritual -- don't change this section.
$upload_status = config_gen_setup_post($page_def, $plugin_name);
include '../../../include/header.php';
config_gen_setup_html($page_def, $plugin_name, $upload_status, $page_heading, $page_intro);
include '../../../include/footer.php';
