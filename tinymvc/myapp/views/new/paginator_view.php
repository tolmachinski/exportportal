<?php $perPage = (int) ($perPage ?? $per_p);?>

<?php if (!empty(ceil($count/$perPage))) {?>
	<?php echo $pagination;?>
<?php }?>
