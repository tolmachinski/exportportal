<?php use const App\Common\PUBLIC_DATETIME_FORMAT_INTERNATIONAL; ?>
<?php use App\Plugins\EPDocs\Rest\Objects\File; ?>
<table class="data table-striped table-bordered w-100pr mt-15 mb-15 vam-table">
    <thead>
        <tr>
            <th class="tal vam">Original name</th>
            <th class="tac vam w-200">Created at</th>
            <th class="w-100">Actions</th>
        </tr>
    </thead>
    <tbody class="tabMessage">
        <?php if (!empty($files)) { ?>
            <?php /** @var File $file */ ?>
            <?php foreach ($files as $file) { ?>
                <tr class="tr-doc" id="file-<?php echo cleanOutput($file->getId()); ?>">
                    <td class="vam">
                        <?php echo cleanOutput($file->getOriginalName()); ?>
                    </td>
                    <td class="tac vam">
                        <?php if (null !== $creation_date = $file->getCreatedAt()) { ?>
                            <?php echo cleanOutput($creation_date->format(PUBLIC_DATETIME_FORMAT_INTERNATIONAL)); ?>
                        <?php } else { ?>
                            &mdash;
                        <?php } ?>
                    </td>
                    <td class="vam tar td-actions">
                        <a class="ep-icon ep-icon_download call-function actions-buttons mr-0 fs-16 js-button js-file-button-download"
                            data-user="<?php echo cleanOutput($user['id']); ?>"
                            data-file="<?php echo cleanOutput($file->getId()); ?>"
                            data-callback="downloadUserUploadedFile"
                            <?php echo addQaUniqueIdentifier("admin-users__verification-file-list__download-file-button")?>
                            title="Download the file">
                        </a>
                        <a class="ep-icon ep-icon_trash txt-red confirm-dialog actions-buttons update-buttons mr-0 fs-16 js-button js-button-remove"
                            data-user="<?php echo cleanOutput($user['id']); ?>"
                            data-file="<?php echo cleanOutput($file->getId()); ?>"
                            data-message="Are you sure you want to remove this file?"
                            data-atas="admin-users__verification-form__confirm-remove-file-dialog__button-ok"
                            data-callback="removeUserUplodedFile"
                            <?php echo addQaUniqueIdentifier("admin-users__verification-file-list__remove-file-button")?>
                            title="Remove the file">
                        </a>
                    </td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td class="vam tac" colspan="3">
                    No uploaded files
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<script>
    $(function() {
        var removeUserUplodedFile = function (button) {
			var url = __group_site_url + 'verification/ajax_operations/remove_uploaded_file';
			var user = button.data('user') || null;
			var file = button.data('file') || null;
            var row = button.closest('tr');
            var form = button.closest('form');
			var onRequestSuccess = function (data) {
				systemMessages(data.message, data.mess_type);
				if ('success' === data.mess_type) {
					row.remove();
				}
			};

			if (null === file || null === user) {
				return;
			}

			showLoader(form);
			postRequest(url, { file: file, user: user })
				.then(onRequestSuccess)
				.catch(onRequestError)
				.finally(hideLoader.bind(null, form));
        };
        var downloadUserUploadedFile = function (button) {
            var url = __group_site_url + 'verification/ajax_operations/access_uploaded_file';
            var user = button.data('user') || null;
            var file = button.data('file') || null;
			var form = button.closest('form');
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

			if (null === file || null === user) {
				return;
			}

			showLoader(form);
			postRequest(url, { file: file, user: user })
				.then(onRequestSuccess)
				.catch(onRequestError)
				.finally(hideLoader.bind(null, form));
		};

        mix(window, {
            removeUserUplodedFile: removeUserUplodedFile,
            downloadUserUploadedFile: downloadUserUploadedFile,
		}, false);
    });
</script>
