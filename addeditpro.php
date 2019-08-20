<?php
ob_start();
session_start();
include_once 'connect.php';
include_once 'ResizeImage.php';
include_once 'header.php';
$large_dir = "uploads/large/";
$thumb_dir = "uploads/thumb/";
$getinfo = $_GET;
// execute the stored procedure
$categories = array();
$proinfo = array();
$sql_categories = 'CALL getcategories(:cat_status)';
$status = '1';
$stmt = $pdo->prepare($sql_categories);
$stmt->bindParam(':cat_status', $status, PDO::PARAM_STR);
$stmt->execute();

while($row = $stmt->fetch())
{
	$categories[] = array(
		'id'=>$row->id,
		'name'=>$row->cat_name
	);
}
$stmt = null;
$pro_id = array_key_exists('pro_id', $getinfo)? base64_decode($getinfo['pro_id']) : 0;

/* Code for edit and delete */
if($pro_id > 0)
{
	/* This is for delete operation */
	if(array_key_exists('op', $getinfo))
	{
		$status = '0';
		$sql_product_del = 'CALL updateproductstatus(:pro_status,:pro_id)';
		$stmt_del = $pdo->prepare($sql_product_del);
		$stmt_del->bindParam(':pro_status', $status, PDO::PARAM_STR);
		$stmt_del->bindParam(':pro_id', $pro_id, PDO::PARAM_INT);
		$stmt_del->execute();
		$stmt_del = null;
		$_SESSION['flash'] = 'Product deleted successfully.';
		header("Location:index.php");
	}
	/* End */
	
	$sql_pro_info = 'CALL getproduct(:pro_id)';
	$stmt_get = $pdo->prepare($sql_pro_info);
	$stmt_get->bindParam(':pro_id', $pro_id, PDO::PARAM_INT);
	$stmt_get->execute();
	$proinfo = $stmt_get->fetch();
	$stmt_get = null;
}

