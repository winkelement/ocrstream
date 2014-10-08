<?php
#
# English Language File for the ResourceSpace plugin ocrstream
#
$lang['ocrstream_title'] = 'OCR Stream';
$lang['ocrstream_intro']='OCR Stream Plugin Configuration';
$lang['ocrstream_language_select'] = 'Standard language for text recognition (only installed laguages are displayed).';
$lang['tesseract_path_info'] = 'Path to tesseract-ocr:';
$lang['ocr_single_resource'] = 'OCR processing';
$lang['ocr_start'] = 'Start OCR';
$lang['tesseract_path_input'] = '<p style="color:red"><b>Tesseract-ocr not found, please specify path to tesseract-ocr executable:</b></p>';
$lang['tesseract_old_version_info'] = 'Please update tesseract to minimum version 3.03 for best performance and extended functionality.';
$lang['ocr_input_formats'] = 'Allowed filetypes:';
$lang['ocr_min_density'] = 'Minimum image density for OCR processing (dpi/ppi)';
$lang['ocr_max_density'] = 'Maximum image density for OCR processing (dpi/ppi)';
$lang['ocr_max_density_help'] = 'Images with density above that value will be processed before doing OCR.';
$lang["ocr_language_select"] = 'Language for text recognition';
$lang["ocr_parameter_select"] = 'Parameter 1';
$lang["ocr_error_1"] = 'Error: No valid Resource ID!.';
$lang["ocr_error_2"] = 'Error: Resource filetype is not allowed for OCR processing.';
$lang["ocr_error_3"] = 'Error: Image density (dpi/ppi) too low for OCR processing.';
$lang["ocr_error_4"] = 'Error: Image density (dpi/ppi) too high for OCR processing.';
$lang['im_processing_header'] = 'Image processing settings';
$lang['im_processing_help'] = 'Some resources need addtional image processing before doing the OCR. <br> A temporary file will be created, the original resource will not be changed. <br> You can adjust the settings for this process here.';
$lang['im_preset_density'] = 'Density (-density [dpi])';
$lang['im_preset_density_help'] = 'Sets the density of the temporary image for OCR processing. <br> Lower values can speed up processing but results can get worse.';
$lang['im_preset_geometry'] = 'Size (-geometry [px])';
$lang['im_preset_geometry_help'] = 'Sets maximum width of the temporary image. <br> Very high values can slow down processing.';
$lang['im_preset_quality'] = 'Quality (-quality [Wert])';
$lang['im_preset_quality_help'] = "Sets the zlib compression level and filter-type for the PNG image format. <br> Values 0-100, default PNG quality is 75. Higher Values can produce better results at cost of processing time.";
$lang['im_preset_deskew'] = 'Straighten (-deskew [%])';
$lang['im_preset_deskew_help'] = 'Straighten an image. Works poor at angles > 5%. <br> A threshold of 40% works for most images.';
$lang['im_preset_sharpen_r'] = 'Adaptive sharpen radius <br> (-adaptive-sharpen [radius]x[sigma])';
$lang['im_preset_sharpen_r_help'] = 'Sets the radius of the Gaussian operator.';
$lang['im_preset_sharpen_s'] = 'Adaptive sharpen sigma <br> (-adaptive-sharpen [radius]x[sigma])';
$lang['im_preset_sharpen_s_help'] = 'Sets the standard deviation of the Gaussian operator. Default is 1.';
$lang["im_preset_select"] = 'Image processing';
?>
