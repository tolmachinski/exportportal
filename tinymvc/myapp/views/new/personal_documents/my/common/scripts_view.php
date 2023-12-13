<?php views()->display('new/download_script'); ?>
<script>
    $(function() {
        var onActionStart = function (table, preservePostion) {
            preservePostion = typeof preservePostion !== 'undefined' ? preservePostion : true;
            if (table.hasClass('modal-table')) {
                showLoader(table.closest('.modal-flex__form'));
            } else {
                var wrapper = table.closest('.dataTables_wrapper');
                var global = $(window);
                table.hide();
                wrapper.addClass('h-450');
                showLoader(wrapper);
                if (preservePostion) {
                    table.data('scrollPosition', window.scrollY || window.pageYOffset);
                    $(window).scrollTop(0);
                }
            }
        };
        var onActionEnd = function (table, preservePostion) {
            preservePostion = typeof preservePostion !== 'undefined' ? preservePostion : true;
            if (table.hasClass('modal-table')) {
                hideLoader(table.closest('.modal-flex__form'));
            } else {
                var wrapper = table.closest('.dataTables_wrapper');
                table.show();
                wrapper.removeClass('h-450');
                hideLoader(wrapper);
                if (preservePostion) {
                    $(window).scrollTop(table.data('scrollPosition') || 0);
                }
            }
        };
        var updateTables = function (refilter) {
            refilter = typeof refilter !== "undefined" ? refilter : true;

            if ($.fn.dataTable) {
                $.fn.dataTable.tables().forEach(function(table) {
                    $(table)
                        .dataTable()
                        .fnDraw(refilter);
                });
            }
        };
        var sendRequest = function (url, data) {
			return $.post(url, data || null, null, 'json');
		};
        var onDownloadDocument = function(button) {
            var url = __group_site_url + 'personal_documents/ajax_operation/download_document';
			var table = button.closest('table');
			var version = button.data('version');
			var documentId = button.data('document') || null;
            button.closest('.dropdown').click();
			var onRequestSuccess = function (data) {
				if ('success' === data.mess_type) {
					if (
						data.token
						&& typeof data.token === 'object'
						&& data.token.constructor === Object
						&& data.token.url
					) {
						downloadFile(data.token.url + (data.token.filename ? "?" + $.param({ name: data.token.filename }) : ''), data.token.filename || data.token.name);
					}
				} else {
					systemMessages(data.message, data.mess_type);
				}
			};

			if (null === documentId || null === version || typeof version === 'undefined') {
				return;
			}

			onActionStart(table, true);
			sendRequest(url, { document: documentId, version: version })
				.done(onRequestSuccess)
				.fail(onRequestError)
				.always(onActionEnd.bind(null, table, true));
        };
        var onUploadDocument = function(caller) {
            closeFancyBox();
            updateTables(false);
        };

        try {
            mix(window, {
                downloadDocument: onDownloadDocument,
                callbackUploadDocument: onUploadDocument,
                callbackReUploadDocument: onUploadDocument,
            });
        } catch (error) {
            if (__debug_mode) {
                if (!error instanceof TypeError) {
                    console.log(error);
                }
            }
        }
    });
</script>
