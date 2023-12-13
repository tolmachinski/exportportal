<div class="wr-form-content w-700 mh-600">
    <div class="about-pp clearfix">
		<div class="box-1-pp">
			<div class="ppersonal-title">
				<h2 class="ppersonal-title__txt">About Us</h2>
			</div>

			<div id="block_about_us" class="text-about-b">
			<?php if($about_page['text_about_us'] != ''){
				echo $about_page['text_about_us'];
			} else{
				echo '<p>The information has not been added.</p>';
			}?>
			</div>
		</div>
		<div class="box-1-pp">
			<div class="ppersonal-title">
				<h2 class="ppersonal-title__txt">History</h2>
			</div>
			<div id="block_history" class="text-about-b">
			<?php if($about_page['text_history'] != ''){
				echo $about_page['text_history'];
			} else{
				echo '<p>The information has not been added.</p>';
			}?>
			</div>
		</div>
		<div class="box-1-pp">
			<div class="ppersonal-title">
				<h2 class="ppersonal-title__txt">Main products lines / services</h2>
			</div>
			<div id="block_what_we_sell" class="text-about-b">
			<?php if($about_page['text_what_we_sell'] != ''){
				echo $about_page['text_what_we_sell'];
			} else{
				echo '<p>The information has not been added.</p>';
			}?>
			</div>
		</div>
		<?php if(isset($about_page_aditional) && !empty($about_page_aditional)){
			foreach($about_page_aditional as $item){?>
			<div class="box-1-pp clearfix">
				<div class="ppersonal-title">
					<h2 class="ppersonal-title__txt"><?php echo $item['title_block'];?></h2>
				</div>
				<div  id="block_<?php echo $item['id_block'];?>" class="text-about-b">
					<?php echo $item['text_block'];?>
				</div>
			</div>
		<?php }
		}?>
	</div>
</div>
