<?php
ob_start();
include_once 'connect.php';
$post = $_POST;
$pro_code = '';
$pro_id = 0;
$sql = '';
$flag = false;
switch($post['checktype'])
{
	//Check whether a product code exists or not
	case 'procode':
		$pro_code = $post['pro_code'];
		$pro_id = $post['pro_id'];
		$sql = 'CALL doesprocodeexists(:code,:id)';
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':code', $pro_code, PDO::PARAM_STR);
		$stmt->bindParam(':id', $pro_id, PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetch();
		if($row->Total > 0)
		{
			$flag = true;
		}
		echo $flag;
		die;
	break;
}
?>