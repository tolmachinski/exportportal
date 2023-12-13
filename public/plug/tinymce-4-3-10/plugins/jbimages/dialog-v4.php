<?php if(empty($_GET['timestamp'])){
exit(translate("systmess_error_page_permision"));
}?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
	<title>Upload an image</title>
	<script type="text/javascript" src="dialog-v4.js"></script>
	<link href="dialog-v4.css" rel="stylesheet" type="text/css">
</head>
<body>
	<form class="form-inline" id="upl" name="upl" action="/blogs/upload_photo/<?php echo $_GET['timestamp']?>" method="post" enctype="multipart/form-data" target="upload_target" onsubmit="jbImagesDialog.inProgress();">

		<div id="upload_in_progress" class="upload_infobar"><img src="img/spinner.gif" width="16" height="16" class="spinner">Upload in progress&hellip; <div id="upload_additional_info"></div></div>
		<div id="upload_infobar" class="upload_infobar"></div>

		<p id="upload_form_container">
			<input id="uploader" name="userfile" type="file" class="jbFileBox" onChange="document.upl.submit(); jbImagesDialog.inProgress();">
		</p>

	</form>

	<iframe id="upload_target" name="upload_target" src=""></iframe>

</body>
</html>