if(!empty($_POST))
{
	$postdata = $_POST;
	foreach($postdata as $key=>$val)
	{
		$postdata[$key] = trim($val);
	}
	$filename = $_FILES['pro_img']['name'];
	$ext = '';
	$rand_num = 0;
	$thumb_name = '';
	$large_name = '';
	
	if(!empty($filename))
	{
		$fileinfo = pathinfo($filename);
		$ext = $fileinfo['extension'];
		$rand_num = rand(1000, 1000000);
		$large_name = $rand_num.".".$ext;
		$file_dir = $large_dir.$large_name;
		$thumb_name = $rand_num."_thumb".".".$ext;
		if(move_uploaded_file($_FILES['pro_img']['tmp_name'], $file_dir))
		{
			$resize = new ResizeImage($file_dir);
			$resize->resizeTo(120, 100, 'exact');
			$resize->saveImage($thumb_dir.$thumb_name);
			
			/* In case of update delete the old images */
			if($pro_id > 0)
			{
				if(!empty($proinfo->pro_img))
				{
					@unlink($large_dir.$proinfo->pro_img);
				}
				if(!empty($proinfo->pro_img_thumb))
				{
					@unlink($thumb_dir.$proinfo->pro_img_thumb);
				}
			}
		}
	}
	
	/* Code for insert */
	if($pro_id == 0)
	{
	
		$sql_insert = "CALL createproduct(:name, :code, :detail, :manufac, :cat_id, :unit_price, :img, :img_thumb, :weight, 
										  :weight_unit, :stock, :created_at)";
										  
		$stmt_insert = $pdo->prepare($sql_insert);
		
		$created = date('Y-m-d H:i:s', time());
		
		$stmt_insert->bindParam(':name', $postdata['pro_name'], PDO::PARAM_STR);
		$stmt_insert->bindParam(':code', $postdata['pro_code'], PDO::PARAM_STR);
		$stmt_insert->bindParam(':detail', $postdata['pro_details'], PDO::PARAM_STR);
		$stmt_insert->bindParam(':manufac', $postdata['pro_manufac'], PDO::PARAM_STR);
		$stmt_insert->bindParam(':cat_id', $postdata['pro_cat_id'], PDO::PARAM_INT);
		$stmt_insert->bindParam(':unit_price', $postdata['pro_unit_price']);
		$stmt_insert->bindParam(':img', $large_name, PDO::PARAM_STR);
		$stmt_insert->bindParam(':img_thumb', $thumb_name, PDO::PARAM_STR);
		$stmt_insert->bindParam(':weight', $postdata['pro_weight']);
		$stmt_insert->bindParam(':weight_unit', $postdata['pro_weight_unit'], PDO::PARAM_STR);
		$stmt_insert->bindParam(':stock', $postdata['pro_stock'], PDO::PARAM_INT);
		$stmt_insert->bindParam(':created_at', $created, PDO::PARAM_STR);
		$stmt_insert->execute();
		
		$_SESSION['flash'] = 'Product created successfully.';
	}
	/* End */
	
	/* Code for update */
	else
	{
		$sql_update = "CALL updateproduct(:name, :code, :detail, :manufac, :cat_id, :unit_price, :img, :img_thumb, :weight, 
										  :weight_unit, :stock, :pro_id)";
										  
		$stmt_update = $pdo->prepare($sql_update);
		
		/* If new image is not uploaded then keep the old ones */
		if(empty($filename))
		{
			$large_name = empty($proinfo->pro_img) ? '' : $proinfo->pro_img;
			$thumb_name = empty($proinfo->pro_img_thumb) ? '' : $proinfo->pro_img_thumb;
		}
		
		$stmt_update->bindParam(':name', $postdata['pro_name'], PDO::PARAM_STR);
		$stmt_update->bindParam(':code', $postdata['pro_code'], PDO::PARAM_STR);
		$stmt_update->bindParam(':detail', $postdata['pro_details'], PDO::PARAM_STR);
		$stmt_update->bindParam(':manufac', $postdata['pro_manufac'], PDO::PARAM_STR);
		$stmt_update->bindParam(':cat_id', $postdata['pro_cat_id'], PDO::PARAM_INT);
		$stmt_update->bindParam(':unit_price', $postdata['pro_unit_price']);
		$stmt_update->bindParam(':img', $large_name, PDO::PARAM_STR);
		$stmt_update->bindParam(':img_thumb', $thumb_name, PDO::PARAM_STR);
		$stmt_update->bindParam(':weight', $postdata['pro_weight']);
		$stmt_update->bindParam(':weight_unit', $postdata['pro_weight_unit'], PDO::PARAM_STR);
		$stmt_update->bindParam(':stock', $postdata['pro_stock'], PDO::PARAM_INT);
		$stmt_update->bindParam(':pro_id', $pro_id, PDO::PARAM_INT);
		$stmt_update->execute();
		
		$_SESSION['flash'] = 'Product updated successfully.';
	}
	/* End */
	
	header("Location:index.php");
}
?>

<a href="index.php" class="btn btn-info">Back</a>

