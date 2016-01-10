<?php 
echo '<form action="index.php" method="post">
	<input type="text" name="text"/>
	<input type="submit" value="Generate"/>
</form>';

$data = $_POST['text'];
?>
<img src="qr_img.php?d=<?php echo $data; ?>" />