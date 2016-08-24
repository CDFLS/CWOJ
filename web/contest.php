<?php
require 'inc/global.php';
require 'inc/ojsettings.php';
require 'inc/checklogin.php';
require 'inc/database.php';
require 'inc/privilege.php';

if(isset($_GET['level'])){
  //If request level page
  require 'inc/problem_flags.php';
  $level_max=(PROB_LEVEL_MASK>>PROB_LEVEL_SHIFT);
  if(isset($_GET['page_id']))
	$page_id=intval($_GET['page_id']);
  else
	$page_id=1;
  $level=intval($_GET['level']);
  if($level<0 || $level>$level_max){
	header("Location: contest.php");
    exit();
  }
  $addt_cond=" (has_tex&".PROB_LEVEL_MASK.")=".($level<<PROB_LEVEL_SHIFT);
  if(!check_priv(PRIV_PROBLEM))
	$addt_cond.=" and defunct='N' ";
  $range="limit ".(($page_id-1)*100).",100";
  if(isset($_SESSION['user'])){
	$user_id=$_SESSION['user'];
	$result=mysqli_query($con,"SELECT contest_id,title,start_time,end_time,defunct,num,source,judge_way,has_tex,joined.res,saved.cid from contest LEFT JOIN (select contest_id as cid,1 as res from contest_status where user_id='$user_id' group by contest_id) as joined on(joined.cid=contest_id) left join (select contest_id as cid from saved_contest where user_id='$user_id') as saved on(saved.cid=contest_id) where $addt_cond order by contest_id desc $range");
  }else{
	$result=mysqli_query($con,"select contest_id,title,start_time,end_time,defunct,num,source,judge_way from contest where $addt_cond order by contest_id desc $range");
  }
  if(mysqli_num_rows($result)==0) $info=_('There\'s no contest of this level');
}else{
  //If request contest page
  if(check_priv(PRIV_PROBLEM)){
    $addt_cond1='';
    $addt_cond='';
  }else{
    $addt_cond1="where defunct='N'";
    $addt_cond=" defunct='N' and ";
  }
  $row=mysqli_fetch_row(mysqli_query($con,"select max(contest_id) from contest $addt_cond1"));
  $maxpage=intval($row[0]/100);
  
  if(isset($_GET['page_id']))
    $page_id=intval($_GET['page_id']);
  else if(isset($_SESSION['view']))
    $page_id=intval($_SESSION['view']/100);
  else
    $page_id=$maxpage;
   
  if($page_id<10){
    header("Location: contest.php");
    exit();
  }else if($page_id>$maxpage){
    if($maxpage==0) $info=_('Looks like there\'s no contest here');
    else{
      header("Location: contest.php?page_id=$maxpage");
      exit();
    }
  }
  $range="between $page_id"."00 and $page_id".'99';
  if(isset($_SESSION['user'])){
    $user_id=$_SESSION['user'];
    $result=mysqli_query($con,"SELECT contest_id,title,start_time,end_time,defunct,num,source,judge_way,has_tex,joined.res,saved.cid from contest LEFT JOIN (select contest_id as cid,1 as res from contest_status where user_id='$user_id' group by contest_id) as joined on (joined.cid=contest_id) left join (select contest_id as cid from saved_contest where user_id='$user_id') as saved on(saved.cid=contest_id) where $addt_cond contest_id $range order by contest_id desc");
  }else{
    $result=mysqli_query($con,"select contest_id,title,start_time,end_time,defunct,num,source,judge_way from contest where $addt_cond contest_id $range order by contest_id desc");
  }
}

