<?php


function get_tesseract_version ()
{
    global $tesseract_path;
    if (PHP_OS=='WINNT')
    {
    $tesseract_version_command = shell_exec($tesseract_path . '\tesseract.exe -v 2>&1');
    $tesseract_version_output = explode("\n", $tesseract_version_command);
    $tesseract_version = $tesseract_version_output[0];
    }
else 
    {
    $tesseract_version_command = shell_exec($tesseract_path . '/tesseract -v 2>&1');
    $tesseract_version_output = explode("\n", $tesseract_version_command);
    $tesseract_version = $tesseract_version_output[1];
    }
    return $tesseract_version;
}
function get_leptonica_version ()
{
    global $tesseract_path;
    if (PHP_OS=='WINNT')
    {
    $tesseract_version_command = shell_exec($tesseract_path . '\tesseract.exe -v 2>&1');
    $tesseract_version_output = explode("\n", $tesseract_version_command);
    $leptonica_version = $tesseract_version_output[1];
    }
else 
    {
    $tesseract_version_command = shell_exec($tesseract_path . '/tesseract -v 2>&1');
    $tesseract_version_output = explode("\n", $tesseract_version_command);
    $leptonica_version = $tesseract_version_output[2];
    }
    return $leptonica_version;
}
function get_tesseract_languages ()
{
    global $tesseract_path;
    if (PHP_OS=='WINNT')
    {
    $tesseract_language_command = shell_exec($tesseract_path . '\tesseract.exe --list-langs 2>&1');
    $tesseract_language_output = explode("\n", $tesseract_language_command);
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
    $tesseract_language_command = shell_exec($tesseract_path . '/tesseract --list-langs 2>&1');
    $tesseract_language_output = explode("\n", $tesseract_language_command);
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