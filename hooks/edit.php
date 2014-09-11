<?php
function HookOcrstreamEditEditbeforesectionhead (){
    	global $ref;
	global $lang;
?>
	<div class="Question" id="question_ocr">
	<label for="ocr_single_resource"><?php echo $lang["ocr_single_resource"]?></label>
<!--	<input class="stdwidth" type="text" name="copyfrom" id="copyfrom" value="" style="width:80px;">-->
	<input type="submit" name="ocr_start" value="<?php echo $lang["ocr_start"]?>" onClick="event.preventDefault();CentralSpacePost(document.getElementById('mainform'),true);">
	</div><!-- end of question_copyfrom -->
	<?php

}
