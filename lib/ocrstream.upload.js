function showLoadingImage() {
    jQuery('#ocr_status_anim').append('<div id="loading-image"><img src="../plugins/ocrstream/gfx/loader_2.gif" alt="Loading..."  style="margin-left:59px;margin-top:10px" /><p style="margin-left:10px">OCR in progress...</p></div>');
};
function hideLoadingImage() {
    jQuery('#ocr_status_anim').fadeOut(800);
};
jQuery(document).ready(function () {
    jQuery.get(baseUrl + '/plugins/ocrstream/include/stage_1.php', {ref: resourceId, ocr_lang: ocr_lang}, function (data) {
        stageOneOutput = JSON.parse(data);
        console.log(stageOneOutput[1]); // debug
        if (stageOneOutput.hasOwnProperty("error")) {
            alert(stageOneOutput["error"]);
            return;
        }
        console.log(status + '2/4 ..'); // debug
        jQuery('#ocr_status_text').html(status + '2/4 ..');
        jQuery.get(baseUrl + '/plugins/ocrstream/include/stage_2.php', {ref: stageOneOutput[0], ocr_lang: ocr_lang, ocr_psm: ocr_psm, param_1: 'none', w: 0, h: 0, x: 0, y: 0}, function (data) {
            stageTwoOutput = JSON.parse(data);
            console.log(stageTwoOutput[1]); // debug
            if (stageTwoOutput.hasOwnProperty("error")) {
                alert(stageTwoOutput["error"]);
                return;
            }
            console.log(status + '3/4 ...'); // debug
            jQuery('#ocr_status_text').html(status + '3/4 ...');
            jQuery.get(baseUrl + '/plugins/ocrstream/include/stage_3.php', {ref: stageTwoOutput[0], ocr_lang: ocr_lang, ocr_psm: ocr_psm, param_1: 'none'}, function (data) {
                stageThreeOutput = JSON.parse(data);
                console.log(stageThreeOutput[1]); // debug
                if (stageThreeOutput.hasOwnProperty("error")) {
                    alert(stageThreeOutput["error"]);
                    return;
                }
                console.log(status + '4/4 ....'); // debug
                jQuery('#ocr_status_text').html(status + '4/4 ....');
                jQuery.get(baseUrl + '/plugins/ocrstream/include/stage_4.php', {ref: stageThreeOutput[0]}, function (data) {
                    stageFourOutput = JSON.parse(data);
                    console.log(stageFourOutput); // debug
                    if (stageFourOutput.hasOwnProperty("error")) {
                        alert(stageFourOutput["error"]);
                        return;
                    }
                    hideLoadingImage();
                });
            });
        });
    });
    showLoadingImage();
});