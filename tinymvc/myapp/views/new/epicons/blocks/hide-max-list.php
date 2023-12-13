<script src="<?php echo __FILES_URL; ?>public/plug/hidemaxlistitem-1-3-4/hideMaxListItem-min.js"></script>
	<script>
	$(function(){
		$('#hideMaxListItemsTest').hideMaxListItems({
			'max': 1,
		});
	})
	</script>

	<div class="row">
		<div class="col-12 col-md-5">
			<div class="minfo-sidebar-box">
				<div class="minfo-sidebar-box__desc">
					<ul class="hide-max-list minfo-sidebar-box__list" id="hideMaxListItemsTest">
						<li class="minfo-sidebar-box__list-item">
							<a class="minfo-sidebar-box__list-link w-160" href="#">
								Aerospace and Defense
							</a>
							<span class="minfo-sidebar-box__list-counter">(3)</span>
						</li>
						<li class="minfo-sidebar-box__list-item">
							<a class="minfo-sidebar-box__list-link w-160" href="#">
								150
							</a>
							<span class="minfo-sidebar-box__list-counter">(2)</span>
						</li>
						<li class="minfo-sidebar-box__list-item">
							<a class="minfo-sidebar-box__list-link w-160" href="#">
								Willys
							</a>
							<span class="minfo-sidebar-box__list-counter">(2)</span>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
