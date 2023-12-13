<div class="wr-filter-admin-panel">
	<div class="filter-admin-panel" >
		<div class="title-b">Filter panel</div>
		<table class="w-100pr">
			<tr>
				<td>Search</td>
				<td>
					<div class="form-group mb-0">
						<div class="input-group">
                            <input type="text"
                                name="search"
                                max="200"
                                class="dt-filter form-control"
                                placeholder="Keywords"
                                data-title="Search">
							<span class="input-group-btn">
								<a class="dt-filter-apply dt-filter-apply-buttons btn btn-primary mr-0 mb-0">
                                    <i class="ep-icon ep-icon_magnifier"></i>
                                </a>
							</span>
						</div>
					</div>
				</td>
            </tr>

            <tr>
                <td>Status</td>
                <td>
                    <select class="form-control dt-filter" data-title="Status" name="status" data-type="select">
                        <option value="" data-default="true">All statuses</option>
                        <option value="<?php echo \App\Envelope\EnvelopeStatuses::CREATED; ?>">Draft</option>
                        <option value="<?php echo \App\Envelope\EnvelopeStatuses::NOT_PROCESSED; ?>">Need processing</option>
                        <option value="<?php echo \App\Envelope\EnvelopeStatuses::PROCESSED; ?>">Processed</option>
                        <option value="<?php echo \App\Envelope\EnvelopeStatuses::SENT; ?>">Sent</option>
                        <option value="<?php echo \App\Envelope\EnvelopeStatuses::DELIVERED; ?>">Delivered</option>
                        <option value="<?php echo \App\Envelope\EnvelopeStatuses::SIGNED; ?>">Signed</option>
                        <option value="<?php echo \App\Envelope\EnvelopeStatuses::DECLINED; ?>">Declined</option>
                        <option value="<?php echo \App\Envelope\EnvelopeStatuses::COMPLETED; ?>">Completed</option>
                        <option value="<?php echo \App\Envelope\EnvelopeStatuses::VOIDED; ?>">Deleted</option>
                    </select>
                </td>
            </tr>

            <tr>
                <td>Type</td>
                <td>
                    <select class="form-control dt-filter" data-title="Type" name="type" data-type="select">
                        <option value="" data-default="true">All types</option>
                        <option value="<?php echo \App\Envelope\EnvelopeTypes::INTERNAL; ?>">Internal</option>
                        <option value="<?php echo \App\Envelope\EnvelopeTypes::PERSONAL; ?>">Personal</option>
                    </select>
                </td>
            </tr>

            <tr>
				<td>Order ID</td>
				<td>
					<div class="form-group mb-0">
						<div class="input-group">
                            <input type="text"
                                name="order"
                                id="documents--order--filter-order"
                                class="dt-filter form-control"
                                placeholder="Order ID"
                                data-title="Order ID">
							<span class="input-group-btn">
								<a class="dt-filter-apply dt-filter-apply-buttons btn btn-primary mr-0 mb-0">
                                    <i class="ep-icon ep-icon_magnifier"></i>
                                </a>
							</span>
						</div>
					</div>
				</td>
            </tr>

			<tr>
				<td>Document ID</td>
				<td>
					<div class="form-group mb-0">
						<div class="input-group">
                            <input type="text"
                                name="document"
                                id="documents--order--filter-document"
                                class="dt-filter form-control"
                                placeholder="Document ID"
                                data-title="Document ID">
							<span class="input-group-btn">
								<a class="dt-filter-apply dt-filter-apply-buttons btn btn-primary mr-0 mb-0">
                                    <i class="ep-icon ep-icon_magnifier"></i>
                                </a>
							</span>
						</div>
					</div>
				</td>
            </tr>

            <tr>
				<td>Sender ID</td>
				<td>
					<div class="form-group mb-0">
						<div class="input-group">
                            <input type="text"
                                name="sender"
                                id="documents--order--filter-sender"
                                class="dt-filter form-control"
                                placeholder="Sender ID"
                                data-title="Sender ID">
							<span class="input-group-btn">
								<a class="dt-filter-apply dt-filter-apply-buttons btn btn-primary mr-0 mb-0">
                                    <i class="ep-icon ep-icon_magnifier"></i>
                                </a>
							</span>
						</div>
					</div>
				</td>
			</tr>

			<tr>
				<td>Sender name</td>
				<td>
					<div class="form-group mb-0">
						<div class="input-group">
                            <input
                                type="text"
                                name="sender_name"
                                class="dt-filter form-control"
                                placeholder="Sender name"
                                data-title="Sender name">
							<span class="input-group-btn">
								<a class="dt-filter-apply dt-filter-apply-buttons btn btn-primary mr-0 mb-0">
                                    <i class="ep-icon ep-icon_magnifier"></i>
                                </a>
							</span>
						</div>
					</div>
				</td>
			</tr>

            <tr>
				<td>Recipient ID</td>
				<td>
					<div class="form-group mb-0">
						<div class="input-group">
                            <input type="text"
                                name="recipient"
                                id="documents--order--filter-recipient"
                                class="dt-filter form-control"
                                placeholder="Recipient ID"
                                data-title="Recipient ID">
							<span class="input-group-btn">
								<a class="dt-filter-apply dt-filter-apply-buttons btn btn-primary mr-0 mb-0">
                                    <i class="ep-icon ep-icon_magnifier"></i>
                                </a>
							</span>
						</div>
					</div>
				</td>
			</tr>

			<tr>
				<td>Recipient name</td>
				<td>
					<div class="form-group mb-0">
						<div class="input-group">
                            <input type="text"
                                name="recipient_name"
                                class="dt-filter form-control"
                                placeholder="Recipient name"
                                data-title="Recipient name">
							<span class="input-group-btn">
								<a class="dt-filter-apply dt-filter-apply-buttons btn btn-primary mr-0 mb-0">
                                    <i class="ep-icon ep-icon_magnifier"></i>
                                </a>
							</span>
						</div>
					</div>
				</td>
            </tr>

            <tr>
                <td>Recipient Status</td>
                <td>
                    <select class="form-control dt-filter" data-title="Recipient Status" name="recipient_status" data-type="select">
                        <option value="" data-default="true">All statuses</option>
                        <option value="<?php echo \App\Envelope\RecipientStatuses::CREATED; ?>">Created</option>
                        <option value="<?php echo \App\Envelope\RecipientStatuses::SENT; ?>">Sent</option>
                        <option value="<?php echo \App\Envelope\RecipientStatuses::DELIVERED; ?>">Delivered</option>
                        <option value="<?php echo \App\Envelope\RecipientStatuses::SIGNED; ?>">Signed</option>
                        <option value="<?php echo \App\Envelope\RecipientStatuses::DECLINED; ?>">Declined</option>
                        <option value="<?php echo \App\Envelope\RecipientStatuses::COMPLETED; ?>">Completed</option>
                    </select>
                </td>
            </tr>

            <tr>
                <td>Recipient Type</td>
                <td>
                    <select class="form-control dt-filter" data-title="Recipient Type" name="recipient_type" data-type="select">
                        <option value="" data-default="true">All types</option>
                        <option value="<?php echo \App\Envelope\RecipientTypes::SIGNER; ?>">Signer</option>
                        <option value="<?php echo \App\Envelope\RecipientTypes::VIEWER; ?>">Viewer</option>
                        <option value="<?php echo \App\Envelope\RecipientTypes::OPERATOR; ?>">Operator</option>
                    </select>
                </td>
            </tr>

            <tr>
				<td>Created</td>
				<td>
                    <div class="input-group">
                        <input type="text"
                            name="created_from"
                            id="documents--order--filter-created-from"
                            class="date-picker dt-filter"
                            placeholder="From"
                            data-title="Created from"
                            readonly>
                        <div class="input-group-addon">-</div>
                        <input type="text"
                            name="created_to"
                            id="documents--order--filter-created-to"
                            class="date-picker dt-filter"
                            placeholder="To"
                            data-title="Created to"
                            readonly>
                    </div>
				</td>
			</tr>

			<tr>
				<td>Updated</td>
				<td>
                    <div class="input-group">
                        <input type="text"
                            name="updated_from"
                            id="documents--order--filter-updated-from"
                            class="date-picker dt-filter"
                            placeholder="From"
                            data-title="Updated from"
                            readonly>
                        <div class="input-group-addon">-</div>
                        <input type="text"
                            name="updated_to"
                            id="documents--order--filter-updated-to"
                            class="date-picker dt-filter"
                            placeholder="To"
                            data-title="Updated to"
                            readonly>
                    </div>
				</td>
            </tr>
		</table>
		<div class="wr-filter-list clearfix mt-10"></div>
	</div>
	<div class="btn-display">
		<div class="i-block"><i class="ep-icon ep-icon_filter"></i></div>
		<span>&laquo;</span>
    </div>

	<div class="wr-hidden" style="display: none;"></div>
</div>
