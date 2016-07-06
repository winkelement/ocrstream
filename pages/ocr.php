<?php

require_once "../../../include/db.php";
require_once "../../../include/authenticate.php";
require_once "../../../include/general.php";
require_once "../../../include/resource_functions.php";
require_once dirname(__DIR__) . "/include/ocrstream_functions.php";

$ID = filter_input(INPUT_GET, 'ref', FILTER_VALIDATE_INT);

if (!is_resource_id_valid($ID))
    {
    include "../../../include/header.php";
    session_unset();
    echo $lang['ocr_error_1'];
    include "../../../include/footer.php";
    die;
    }

$resource = get_resource_data($ID);
$pg_num = get_page_count($resource, -1);
$imageurl = get_resource_path($ID,false,'pre',false,$resource["preview_extension"],-1,1);

include "../../../include/header.php";

?>
<h1>OCR</h1>
<div id='ocrimgdiv' style='float:left;padding:0;margin:0;'><img src="<?php echo $imageurl?>" id='ocrimage' /></div>
<div>Number of pages: <?php echo $pg_num; ?></div>
<div>Dummy</div>
<div>under development</div>

<?php

include "../../../include/footer.php";
