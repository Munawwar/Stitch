<?php
	define("TI_PROFILE", true); //For performance info. Look at apache headers for profile info.
	include('mti.php');
?>
<!DOCTYPE html>
<html>
	<head></head>
	<body>
		<div style="padding: 10px; background-color: #f0f0f0;">
			<?php startblock('block1'); ?>
				hello 1
			<?php endblock(); ?>
			<br/>
			<?php startblock('block2'); ?>
				hello 2
			<?php endblock(); ?>
		</div>
	</body>
</html>
