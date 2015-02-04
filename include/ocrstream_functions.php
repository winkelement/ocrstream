<?php

/**
 * Get temp dir for ocrstream, create one if none exists
 * 
 * @return string OCRStream plugin temp directory
 */
function get_ocr_temp_dir() {
    $temp_dir = get_temp_dir();
    if(!is_dir($temp_dir . "/ocrstream_plugin")){
        mkdir($temp_dir . "/ocrstream_plugin",0777);        
    }
    $ocr_temp_dir = $temp_dir . "/ocrstream_plugin";
    return ($ocr_temp_dir);
}

/**
 * Checks if OS is Windows
 * 
 * @return boolean
 */
function is_windows() {
    $os = php_uname('s');
    if (stristr($os, 'win')) {
        if (stristr($os, 'Darwin')) {
            $is_windows = false;
        } else {
            $is_windows = true;
        }
    } else {
        $is_windows = false;
    }
    return $is_windows;
}

/**
 * Get path to tesseract executable
 * 
 * @global string $tesseract_path Tesseract directory path
 * @return string
 */
function get_tesseract_fullpath() {
    global $tesseract_path;
    if (is_windows()) {
        $tesseract_fullpath = $tesseract_path . '\tesseract.exe';
    } else {
        $tesseract_fullpath = $tesseract_path . '/tesseract';
    }
    return $tesseract_fullpath;
}

/**
 * Checks if tesseract executable exists
 * 
 * @return boolean 
 */
function is_tesseract_installed() {
    $tesseract_fullpath = get_tesseract_fullpath();
    if (file_exists($tesseract_fullpath)) {
        $tesseract_installed = true;
    } else {
        $tesseract_installed = false;
    }
    return $tesseract_installed;
}

/**
 * Get tesseract version
 * 
 * @return string Tesseract version output string
 */
function get_tesseract_version() {
    // Check if version information is already in plugin_config
    $plugin_config = get_plugin_config('ocrstream');
    if (isset($plugin_config['tesseract_version']) && isset($plugin_config['leptonica_version'])) {
        // Read version from config and return array
        $tesseract_version = $plugin_config['tesseract_version'];
        $leptonica_version = $plugin_config['leptonica_version'];
    } else {
        $tesseract_fullpath = get_tesseract_fullpath();
        $tesseract_version_command = run_command(escapeshellarg($tesseract_fullpath) . ' -v', true);
        $tesseract_version_output = explode("\n", $tesseract_version_command);
        if (stristr($tesseract_version_output [0], 'libtiff.so.5')) { // Skipping error output in first line if libftiff/liblept version mismatch
            $tesseract_version = $tesseract_version_output[1];
            $leptonica_version = $tesseract_version_output[2];
        } else {
            $tesseract_version = $tesseract_version_output[0];
            $leptonica_version = $tesseract_version_output[1];
        }
        // Add version to plugin_config and write it back to db
        $plugin_config['tesseract_version'] = trim($tesseract_version);
        $plugin_config['leptonica_version'] = trim($leptonica_version);
        set_plugin_config('ocrstream', $plugin_config);
    }
    return array($tesseract_version, $leptonica_version);
}

/**
 * Get available languages for tesseract-ocr
 * 
 * Returns an array of languages that are currently installed into the /TESSDATA directory.
 * Language codes use ISO 639-2 standard.
 * 
 * @link https://code.google.com/p/tesseract-ocr/downloads/list tesseract language data downloads
 * @return array 
 */
