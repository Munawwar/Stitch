<?php
	define("TI_PROFILE", true); //For performance info. Look at apache headers for profile info.
	include('../stitch.php');
?>
<!DOCTYPE html>
<html>
	<head></head>
	<body>
		<div style="padding: 10px; background-color: #f0f0f0;">
			<?php defineblock('block1'); ?>
			<br/>
			<?php startblock('block2'); ?>
				Default text - block2
			<?php endblock(); ?>
		</div>
	</body>
</html>
