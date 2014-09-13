<?php

function is_windows ()
{   
    $win_pattern = '/(WIN)(Win)(win)/';
    
    preg_match($win_pattern, php_uname("s"), $hits);
    
    (count($hits) > 0) ? $is_windows = true : $is_windows = false;
    
    return $is_windows;
}

function get_tesseract_fullpath ()
{
    global $tesseract_path;
    if (is_windows())
    {
    $tesseract_fullpath = $tesseract_path . '\tesseract.exe';
    }
    else
    {
    $tesseract_fullpath = $tesseract_path . '/tesseract';
    }
    return $tesseract_fullpath;
}

function is_tesseract_installed ()
{
    $tesseract_fullpath = get_tesseract_fullpath();
    $tesseract_lookup = shell_exec($tesseract_fullpath . ' -v 2>&1');

    if (PHP_OS=='WINNT'){
        $tesseract_installed = true;
    }
    else {
        $not_found_pattern = '/not found/';

        preg_match($not_found_pattern, $tesseract_lookup, $hits);
        if (count($hits) > 0) {
            $tesseract_installed = false;
        }
        else {
            $tesseract_installed = true;
        }
    }
    return $tesseract_installed;
}


function get_tesseract_version ()
{
    $tesseract_fullpath = get_tesseract_fullpath();
    $tesseract_version_command = shell_exec($tesseract_fullpath . ' -v 2>&1');
    $tesseract_version_output = explode("\n", $tesseract_version_command);
    if (is_windows())
    {
    $tesseract_version = $tesseract_version_output[0];
    }
    else
    {
    $tesseract_version = $tesseract_version_output[0];
    }
    return $tesseract_version;
}

function get_leptonica_version ()
{
    $tesseract_fullpath = get_tesseract_fullpath();
    $tesseract_version_command = shell_exec($tesseract_fullpath . ' -v 2>&1');
    $tesseract_version_output = explode("\n", $tesseract_version_command);
    if (is_windows())
    {
    $leptonica_version = $tesseract_version_output[0];
    }
    else
    {
    $leptonica_version = $tesseract_version_output[1];
    }
    return $leptonica_version;
}

function get_tesseract_languages ()
{
    $tesseract_fullpath = get_tesseract_fullpath();
    $tesseract_language_command = shell_exec($tesseract_fullpath . ' --list-langs 2>&1');
    $tesseract_language_output = explode("\n", $tesseract_language_command);
    if (is_windows())
    {
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
    $i = 2;
    $n = 0;
    while($i < count($tesseract_language_output))
    {
    $tesseract_languages[$n] = $tesseract_language_output[$i];
    $n++;
    $i++;
    }
    }
    return $tesseract_languages;
}

//function ocr_test ($ocrtestfile)
//{
//    global $ocr_global_language;
//    $tesseract_fullpath = get_tesseract_fullpath();
//    $tess_cmd = ($tesseract_fullpath . ' ' . $ocrtestfile . ' /home/robert/web/test -l ' . $ocr_global_language);
//    shell_exec($tess_cmd);
//    $tess_content = file_get_contents('/home/robert/web/test.txt');
//    return $tess_content;
//}