function get_tesseract_languages() {
    // Check if languages are already in plugin_config
    $plugin_config = get_plugin_config('ocrstream');
    if (isset($plugin_config['tesseract_languages'])) {
        // Read languages from config and return array
        $tesseract_languages = $plugin_config['tesseract_languages'];
    } else {
        // Get languages via tesseract cli
        $tesseract_fullpath = get_tesseract_fullpath();
        $tesseract_language_command = run_command(escapeshellarg($tesseract_fullpath) . ' --list-langs', true);
        $tesseract_languages = explode("\n", $tesseract_language_command);
        if (stristr($tesseract_languages [0], 'libtiff.so.5')) { // Skipping additional line if libftiff version does not match liblept version
            array_shift($tesseract_languages);
            array_shift($tesseract_languages);
            array_pop($tesseract_languages);
        } else {
            array_shift($tesseract_languages); // Skipping first line output ("Available languages...")
            array_pop($tesseract_languages); // Skipping last line (empty)
        }
        array_walk($tesseract_languages, 'trim_value');
        // Add langauges to plugin_config and write it back to db
        $plugin_config['tesseract_languages'] = $tesseract_languages;
        set_plugin_config('ocrstream', $plugin_config);        
    }
    return $tesseract_languages;
}

/**
 * Trim value, use for arrays
 * 
 * @param string $value
 */
function trim_value(&$value) {
    $value = trim($value);
}

/**
 * Check if tesseract version is old
 * 
 * Version 3.0.3 of tesseract includes many improvements and optimizations.
 * Older Versions do not support image input via textfile (list of imagepaths) for multipage processing and don't support pdf output.
 * 
 * @return boolean
 */
function tesseract_version_is_old() {
    $tesseract_version = get_tesseract_version()[0];
    if (substr($tesseract_version, 10, 1) < 3) {
        $tesseract_version_is_old = true;
    } else {
        if (substr($tesseract_version, 13, 1) < 3) {
            $tesseract_version_is_old = true;
        } else {
            $tesseract_version_is_old = false;
        }
    }
    return $tesseract_version_is_old;
}

/**
 * Check if PHP Session is started
 * 
 * @return boolean
 */
function is_session_started() {
    if ( php_sapi_name() !== 'cli' ) {
        if ( version_compare(phpversion(), '5.4.0', '>=') ) {
            return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
        } else {
            return session_id() === '' ? FALSE : TRUE;
        }
    }
    return FALSE;
}

/**
 * Get the file extension from the original resource file
 * 
 * @param int $ref
 * @return string file extension
 */
function get_file_extension ($ref) {
    $ext = sql_value("select file_extension value from resource where ref = '$ref'", '');
    return $ext;
}

/**
 * Check if Resource ID is valid INTEGER and exists in database
 * 
 * @param int $ID
 * @return boolean
 */
function is_resource_id_valid ($ID) {
    if ($ID == null || $ID < 1 || $ID > sql_value("SELECT ref value FROM resource ORDER BY ref DESC LIMIT 1", '')) {
    return false;
    } else {
    return true;
    }
}

/**
 * Get Image density
 * 
 * @global string $imagemagick_path
 * @param string $resource_path
 * @return mixed Density value
 */
function get_image_density ($resource_path) {
    $convert_fullpath = get_utility_path("im-convert");
    $density = run_command($convert_fullpath . ' -format %y ' . $resource_path . ' info:');
    return $density;
}

/**
 * Get image geometry
 * 
 * @param int $ID Resource ID
 * @return int Image geometry
 */
function get_image_geometry ($ID) {
    $geometry = sql_value("SELECT width value FROM resource_dimensions WHERE resource ='$ID'", '');
    return $geometry;
}

/**
 * Get Resource Type
 * 
 * @param int $ID Resource ID
 * @return int Resource type
 */
function get_res_type ($ID) {
    $res_type = sql_value("select resource_type value from resource where ref ='$ID'", '');
    return $res_type;
}

/**
 * Set the OCR state flag for a Resource
 * 
 * ocr_state = 1 : file flagged for ocr processing
 * ocr_state = 2 : ocr on this file has been completed
 * 
 * @param int $ID Resource ID
 * @param int $ocr_state OCR state flag
 * @return string Error message
 */
