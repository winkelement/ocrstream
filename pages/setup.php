<?php

// Do the include and authorization checking ritual -- don't change this section.
include '../../../include/db.php';
include '../../../include/authenticate.php';
if (!checkperm('a')) {
    exit($lang['error-permissiondenied']);
}
include '../../../include/general.php';

include_once "../include/ocrstream_functions.php";

// Specify the name of this plugin, the heading to display for the page and the
// optional introductory text. Set $page_intro to "" for no intro text
// Change to match your plugin.
$plugin_name = 'ocrstream';
$page_heading = $lang['ocrstream_title'];
$page_intro = '<p>' . $lang['ocrstream_intro'] . '</p>';
$page_def = array();

if (is_tesseract_installed()) {
    $tesseract_version = get_tesseract_version();
    $leptonica_version = get_leptonica_version();
    $tesseract_languages = get_tesseract_languages();
    $page_def[] = config_add_text_input('tesseract_path', $lang['tesseract_path_info']);
    $page_def[] = config_add_html("<p style=font-size:14px;>$tesseract_version<br>$leptonica_version</p>");
    if (tesseract_version_is_old()) {
        $page_def[] = config_add_html($lang['tesseract_old_version_info']);
        $page_def[] = config_add_html("<p<br></p>");
    }
    $page_def[] = config_add_single_select('ocr_global_language', $lang['ocrstream_language_select'], $tesseract_languages, false, 90);
} else {
    $page_def[] = config_add_text_input('tesseract_path', $lang['tesseract_path_input']);
}
//$page_def[] = config_add_single_select('ocr_psm_global', $lang['ocr_psm'], $ocr_psm_array, true, 414); // not really needed here
$page_def[] = config_add_single_ftype_select('ocr_ftype_1', $lang['ocr_ftype_1']);
$page_def[] = config_add_text_list_input('ocr_allowed_extensions', $lang['ocr_input_formats']);
$page_def[] = config_add_text_input('ocr_min_density', $lang['ocr_min_density'], false, 45);
$page_def[] = config_add_text_input('ocr_max_density', $lang['ocr_max_density'], false, 45);
$page_def[] = config_add_html($lang['ocr_max_density_help']);
$page_def[] = config_add_section_header($lang['im_processing_header'], $lang['im_processing_help']);
$page_def[] = config_add_html("<p style=font-size:18px;>Preset 1</p>");
$page_def[] = config_add_text_input('im_preset_1_density', $lang['im_preset_density'], false, 45);
$page_def[] = config_add_html($lang['im_preset_density_help']);
$page_def[] = config_add_text_input('im_preset_1_geometry', $lang['im_preset_geometry'], false, 45);
$page_def[] = config_add_html($lang['im_preset_geometry_help']);
$page_def[] = config_add_text_input('im_preset_1_quality', $lang['im_preset_quality'], false, 45);
$page_def[] = config_add_html($lang['im_preset_quality_help']);
$page_def[] = config_add_text_input('im_preset_1_deskew', $lang['im_preset_deskew'], false, 45);
$page_def[] = config_add_html($lang['im_preset_deskew_help']);
$page_def[] = config_add_text_input('im_preset_1_sharpen_r', $lang['im_preset_sharpen_r'], false, 45);
$page_def[] = config_add_html($lang['im_preset_sharpen_r_help']);
$page_def[] = config_add_text_input('im_preset_1_sharpen_s', $lang['im_preset_sharpen_s'], false, 45);
$page_def[] = config_add_html($lang['im_preset_sharpen_s_help']);

// Build the $page_def array of descriptions of each configuration variable the plugin uses.
// Each element of $page_def describes one configuration variable. Each description is
// created by one of the config_add_xxxx helper functions. See their definitions and
// descriptions in include/plugin_functions for more information.
// Do the page generation ritual -- don't change this section.
$upload_status = config_gen_setup_post($page_def, $plugin_name);
include '../../../include/header.php';
config_gen_setup_html($page_def, $plugin_name, $upload_status, $page_heading, $page_intro);
include '../../../include/footer.php';
?>
<script src="../lib/alphanum/jquery.alphanum.js"></script>
<script>
    jQuery("#ocr_min_density, \n\
            #ocr_max_density, \n\
            #im_preset_1_density,\n\
            #im_preset_1_geometry,\n\
            #im_preset_1_quality,\n\
            #im_preset_1_deskew,\n\
            #im_preset_1_sharpen_r,\n\
            #im_preset_1_sharpen_s").numeric("ocrstreamSetupNum");
</script>
