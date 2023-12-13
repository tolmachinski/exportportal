
tinymce.PluginManager.add('remote_preview', function(editor) {
	var settings = editor.settings
    var sandbox = !tinymce.Env.ie;
    var url = editor.settings.remote_preview_url || null;
    var data = editor.settings.remote_preview_data || {};
    var mapTo = editor.settings.remote_preview_map_to || null;
    var debug = editor.settings.remote_preview_debug || Boolean(0);
    var Promise = Promise || tinymce.util.Promise;
    var loadContent = function(url, data) {
        data = data || {};

        return new Promise(function(resolve, reject) {
            if('jQuery' in window) {
                jQuery.post(url, data).done(function(response) {
                    if(null !== mapTo) {
                        return resolve(mapTo(response));
                    }

                    return resolve(response);
                }).fail(function(error){
                    if(debug) {
                        console.error(error);
                    }

                    reject(new Error('Failed to load preview'));
                });
            } else {
                var payload = new FormData();
                for (var key in data) {
                    if (data.hasOwnProperty(key)) {
                        payload.append(key, data[key]);
                    }
                }

                tinymce.util.XHR.send({
                    url: url,
                    data: payload,
                    success: function(response, xhr) {
                        if(null !== mapTo) {
                            resolve(mapTo(response, xhr));
                        }

                        resolve(response.toString() || '');
                    },
                    error: function() {
                        reject(new Error('Failed to load preview'));
                    },
                });
            }
        });
    };

	editor.addCommand('mceRemotePreview', function() {
        if(null === url) {
            tinymce.activeEditor.notificationManager.open({
                text: 'You must specify URL that provides HTML contents',
                type: 'error'
            });

            return;
        }

		editor.windowManager.open({
			title: 'Preview',
			width: parseInt(editor.getParam("remote_preview_width", "650"), 10),
			height: parseInt(editor.getParam("remote_preview_height", "500"), 10),
			html: '<iframe src="javascript:\'\'" frameborder="0"' + (sandbox ? ' sandbox="allow-scripts"' : '') + '></iframe>',
			buttons: {
				text: 'Close',
				onclick: function() {
					this.parent().parent().close();
				}
			},
			onPostRender: function() {
                var self = this;
                var payload = Object.assign({}, data, {
                    content: editor.getContent(),
                });

                loadContent(url, payload).then(function(remoteContent){
                    var previewHtml, headHtml = '';

                    headHtml += '<base href="' + editor.documentBaseURI.getURI() + '">';
                    tinymce.each(editor.contentCSS, function(url) {
                        headHtml += '<link type="text/css" rel="stylesheet" href="' + editor.documentBaseURI.toAbsolute(url) + '">';
                    });

                    var bodyId = settings.body_id || 'tinymce';
                    if (bodyId.indexOf('=') != -1) {
                        bodyId = editor.getParam('body_id', '', 'hash');
                        bodyId = bodyId[editor.id] || bodyId;
                    }

                    var bodyClass = settings.body_class || '';
                    if (bodyClass.indexOf('=') != -1) {
                        bodyClass = editor.getParam('body_class', '', 'hash');
                        bodyClass = bodyClass[editor.id] || '';
                    }

                    var preventClicksOnLinksScript = (
                        '<script>' +
                            'document.addEventListener && document.addEventListener("click", function(e) {' +
                                'for (var elm = e.target; elm; elm = elm.parentNode) {' +
                                    'if (elm.nodeName === "A") {' +
                                        'e.preventDefault();' +
                                    '}' +
                                '}' +
                            '}, false);' +
                        '</script> '
                    );

                    var dirAttr = editor.settings.directionality ? ' dir="' + editor.settings.directionality + '"' : '';

                    previewHtml = (
                        '<!DOCTYPE html>' +
                        '<html>' +
                        '<head>' +
                            headHtml +
                        '</head>' +
                        '<body id="' + bodyId + '" class="mce-content-body ' + bodyClass + '"' + dirAttr + '>' +
                            remoteContent +
                            preventClicksOnLinksScript +
                        '</body>' +
                        '</html>'
                    );

                    if (!sandbox) {
                        // IE 6-11 doesn't support data uris on iframes
                        // so I guess they will have to be less secure since we can't sandbox on those
                        // TODO: Use sandbox if future versions of IE supports iframes with data: uris.
                        var doc = self.getEl('body').firstChild.contentWindow.document;
                        doc.open();
                        doc.write(previewHtml);
                        doc.close();
                    } else {
                        self.getEl('body').firstChild.src = 'data:text/html;charset=utf-8,' + encodeURIComponent(previewHtml);
                    }
                }).catch(function(error) {
                    if(debug) {
                        console.error(error);
                    }

                    tinymce.activeEditor.notificationManager.open({
                        text: 'Failed to load preview',
                        type: 'error'
                    });
                });
			}
		});
	});

	editor.addButton('remote_preview', {
		title: 'Preview',
		cmd: 'mceRemotePreview'
	});

	editor.addMenuItem('remote_preview', {
		text: 'Preview',
		cmd: 'mceRemotePreview',
		context: 'view'
	});
});