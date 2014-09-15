<?php

function is_windows ()
{
    $os = php_uname('s');
    if(stristr($os, 'win')){
    $is_windows = true;
    }
    else {
    $is_windows = false;
    }
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

    if (is_windows())
    {
        if (file_exists ($tesseract_fullpath))
        {
        $tesseract_installed = true;
        }
        else {
        $tesseract_installed = false;
        }
    }
    else
    {
        if (file_exists ($tesseract_fullpath))
        {
        $tesseract_installed = true;
        }
        else {
        $tesseract_installed = false;
        }
    }
    return $tesseract_installed;
}


function get_tesseract_version ()
{
    $tesseract_fullpath = get_tesseract_fullpath();
    $tesseract_version_command = shell_exec($tesseract_fullpath . ' -v 2>&1');
    $tesseract_version_output = explode("\n", $tesseract_version_command);

    if (stristr($tesseract_version_output [0],'libtiff.so.5')){ // Skipping error output in first line when libftiff version does not match liblept version
        $tesseract_version = $tesseract_version_output[1];
    }
    else {
        $tesseract_version = $tesseract_version_output[0];
    }

    return $tesseract_version;
}

function get_leptonica_version ()
{
    $tesseract_fullpath = get_tesseract_fullpath();
    $tesseract_version_command = shell_exec($tesseract_fullpath . ' -v 2>&1');
    $tesseract_version_output = explode("\n", $tesseract_version_command);
    if (stristr($tesseract_version_output [0],'libtiff.so.5')){ // Skipping error output in first line when libftiff version does not match liblept version
        $leptonica_version = $tesseract_version_output[2];
        }
        else {
        $leptonica_version = $tesseract_version_output[1];
        }
    return $leptonica_version;
}

function get_tesseract_languages ()
{
    $tesseract_fullpath = get_tesseract_fullpath();
    $tesseract_language_command = shell_exec($tesseract_fullpath . ' --list-langs 2>&1');
    $tesseract_language_output = explode("\n", $tesseract_language_command);
    if (stristr($tesseract_language_output [0],'libtiff.so.5')){ // Skipping error output in first line when libftiff version does not match liblept version
    $i = 2;
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
    $i = 1;
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

function tesseract_version_is_old ()
{
    $tesseract_version = get_tesseract_version();
    if (substr($tesseract_version, 10, 1) < 3)
    {
       $tesseract_version_is_old = true;
    }
    else {
        if (substr($tesseract_version, 13, 1) < 3)
            {
            $tesseract_version_is_old = true;
            }
        else {
            $tesseract_version_is_old = false;
        }
    }
    return $tesseract_version_is_old;
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