function set_ocr_state($ID, $ocr_state) {
    $ID_filter_options = ["options" =>['min_range' => 1, 'max_range' => sql_value("SELECT ref value FROM resource ORDER BY ref DESC LIMIT 1", '')]];
    $ID_filtered = filter_var($ID, FILTER_VALIDATE_INT, $ID_filter_options);
    $ocr_state_filter_options = ["options" =>['min_range' => 0, 'max_range' => 2]];
    $ocr_state_filtered = filter_var($ocr_state, FILTER_VALIDATE_INT, $ocr_state_filter_options);
    if (!$ID_filtered || !$ocr_state_filtered) {
        $error_msg = "Error setting OCR state ($ocr_state) for Resource ID ($ID).";
        debug("OCRStream: $error_msg");
        return($error_msg);
    }
    sql_query("UPDATE resource SET ocr_state =  '$ocr_state_filtered' WHERE ref = '$ID_filtered'");
}

function build_im_preset_1 ($im_preset_1_crop_w, $im_preset_1_crop_h, $im_preset_1_crop_x, $im_preset_1_crop_y) {
    global $im_preset_1_density, $im_preset_1_geometry, $im_preset_1_quality, $im_preset_1_deskew, $im_preset_1_sharpen_r, $im_preset_1_sharpen_s;
    $im_preset_1 = array(
    'colorspace' => ('-colorspace gray'),
    'type' => ('-type grayscale'),
    'density' => ('-density ' . $im_preset_1_density),
    'geometry' => ('-geometry ' . $im_preset_1_geometry),
    'crop' => ('-crop ' . $im_preset_1_crop_w . 'x' . $im_preset_1_crop_h . '+' . $im_preset_1_crop_x . '+' . $im_preset_1_crop_y),
    'quality' => ('-quality ' . $im_preset_1_quality),
    'trim' => ('-trim'),
    'deskew' => ('-deskew ' . $im_preset_1_deskew . '%'),
    'normalize' => ('-normalize'),
    'sharpen' => ('-sharpen ' . $im_preset_1_sharpen_r . 'x' . $im_preset_1_sharpen_s),
    //'depth'     => ('-depth 8'),
    );
    return $im_preset_1;
}

/**
 * Image pre-processing for OCR
 * 
 * @param int $ID Resource ID
 * @param array $im_preset Array of imagemagick options for image processing
 * @param string $ocr_temp_dir OCR temp directory
 */
function ocr_image_processing ($ID, $im_preset, $ocr_temp_dir) {
    set_time_limit(1800);
    $convert_fullpath = get_utility_path("im-convert");
    $ext = get_file_extension ($ID);
    $resource_path = get_resource_path($ID, true, "", false, $ext);
    $im_ocr_cmd = $convert_fullpath . " " . implode(' ', $im_preset) . ' ' . escapeshellarg($resource_path) . ' ' . escapeshellarg($ocr_temp_dir . '/im_tempfile_' . $ID . '.jpg');
    debug("CLI command: $im_ocr_cmd");
    $process = new Process($im_ocr_cmd);
    $process->setTimeout(3600);
    $process->run();
    debug ("CLI output: " . $process->getOutput());
    debug ("CLI errors: " . trim($process->getErrorOutput()));
}

/**
 * Tesseract processing
 * 
 * @param int $ID Resource ID
 * @param string $ocr_lang Language for OCR processing
 * @param int $ocr_psm Tesseract page segmentation mode
 * @param string $ocr_temp_dir Temp directory
 * @param string $mode Resource OCR mode
 * @param int $pg_num Number of pages 
 */