$inTitle=_('Contests');
$Title=$inTitle .' - '. $oj_name;
?>
<!DOCTYPE html>
<html>
  <?php require 'head.php';?>

  <body>
    <?php require 'page_header.php';?>
    <div class="container">
      <div class="row">
		<div class="col-xs-12 text-center">
		<?php if(!isset($level)){?>
		  <ul class="pagination">
		  <?php
			if($maxpage>10){
			  for($i=$maxpage;$i>=10;--$i)
				if($i!=$page_id)
				  echo '<li><a href="contest.php?page_id=',$i,'">',$i,'</a></li>';
				else
				  echo '<li class="active"><a href="contest.php?page_id=',$i,'">',$i,'</a></li>';
			}?>
			<li><a href="contest.php?level=0"><i class="fa fa-fw fa-list-ul"></i> <?php echo _('Levels')?> <i class="fa fa-angle-double-right"></i></a></li>
		  </ul>
		  <?php }else{?>  
		  <ul class="pagination">
			<li><a href="contest.php"><i class="fa fa-angle-double-left"></i> <i class="fa fa-fw fa-th-list"></i> <?php echo _('All')?></a></li>
            <?php
              for($i=0;$i<=$level_max;++$i){
                if($i!=$level)
                  echo '<li>';
                else
                  echo '<li class="active">';
                echo '<a href="contest.php?level=',$i,'">',$i,'</a></li>';
              }
            ?>
		  </ul>
		  <?php }?>
		</div>
      </div>
      <div class="row">
        <div class="col-xs-12">
          <?php if(isset($info)){?>
            <div class="text-center none-text none-center">
              <p><i class="fa fa-meh-o fa-4x"></i></p>
              <p><b>Whoops</b><br>
              <?php echo $info?></p>
            </div>
          <?php }else{?>
		  <div class="table-responsive">
		    <table class="table table-striped table-bordered" id="contest_table">
			  <thead>
              <tr>
				<th style="width:6%">ID</th>
				<?php 
				if(isset($_SESSION['user']))
				  echo '<th colspan="3">';
				else
				  echo '<th>';
                echo _('Title'),'</th>';?>
				<th style="width:15%"><?php echo _('Start Time')?></th>  
				<th style="width:5%"><?php echo _('Status')?></th>  
                <th style="width:10%"><?php echo _('Format')?></th>
				<th style="width:25%"><?php echo _('Tags')?></th>
              </tr>
              </thead>
              <tbody>
              <?php 
              while($row=mysqli_fetch_row($result)){
                switch ($row[7]){
                  case 0:
                    $judge_way=_('Training');
                    break;
                  case 1:
                    $judge_way=_('CWOJ');
                    break;
                  case 2:
                    $judge_way=_('ACM-like');
                    break;
                  case 3:
                    $judge_way=_('OI-like');
                    break;
                }
                if(time()>strtotime($row[3])) $cont_status='<span class="label label-wa">'._('Ended').'</span>';
                else if(time()<strtotime($row[2])) $cont_status='<span class="label label-re">'._('Upcoming').'</span>';
                else $cont_status='<span class="label label-ac">'._('In Progress').'</span>';
                echo '<tr>';
                echo '<td>',$row[0],'</td>';
                if(isset($_SESSION['user'])){
				  echo '<td class="width-for-2x-icon"><i class=', is_null($row[9]) ? '"fa fa-fw fa-remove fa-2x" style="visibility:hidden"' : '"fa fa-fw fa-2x fa-paper-plane" style="color:steelblue"', '></i>', '</td>';
				  echo '<td style="text-align:left;border-left:0;">';
                }else echo '<td style="text-align:left">';
                echo '<a href="contestpage.php?contest_id=',$row[0],'">',$row[1];
                if($row[4]=='Y')echo '&nbsp;&nbsp;<span class="label label-danger">',_('Deleted'),'</span>';
                echo '</a>';
                if(isset($_SESSION['user']))
				  echo '<td class="width-for-2x-icon" style="border-left:0;"><i data-pid="',$row[0],'" class="', is_null($row[10]) ? 'fa fa-star-o' : 'fa fa-star', ' fa-fw fa-2x text-warning save_problem" style="cursor:pointer;"></i></td>';
                echo'</td><td>',$row[2],'</a></td>';
                echo '<td>',$cont_status,'</td>';
                echo '<td>',$judge_way,'</td>';
                echo '<td>',$row[6],"</td></tr>\n";
              }?>
              </tbody>
		    </table>
          </div>
          <?php }?>
        </div>
      </div>
      <div class="row">
        <ul class="pager">
          <li>
            <?php if(!isset($_GET['level'])){?>
            <a class="pager-pre-link shortcut-hint" title="Alt+A" <?php 
              if($page_id>10) echo 'href="contest.php?page_id='.($page_id-1).'"';
            ?>><i class="fa fa-fw fa-angle-left"></i> <?php echo _('Previous')?></a>
            <?php }else{?>
            <a class="pager-pre-link shortcut-hint" title="Alt+A" <?php
              if($page_id>1) echo 'href="contest.php?level='.$level.'&page_id='.($page_id-1).'"';
            ?>><i class="fa fa-fw fa-angle-left"></i> <?php echo _('Previous')?></a>
            <?php }?>
          </li>
          <li>
             <?php if(!isset($_GET['level'])){?>
            <a class="pager-next-link shortcut-hint" title="Alt+D" <?php 
              if($page_id<$maxpage) echo 'href="contest.php?page_id='.($page_id+1).'"';
            ?>><?php echo _('Next')?> <i class="fa fa-fw fa-angle-right"></i></a>
            <?php }else{?>
            <a class="pager-pre-link shortcut-hint" title="Alt+D" <?php
              if(mysqli_num_rows($result)==100) echo 'href="contest.php?level='.$level.'&page_id='.($page_id+1).'"';
            ?>><?php echo _('Next')?> <i class="fa fa-fw fa-angle-right"></i></a>
            <?php }?>
          </li>
        </ul>
      </div>
      <hr>
      <footer>
      <p>&copy; <?php echo"{$year} {$oj_copy}";?></p>
      </footer>
    </div>
    <script src="/assets/js/common.js?v=<?php echo $web_ver?>"></script>
    <script type="text/javascript"> 
      $(document).ready(function(){
        change_type(2);
        var cur_page=<?php echo $page_id ?>;
        $('#nav_cont').parent().addClass('active');
		$('#nav_cont_text').removeClass("hidden-sm");
        $('#contest_table').click(function(E){
          var $target = $(E.target);
          if($target.is('i.save_problem')){
            var pid = $target.attr('data-pid');
            var op;
            if($target.hasClass('fa-star'))
              op='rm_saved';
            else
              op='add_saved';
            $.get('ajax_mark.php?type=2&prob='+pid+'&op='+op,function(result){
              if(/success/.test(result)){
                $target.toggleClass('fa-star-o')
                $target.toggleClass('fa-star')
              }
            });
          }
        });
      });
    </script>
  </body>
</html>