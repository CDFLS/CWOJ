<?php
require 'inc/global.php';
if(!isset($_POST['id'])){
	echo _('Invalid Argument...');
    exit();
}

if(!isset($_SESSION['user'])){
	echo _('Please login first...');
    exit();
}
require 'inc/database.php';

$uid=($_SESSION['user']);
if('all'==$_POST['id']){
	mysqli_query($con,"update solution set public_code=1 where user_id='$uid'");
}else{
	$id=intval($_POST['id']);
	mysqli_query($con,"update solution set public_code=(!public_code) where solution_id=$id and user_id='$uid'");
	if(1==mysqli_affected_rows($con))
		echo 'success';
	else
		echo _('Something went wrong...');
}