function tesseract_processing($ID, $ocr_lang , $ocr_psm, $ocr_temp_dir, $mode, $pg_num) {
    $tesseract_fullpath = get_tesseract_fullpath();
    if ($mode === 'multipage_new') {
        $n = 0;
        set_time_limit(1800);
        while ($n < $pg_num) {
            file_put_contents($ocr_temp_dir . '/im_ocr_file_' . $ID, trim($ocr_temp_dir . '/im_tempfile_' . $ID . '-' . $n . '.jpg').PHP_EOL, FILE_APPEND);
            $n++;
        }
        $tess_cmd = ($tesseract_fullpath . ' ' . $ocr_temp_dir . '/im_ocr_file_' . $ID . ' ' . escapeshellarg($ocr_temp_dir . '/ocr_output_file_' . $ID) . ' -l ' . $ocr_lang.' -psm ' . $ocr_psm);
        debug("CLI command: $tess_cmd");
        $process = new Process($tess_cmd);
        $process->setTimeout(3600);
        $process->setIdleTimeout(600);
        $process->run();
        debug ("CLI output: " . $process->getOutput());
        debug ("CLI errors: " . trim($process->getErrorOutput()));
    } elseif ($mode === 'multipage_old') {
        $i = 0;
        set_time_limit(1800);
        while ($i < $pg_num) {
            $ocr_input_file = ($ocr_temp_dir . '/im_tempfile_' . $ID . '-' . $i . '.jpg');
            $tess_cmd = ($tesseract_fullpath . ' ' . $ocr_input_file . ' ' . escapeshellarg($ocr_temp_dir . '/ocrtempfile_' . $ID) . ' -l ' . $ocr_lang.' -psm ' . $ocr_psm);
            debug("CLI command: $tess_cmd");
            $process = new Process($tess_cmd);
            $process->setTimeout(3600);
            $process->setIdleTimeout(600);
            $process->run();
            debug ("CLI output: " . $process->getOutput());
            debug ("CLI errors: " . trim($process->getErrorOutput()));
            file_put_contents($ocr_temp_dir . '/ocr_output_file_' . $ID . '.txt', file_get_contents($ocr_temp_dir . '/ocrtempfile_' . $ID . '.txt'), FILE_APPEND);
            $i ++;
        }
    } elseif ($mode === 'single_processed') {
        $ocr_input_file = ($ocr_temp_dir . '/im_tempfile_' . $ID . '.jpg');
        $tess_cmd = ($tesseract_fullpath . ' ' . $ocr_input_file . ' ' . escapeshellarg($ocr_temp_dir . '/ocr_output_file_' . $ID) . ' -l ' . $ocr_lang.' -psm ' . $ocr_psm);
        debug("CLI command: $tess_cmd");
        $process = new Process($tess_cmd);
        $process->run();
        debug ("CLI output: " . $process->getOutput());
        debug ("CLI errors: " . trim($process->getErrorOutput()));
    } elseif ($mode === 'single_original') {
        $ext = get_file_extension ($ID);
        $resource_path = get_resource_path($ID, true, "", false, $ext);
        $tess_cmd = (escapeshellarg($tesseract_fullpath) . ' ' . escapeshellarg($resource_path) . ' ' . escapeshellarg($ocr_temp_dir . '/ocr_output_file_' . $ID) . ' -l ' . $ocr_lang.' -psm ' . $ocr_psm);
        debug("CLI command: $tess_cmd");
        $process = new Process($tess_cmd);
        $process->run();
        debug ("CLI output: " . $process->getOutput());
        debug ("CLI errors: " . trim($process->getErrorOutput()));
    }   
}

function set_ocronjob_field () {
    $ocronjob_fieldname = 'ocronjob';
    $fieldnames = sql_array("SELECT name value FROM resource_type_field", '');
    if (in_array($ocronjob_fieldname, $fieldnames)) {
        return;
    } else {
        $last_field_number = sql_value("SELECT ref value FROM resource_type_field ORDER BY ref DESC LIMIT 1", '');
        $last_field_number++;
        sql_query("INSERT INTO resource_type_field "
                . "(ref, name, title, type, options, resource_type, display_field) "
                . "VALUES "
                . "($last_field_number, 'ocronjob', 'OCR Cronjob', '2', 'Cronjob enabled', '2', '0')", '');
    }
}