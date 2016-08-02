<?php
require 'inc/privilege.php';
function CMP_TYPE($way, $precision)
{
	if($way=='tra')
		return 0;
	else if($way=='float')
		return (1 << 16)+ ($precision & 65535);
	else if($way=='int')
		return 2 << 16;
	else if($way=='spj')
		return 3 << 16;
	return 0;
}
session_start();
if(!check_priv(PRIV_PROBLEM))
	die('你没有权限...');
else if(!isset($_POST['op']))
	die('参数无效...');

require 'inc/database.php';

if($_POST['op']=='del'){
    if(!isset($_POST['problem_id']))
        die('题目不存在...');
    $id=intval($_POST['problem_id']);
    $result=mysqli_query($con,"select defunct from problem where problem_id=$id");
    if($row=mysqli_fetch_row($result)){
	if($row[0]=='N') $opr='Y';
    else $opr='N';
    if(mysqli_query($con,"update problem set defunct='$opr' where problem_id=$id"))
        echo 'success';
    else
        echo '系统错误...';
    }
}else{
isset($_POST['time'])&&!empty($_POST['time']) ? $time=intval($_POST['time']) : die('请输入时间...');
if($time<0) die('请输入有效时间...');
isset($_POST['memory'])&&!empty($_POST['memory']) ? $memory=intval($_POST['memory']) : die('请输入内存...');
if($memory<0) die('请输入有效内存...');
isset($_POST['score'])&&!empty($_POST['score']) ? $score=intval($_POST['score']) : die('请输入每点分值...');
if($score<0) die('请输入有效每点分值...');
$compare_way=isset($_POST['compare']) ? CMP_TYPE($_POST['compare'], intval($_POST['precision'])) : 0;
isset($_POST['title'])&&!empty($_POST['title']) ? $title=mysqli_real_escape_string($con,$_POST['title']) : die('请输入标题...');
$des=isset($_POST['description']) ? mysqli_real_escape_string($con,$_POST['description']) : '';
$input=isset($_POST['input']) ? mysqli_real_escape_string($con,$_POST['input']) : '';
$output=isset($_POST['output']) ? mysqli_real_escape_string($con,$_POST['output']) : '';
$samp_in=isset($_POST['sample_input']) ? mysqli_real_escape_string($con,$_POST['sample_input']) : '';
$samp_out=isset($_POST['sample_output']) ? mysqli_real_escape_string($con,$_POST['sample_output']) : '';
$hint=isset($_POST['hint']) ? mysqli_real_escape_string($con,$_POST['hint']) : '';
$source=isset($_POST['source']) ? mysqli_real_escape_string($con,$_POST['source']) : '';

require 'inc/problem_flags.php';
$has_tex=0;
if(isset($_POST['option_open_source'])){
	switch(intval($_POST['option_open_source'])){
		case 0:
			break;
		case 1:
			$has_tex|=PROB_SOLVED_OPENSOURCE;
			break;
		case 2:
			$has_tex|=PROB_DISABLE_OPENSOURCE;
			break;
	}
}
if(isset($_POST['option_level'])){
	$l=intval($_POST['option_level']);
	$level_max=(PROB_LEVEL_MASK>>PROB_LEVEL_SHIFT);
	if($l>=0 && $l<=$level_max){
		$has_tex|=($l<<PROB_LEVEL_SHIFT);
	}
}
if(isset($_POST['hide_prob'])){
	$has_tex|=PROB_IS_HIDE;
}
foreach ($_POST as $value) {
	if(strstr($value,'[tex]') || strstr($value,'[inline]')) {
		$has_tex|=PROB_HAS_TEX;
		//echo $value;
		break;
	}
}


if($_POST['op']=='edit'){
	if(!isset($_POST['problem_id']))
		die('参数无效...');
	$id=intval($_POST['problem_id']);

	$result=mysqli_query($con,"update problem set title='$title',case_time_limit=$time,memory_limit=$memory,case_score=$score,description='$des',input='$input',output='$output',sample_input='$samp_in',sample_output='$samp_out',hint='$hint',source='$source',has_tex=$has_tex,compare_way=$compare_way where problem_id=$id");
	if(!$result)
		die('数据库操作失败...');
	else
		echo('success');
}else if($_POST['op']=='add'){
	$id=1000;
	$result=mysqli_query($con,'select max(problem_id) from problem');
	if( ($row=mysqli_fetch_row($result)) && intval($row[0]))
		$id=intval($row[0])+1;
	$result=mysqli_query($con,"insert into problem (problem_id,title,description,input,output,sample_input,sample_output,hint,source,in_date,memory_limit,case_time_limit,case_score,has_tex,compare_way) values ($id,'$title','$des','$input','$output','$samp_in','$samp_out','$hint','$source',NOW(),$memory,$time,$score,$has_tex,$compare_way)");
	if(!$result)
		die('数据库操作失败...');
	else
		echo('success');
}
}
?>