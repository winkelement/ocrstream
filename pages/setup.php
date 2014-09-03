<?php

// Do the include and authorization checking ritual -- don't change this section.
include '../../../include/db.php';
include '../../../include/authenticate.php'; if (!checkperm('a')) {exit ($lang['error-permissiondenied']);}
include '../../../include/general.php';

// Specify the name of this plugin, the heading to display for the page and the
// optional introductory text. Set $page_intro to "" for no intro text
// Change to match your plugin.
$plugin_name = 'ocrstream';
$page_heading = $lang['ocrstream_title'];
$page_intro = '<p>' . $lang['ocrstream_intro'] . '</p>';
if (PHP_OS=='WINNT')
    {
    $tesseract_version_command = shell_exec($tesseract_path . '\tesseract.exe -v 2>&1');
    $tesseract_language_command = shell_exec($tesseract_path . '\tesseract.exe --list-langs 2>&1');
    $tesseract_version_output = explode("\n", $tesseract_version_command);
    $tesseract_language_output = explode("\n", $tesseract_language_command);
    $tesseract_version = $tesseract_version_output[0];
    $leptonica_version = $tesseract_version_output[1];
    $i = 1;
    $n = 0;
    while($i < count($tesseract_language_output))
    {
    $tesseract_languages[$n] = $tesseract_language_output[$i];
    $n++;
    $i++;
    }
    }
else 
    {
    $tesseract_version_command = shell_exec($tesseract_path . '/tesseract -v 2>&1');
    $tesseract_language_command = shell_exec($tesseract_path . '/tesseract --list-langs 2>&1');
    $tesseract_version_output = explode("\n", $tesseract_version_command);
    $tesseract_language_output = explode("\n", $tesseract_language_command);
    $tesseract_version = $tesseract_version_output[1];
    $leptonica_version = $tesseract_version_output[2];
    $i = 2;
    $n = 0;
    while($i < count($tesseract_language_output))
    {
    $tesseract_languages[$n] = $tesseract_language_output[$i];
    $n++;
    $i++;
    }
    }

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
