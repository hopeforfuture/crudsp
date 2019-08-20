<?php
ob_start();
session_start();
include_once 'connect.php';
include_once 'header.php';
$large_dir = "uploads/large/";
$thumb_dir = "uploads/thumb/";
$getinfo = $_GET;
if(empty($getinfo))
{
	header("Location:index.php");
}
$pro_id = base64_decode($getinfo['pro_id']);
$sql_pro_info = 'CALL getproduct(:pro_id)';
$stmt = $pdo->prepare($sql_pro_info);
$stmt->bindParam(':pro_id', $pro_id, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch();
$stmt = null;
$pro_img_src = empty($row->pro_img_thumb) ? 'img/no-img.jpg' : $thumb_dir.$row->pro_img_thumb;
?>
<a href="index.php" class="btn btn-info">Back</a>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="form-group">
            <strong>Product Name:</strong>
            <?php echo $row->pro_name; ?>
        </div>
    </div>
	<div class="col-xs-12 col-sm-12 col-md-12">
        <div class="form-group">
            <strong>Product Code:</strong>
            <?php echo $row->pro_code; ?>
        </div>
    </div>
	<div class="col-xs-12 col-sm-12 col-md-12">
        <div class="form-group">
            <strong>Product Details:</strong>
            <?php echo $row->pro_details; ?>
        </div>
    </div>
	<div class="col-xs-12 col-sm-12 col-md-12">
        <div class="form-group">
            <strong>Product Manufacturer:</strong>
            <?php echo $row->pro_manufac; ?>
        </div>
    </div>
	<div class="col-xs-12 col-sm-12 col-md-12">
        <div class="form-group">
            <strong>Product Category:</strong>
            <?php echo $row->cat_name; ?>
        </div>
    </div>
	<div class="col-xs-12 col-sm-12 col-md-12">
        <div class="form-group">
            <strong>Product Unit Price:</strong>
            <?php echo "Rs.".number_format($row->pro_unit_price); ?>
        </div>
    </div>
	<div class="col-xs-12 col-sm-12 col-md-12">
        <div class="form-group">
            <strong>Product Weight:</strong>
            <?php echo $row->pro_weight." ".$row->pro_weight_unit; ?>
        </div>
    </div>
	<div class="col-xs-12 col-sm-12 col-md-12">
        <div class="form-group">
            <strong>Product Image:</strong>
            <img src="<?php echo $pro_img_src; ?>" />
        </div>
    </div>
</div>