<form method="post" enctype="multipart/form-data">
  <div class="row">
	<div class="form-group required">
		<div class="col-xs-3">
			<label class="control-label" for="pro_name">Product Name</label>
			<input class="form-control" id="pro_name" name="pro_name" type="text" value="<?php echo empty($proinfo->pro_name) ? '' : $proinfo->pro_name; ?>">
		</div>
	</div>
   </div>
   <div class="row">
		<div class="form-group required">
			<div class="col-xs-3">
				<label class="control-label" for="pro_code">Product Code</label>
				<input class="form-control" id="pro_code" name="pro_code" type="text" value="<?php echo empty($proinfo->pro_code) ? '' : $proinfo->pro_code; ?>">
			</div>
		</div>
	</div>
	<div class="row">
		<div class="form-group required">
			<div class="col-xs-3">
				<label class="control-label" for="pro_details">Product Decription</label>
				<textarea class="form-control" name="pro_details" id="pro_details"><?php echo empty($proinfo->pro_details) ? '' : $proinfo->pro_details; ?></textarea>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="form-group required">
			<div class="col-xs-3">
				<label class="control-label" for="pro_manufac">Product Manufacturer</label>
				<input class="form-control" id="pro_manufac" name="pro_manufac" type="text" value="<?php echo empty($proinfo->pro_manufac) ? '' : $proinfo->pro_manufac; ?>">
			</div>
		</div>
	</div>
	<div class="row">
		<div class="form-group required">
			<div class="col-xs-3">
				<label class="control-label" for="pro_cat_id">Product Categories</label>
				<select class="form-control" name="pro_cat_id" id="pro_cat_id">
					<option value="">---Select Category---</option>
					<?php
					if(!empty($categories))
					{
						foreach($categories as $cat)
						{
						?>
						<option <?php if(!empty($proinfo->pro_cat_id) && ($proinfo->pro_cat_id == $cat['id'])) { echo 'selected';} ?> value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
						<?php
						}
					}
					?>
				</select>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="form-group required">
			<div class="col-xs-3">
				<label class="control-label" for="pro_unit_price">Unit Price</label>
				<input class="form-control" id="pro_unit_price" name="pro_unit_price" type="text" value="<?php echo empty($proinfo->pro_unit_price) ? '' : $proinfo->pro_unit_price; ?>">
			</div>
		</div>
	</div>
	<div class="row">
		<div class="form-group required">
			<div class="col-xs-3">
				<label class="control-label" for="pro_weight">Product Weight</label>
				<input class="form-control" id="pro_weight" name="pro_weight" type="text" value="<?php echo empty($proinfo->pro_weight) ? '' : $proinfo->pro_weight; ?>">
			</div>
		</div>
	</div>
	<div class="row">
		<div class="form-group required">
			<div class="col-xs-3">
				<label class="control-label" for="pro_weight_unit">Weight Unit</label>
				<select class="form-control" class="form-control" name="pro_weight_unit" id="pro_weight_unit">
					<option value="">---Select Weight Unit---</option>
					<option <?php if(!empty($proinfo->pro_weight_unit) && ($proinfo->pro_weight_unit == 'lbs')) { echo 'selected';} ?> value="lbs">lbs</option>
					<option <?php if(!empty($proinfo->pro_weight_unit) && ($proinfo->pro_weight_unit == 'kgs')) { echo 'selected';} ?> value="kgs">kgs</option>
					<option <?php if(!empty($proinfo->pro_weight_unit) && ($proinfo->pro_weight_unit == 'gms')) { echo 'selected';} ?> value="gms">gms</option>
				</select>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="form-group required">
			<div class="col-xs-3">
				<label class="control-label" for="pro_weight">Product Stock</label>
				<input class="form-control" id="pro_stock" name="pro_stock" type="number" value="<?php echo empty($proinfo->pro_stock) ? '' : $proinfo->pro_stock; ?>">
			</div>
		</div>
	</div>
	<div class="row">
		<div class="form-group">
			<div class="col-xs-3">
				<label for="pro_img">Product Thumb Image</label>
				<input class="form-control" id="pro_img" name="pro_img" type="file">
				<?php
					if(!empty($proinfo->pro_id) && ($proinfo->pro_id > 0))
					{
						echo "<br/>";
						$thumb_img_src = empty($proinfo->pro_img_thumb) ? 'img/no-img.jpg' : $thumb_dir.$proinfo->pro_img_thumb;
						?>
						<img src="<?php echo $thumb_img_src; ?>" />
						<?php
					}
				?>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="form-group">
		   <div class="col-sm-offset-2 col-sm-10">
				<button type="submit" class="btn btn-default" onclick="Javascript: return formvalidate();">Save</button>
		   </div>
		</div>
	</div>
</form>

<?php include_once 'footer.php'; ?>

