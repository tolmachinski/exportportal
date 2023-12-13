<?php if (is_privileged('user',$user_main['idu'],'have_library') || have_right('moderate_content')){ ?>
<script type="text/javascript">
	var callbackAddLibraryDocument = function (response) {
		_notifyContentChangeCallback();
	};
	var callbackEditLibraryDocument = function (response) {
		_notifyContentChangeCallback();
	};
	var callbackAddLibraryCategory = function (response) {
		return true;
	};
	var callbackEditLibraryCategory = function (response) {
		return true;
	};
</script>
<?php } ?>