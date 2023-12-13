/**
 * Justboil.me - a TinyMCE image upload plugin
 * jbimages/plugin.js
 *
 * Author: Viktor Kuzhelnyi, Bendiucov Oleg
 *
 * Version: 3.0 released 19/11/2014
 */

tinymce.PluginManager.add('jbimages', function(editor, url) {
	function jbBox() {
		editor.windowManager.open({
			title: 'Upload an image',
			file : url + '/dialog-v4.php',
			width : 350,
			height: 135,
			buttons: [{
				text: 'Upload',
				classes:'widget btn primary first abs-layout-item',
				disabled : true,
				onclick: 'close'
			},
			{
				text: 'Close',
				onclick: 'close'
			}]
		});
	}

	// Add a button that opens a window
	editor.addButton('jbimages', {
		tooltip: 'Upload an image',
		icon : 'image',
		text: 'Upload',
		onclick: jbBox
	});

	// Adds a menu item to the tools menu
	editor.addMenuItem('jbimages', {
		text: 'Upload image',
		icon : 'image',
		context: 'insert',
		onclick: jbBox
	});
});