<script>
	 //CKEDITOR.config.colorButton_colors = 'CF5D4E,454545,FFF,CCC,DDD,CCEAEE,66AB16';
	 
     CKEDITOR.replace( 'pro_details', {
		 height: 300,
		 width: 600,
		 extraPlugins: 'colorbutton,colordialog'
	 });
	 function formvalidate()
	 {
		var err_pro_name = '';
		var err_pro_code = '';
		var err_pro_details = '';
		var err_pro_manufac = '';
		var err_pro_cat_id = '';
		var err_pro_unit_price = '';
		var err_pro_weight = '';
		var err_pro_weight_unit = '';
		var err_pro_stock = '';
		var err_pro_img = '';
		var err_msg = '';
		var separator = '<br/>';
		
		var pro_name = $.trim($("#pro_name").val());
		var pro_code = $.trim($("#pro_code").val());
		var pro_details = $.trim(CKEDITOR.instances['pro_details'].getData());
		var pro_manufac = $.trim($("#pro_manufac").val());
		var pro_cat_id = $.trim($("#pro_cat_id").val());
		var pro_unit_price = $.trim($("#pro_unit_price").val());
		var pro_weight = $.trim($("#pro_weight").val());
		var pro_weight_unit = $.trim($("#pro_weight_unit").val());
		var pro_stock = $.trim($("#pro_stock").val());
		var pro_img = $.trim($("#pro_img").val());
		var pro_id = '<?php echo $pro_id; ?>';
		var ext = '';
		var allowed_types = ['jpeg','jpg', 'png', 'gif'];
		
		if(pro_name == '')
		{
			err_pro_name = 'Product name required.' + separator;
		}
		if(pro_code == '')
		{
			err_pro_code = 'Product code required.' + separator;
		}
		else if(pro_code.length > 0)
		{
			$.ajax({
				type: "post",
				url: 'ajax.php',
				async: false,
				data: {pro_code: pro_code, pro_id: pro_id, checktype: 'procode'},
				success: function(response)
				{
					if(response)
					{
						err_pro_code = 'Product code exists.' + separator;
					}
				}
			})
		}
		if(pro_details == '')
		{
			err_pro_details = 'Product details required.' + separator;
		}
		if(pro_manufac == '')
		{
			err_pro_manufac = 'Product manufacturer required.' + separator;
		}
		if(pro_cat_id == '')
		{
			err_pro_cat_id = 'Product category required.' + separator;
		}
		if(pro_unit_price == '')
		{
			err_pro_unit_price = 'Product unit price required.' + separator;
		}
		else if(pro_unit_price.length > 0 && isNaN(pro_unit_price))
		{
			err_pro_unit_price = 'Product unit price is not valid.' + separator;
		}
		if(pro_weight == '')
		{
			err_pro_weight = 'Product weight required.' + separator;
		}
		else if(pro_weight.length > 0 && isNaN(Number(pro_weight)))
		{
			err_pro_weight = 'Product weight is not valid.' + separator;
		}
		if(pro_weight_unit == '')
		{
			err_pro_weight_unit = 'Product weight unit required.' + separator;
		}
		if(pro_stock == '')
		{
			err_pro_stock = 'Product stock required.' + separator;
		}
		else if(pro_stock.length > 0 && Number(pro_stock)< 0)
		{
			err_pro_stock = 'Product stock is not valid.' + separator;
		}
		if(pro_img.length > 0)
		{
			var index = pro_img.lastIndexOf('.')+1;
			ext = pro_img.substring(index);
			
			if(allowed_types.indexOf(ext) == -1)
			{
				err_pro_img = 'File type is not supported.' + separator;
			}
		}
		
		err_msg = err_pro_name + err_pro_code + err_pro_details + err_pro_manufac + err_pro_cat_id + err_pro_unit_price + err_pro_weight
		          + err_pro_weight_unit + err_pro_stock + err_pro_img;
				  
		if(err_msg.length > 0)
		{
			$(".modal-body").html(err_msg);
			$("#myModal").modal();
			return false;
		}
		else
		{
			return true;
		}
		
	 }
</script>