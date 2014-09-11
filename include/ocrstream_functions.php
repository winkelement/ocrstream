<?php

function get_tesseract_fullpath ()
{
    global $tesseract_path;
    if (PHP_OS=='WINNT')
    {
    $tesseract_fullpath = $tesseract_path . '\tesseract.exe';
    }   
    else 
    {
    $tesseract_fullpath = $tesseract_path . '/tesseract';    
    }
    return $tesseract_fullpath;
}

function get_tesseract_version ()
{
    $tesseract_fullpath = get_tesseract_fullpath();
    $tesseract_version_command = shell_exec($tesseract_fullpath . ' -v 2>&1');
    $tesseract_version_output = explode("\n", $tesseract_version_command);
    if (PHP_OS=='WINNT')
    {
    $tesseract_version = $tesseract_version_output[0];
    }
    else 
    {
    $tesseract_version = $tesseract_version_output[1];
    }
    return $tesseract_version;
}

function get_leptonica_version ()
{
    $tesseract_fullpath = get_tesseract_fullpath();
    $tesseract_version_command = shell_exec($tesseract_fullpath . ' -v 2>&1');
    $tesseract_version_output = explode("\n", $tesseract_version_command);
    if (PHP_OS=='WINNT')
    {
    $leptonica_version = $tesseract_version_output[1];
    }
    else 
    {
    $leptonica_version = $tesseract_version_output[2];
    }
    return $leptonica_version;
}

function get_tesseract_languages ()
{
    $tesseract_fullpath = get_tesseract_fullpath();
    $tesseract_language_command = shell_exec($tesseract_fullpath . ' --list-langs 2>&1');
    $tesseract_language_output = explode("\n", $tesseract_language_command);
    if (PHP_OS=='WINNT')
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