<?php include('template.php'); ?>

<?php startblock('block1'); ?>
	Overridded text 1
<?php endblock(); ?>

<?php startblock('block2', TI::APPEND); ?>
	Appended text 2
<?php endblock(); ?>
