<?php
require('inc/lang_conf.php');
require('inc/functions.php');
require ('inc/checklogin.php');

if(!isset($_SESSION['user']) || strlen($_SESSION['user'])==0)
	die('Not Logged in.');
if(!isset($_POST['language'],$_POST['problem']))
	die('Wrong argument');

$lang=intval($_POST['language']);
if(!array_key_exists($lang,$LANG_NAME))
	die('Invalid language');

$prob=intval($_POST['problem']);

if(!isset($_POST['source']))
	die('No source code.');
$code=$_POST['source'];
if(strlen($code)>29990)
	die('Code is too long.');

require('inc/database.php');

$res=mysqli_query($con,"select case_time_limit,memory_limit,case_score,compare_way,defunct,has_tex from problem where problem_id=$prob");
if(!($row=mysqli_fetch_row($res)))
	die('No such problem');

require('inc/problem_flags.php');
$forbidden=false;
if($row[4]=='Y' && !isset($_SESSION['administrator']))
  $forbidden=true;
else if($row[5]&PROB_IS_HIDE && !isset($_SESSION['insider']))
  $forbidden=true;
if($forbidden)
	die('You don\'t have permissions to access this problem');

$_SESSION['lang']=$lang;
mysqli_query($con,"update users set language=$lang where user_id='".$_SESSION['user']."'");
mysqli_query($con,"update problem set in_date=NOW() where problem_id=$prob");

$key=md5('key'.time().rand());
$share_code=(isset($_POST['public']) ? 1 : 0);

$data=array(
	'a'=>$prob,
	'b'=>$lang,
	'c'=>$row[0],
	'd'=>$row[1],
	'e'=>$row[2],
	'f'=>$code,
	'g'=>$_SESSION['user'],
	'h'=>$key,
	'i'=>$share_code,
	'j'=>$row[3]
);
ignore_user_abort(TRUE);
$result = posttodaemon($data);
//echo $result;
if(strstr($result,"OK"))
	header("location: wait.php?key=$key");
else
	die($result);
?>
