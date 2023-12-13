</div><!-- content body -->
<!--==============================footer=================================-->
<div class="row mt-15">
    <div class="col-xs-12 bdt-2-black">
		<div class="copy pt-25 ml-50 pb-50">The page was loaded in {TMVC_TIMER} seconds. Export Portal Administration Team &copy; 2011 - <?php echo Date('Y');?></div>
    </div>
</div>
</div><!--MAIN -->

<div id="btn-scrollup">go up</div>

</body>
</html>

<?php
  encoreScripts();

  if (logged_in()) {
      encoreEntryScriptTags("chat_app");
  }
?>
