<?php
ob_start();
session_start();
include_once 'connect.php';
include_once 'header.php';
$sessdata = $_SESSION;
$msg = '';
$thumb_dir = "uploads/thumb/";
if(array_key_exists('flash', $sessdata))
{
	$msg = $sessdata['flash'];
	unset($_SESSION['flash']);
}
$status = '1';
$sql_products = "CALL getproducts(:status)";
$stmt = $pdo->prepare($sql_products);
$stmt->bindParam(':status', $status, PDO::PARAM_STR);
$stmt->execute();
$serial = 1;
?>
<h3 align="center">Products List</h3>
<a href="addeditpro.php" class="btn btn-info">ADD PRODUCT</a>  
<br />  
<div class="table-responsive">
	<?php
	if(!empty($msg))
	{
		echo "<p style='color:red;font-weight:bold;'>".$msg."</p>";
	}
	?>
	 <table id="pro_data" class="table table-striped table-bordered">  
		  <thead>  
			   <tr>  
					<td>Serial No</td>  
					<td>Product Name</td>  
					<td>Product Code</td>  
					<td>Product Price(Rs.)</td>  
					<td>Product Image</td>
					<td>Action</td>					
			   </tr>  
		  </thead>  
		  <?php  
		  while($row = $stmt->fetch())  
		  {  
			  $src = empty($row->pro_img_thumb) ? 'img/no-img.jpg' : $thumb_dir.$row->pro_img_thumb;
			  $width = empty($row->pro_img_thumb) ? 120 : '';
			  $height = empty($row->pro_img_thumb) ? 100 : '';
			  
		  ?>
			<tr>
				<td><?php echo $serial; ?></td>
				<td><?php echo $row->pro_name; ?></td>
				<td><?php echo $row->pro_code; ?></td>
				<td><?php echo number_format($row->pro_unit_price); ?></td>
				<td><img src="<?php echo $src ; ?>" width="<?php echo $width; ?>" height="<?php echo $height; ?>" /></td>
				<td>
					<a href="Javascript:void(0);" class="label label-warning">Edit</a>
					<a target="_blank" href="Javascript:void(0);" class="label label-info">View</a>
                    <a href="Javascript:void(0);" class="label label-danger" onclick="return confirm('Are you sure to delete?')">Delete</a>
				</td>
			</tr>
		  <?php
			$serial++;
		  }  
		  ?>  
	 </table>  
</div>  
<?php include_once 'footer.php'; ?>
<script>  
 $(document).ready(function(){  
      $('#pro_data').DataTable();  
 });  
 </script>  