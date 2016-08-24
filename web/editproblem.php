<?php
require 'inc/global.php';
require 'inc/ojsettings.php';
require 'inc/checklogin.php';
require 'inc/privilege.php';

if(!check_priv(PRIV_PROBLEM))
    include '403.php';
else if(!isset($_SESSION['admin_tfa']) || !$_SESSION['admin_tfa']){
    $_SESSION['admin_retpage'] = $_SERVER['REQUEST_URI'];
    header("Location: admin_auth.php");
    exit();
}else{
    require 'inc/problem_flags.php';
    require 'inc/database.php';
    $level_max=(PROB_LEVEL_MASK>>PROB_LEVEL_SHIFT);
    if(!isset($_GET['problem_id'])){
        $p_type='add';
        $inTitle=_('New Problem');
        $prob_id=1000;
        $result=mysqli_query($con,'select max(problem_id) from problem');
        if(($row=mysqli_fetch_row($result)) && intval($row[0]))
            $prob_id=intval($row[0])+1;
    }else{
        $p_type='edit';
        $prob_id=intval($_GET['problem_id']);  
        $inTitle=_('Edit Problem')." #$prob_id";
        $query="select title,description,input,output,sample_input,sample_output,hint,source,case_time_limit,memory_limit,case_score,compare_way,has_tex from problem where problem_id=$prob_id";
        $result=mysqli_query($con,$query);
        $row=mysqli_fetch_row($result);
        if(!$row)
            $info=_('There\'s no such problem');
        else{ 
            switch($row[11] >> 16){
                case 0:
                    $way='tra';
                    break;
                case 1:
                    $way='float';
                    $prec=($row[11] & 65535);
                break;
                case 2:
                    $way='int';
                    break;
                case 3:
                    $way='spj';
                    break;
            }
        }
    
        $option_opensource=0;
        if($row[12]&PROB_DISABLE_OPENSOURCE)
            $option_opensource=2;
        else if($row[12]&PROB_SOLVED_OPENSOURCE)
            $option_opensource=1;
        $option_level=($row[12]&PROB_LEVEL_MASK)>>PROB_LEVEL_SHIFT;
        $option_hide=(($row[12]&PROB_IS_HIDE)?'checked':'');
    }

    $Title=$inTitle .' - '. $oj_name;
?>
<!DOCTYPE html>
<html>
  <?php require 'head.php'; ?>

  <body>
    <?php require 'page_header.php'; ?>
    <div class="container edit-page">
      <?php if(isset($info)){?>
        <div class="text-center none-text none-center">
          <p><i class="fa fa-meh-o fa-4x"></i></p>
          <p><b>Whoops</b><br>
          <?php echo $info?></p>
        </div>
      <?php }else{?>
	  <div class="collapse" id="showtools">
	    <p><button class="btn btn-primary" id="btn_show"><?php echo _('Show Toolbar')?><i class="fa fa-fw fa-angle-right"></i></button></p>
	  </div>
      <form action="#" method="post" id="edit_form" style="padding-top:10px">
        <input type="hidden" name="op" value="<?php echo $p_type?>">
		<input type="hidden" name="problem_id" value="<?php echo $prob_id?>">
        <div class="row">
          <div class="form-group col-xs-12 col-sm-9">
            <label><?php echo _('Title')?></label>
			<input type="text" class="form-control" name="title" id="input_title" value="<?php if($p_type=='edit') echo $row[0]?>">
          </div>
        </div>
        <div class="row">
          <div class="form-group col-xs-4 col-sm-3">
            <label><?php echo _('Time Limit (ms)')?></label>
			<input id="input_time" name="time" class="form-control" type="number" value="<?php if($p_type=='edit') echo $row[8]; else echo '1000'?>">
          </div>
		  <div class="form-group col-xs-4 col-sm-3">
            <label><?php echo _('Memory Limit (KB)')?></label>
			<input id="input_memory" name="memory" class="form-control" type="number" value="<?php if($p_type=='edit') echo $row[9]; else echo '65536'?>">
          </div>  
		  <div class="form-group col-xs-4 col-sm-3">
			<label><?php echo _('Case Score (Full: 100)')?></label>
			<input id="input_score" name="score" class="form-control" type="number" value="<?php if($p_type=='edit') echo $row[10]; else echo '10'?>">
		  </div>    
        </div>
        <div class="row">
          <div class="form-group col-xs-6 col-sm-4">
            <label><?php echo _('Comparison')?></label>
              <select class="form-control" name="compare" id="input_cmp">
                <option value="tra"><?php echo _('Traditional')?></option>
                <option value="int"><?php echo _('Integer')?></option>
                <option value="float"><?php echo _('Real Number')?></option>
                <option value="spj"><?php echo _('Special Judge')?></option>
              </select>
              <?php if($p_type=='edit'){?>
              <script>
                $('#input_cmp').val("<?php echo $way?>");
              </script>
              <?php }?>
              <span id="input_cmp_help" class="help-block"></span>
          </div>
          <div class="form-group col-xs-6 col-sm-4 collapse" id="div_cmp_pre">
            <label><?php echo _('Precision')?></label>
              <select name="precision" class="form-control" id="input_cmp_pre"></select>
          </div>
        </div>      
        <div class="row">
          <div class="form-group col-xs-6 col-sm-3"> 
			<label><?php echo _('Open Source to')?></label>
                <select class="form-control" name="option_open_source" id="option_open_source">
                  <option value="0"><?php echo _('Everyone')?></option>
                  <option value="1"><?php echo _('Solved Users')?></option>
                  <option value="2"><?php echo _('Nobody')?></option>
                </select>
				<?php if($p_type=='edit'){?>
				<script>
				  document.getElementById('option_open_source').selectedIndex="<?php echo $option_opensource?>"
                </script>
				<?php }?>
			</div>
			<div class="form-group col-xs-6 col-sm-3">
              <label><?php echo _('Level')?></label>
              <select class="form-control" name="option_level" id="option_level">
                <script>
                <?php if($p_type=='add'){?>
                  for(var i=0;i<=<?php echo $level_max?>;i++){
                    document.write('<option value="'+i+'">'+i+'</option>')
                  }
				  <?php }else{?>
				  for(var i=0;i<=<?php echo $level_max?>;i++){
                    if(i==<?php echo $option_level?>)
                      document.write('<option selected value="'+i+'">'+i+'</option>')
                    else
                      document.write('<option value="'+i+'">'+i+'</option>')
                  }
                <?php }?>
                </script>
              </select>
			</div>
            <div class="form-group col-xs-12 col-sm-3">
			  <label><?php echo _('Options')?></label>
              <div class="checkbox">
                <label>
                    <input <?php if($p_type=='edit') echo $option_hide?> type="checkbox" name="hide_prob"><?php echo _('Hide')?>
                </label>
              </div>  
		    </div>
        </div>
        <div class="row">
          <div class="form-group col-xs-12 col-sm-9">
              <label><?php echo _('Description')?></label>
              <textarea class="form-control col-xs-12" name="description" rows="13"><?php if($p_type=='edit') echo htmlspecialchars($row[1])?></textarea>
          </div>
        </div>       
        <div class="row">
          <div class="form-group col-xs-12 col-sm-9">
              <label><?php echo _('Input')?></label>
              <textarea class="form-control col-xs-12" name="input" rows="8"><?php if($p_type=='edit') echo htmlspecialchars($row[2])?></textarea>
          </div>
        </div>       
        <div class="row">
          <div class="form-group col-xs-12 col-sm-9">
              <label><?php echo _('Output')?></label>
              <textarea class="form-control col-xs-12" name="output" rows="8"><?php if($p_type=='edit') echo htmlspecialchars($row[3])?></textarea>
          </div>
        </div>
        <div class="row">
          <div class="form-group col-xs-12 col-sm-9">
              <label><?php echo _('Sample Input')?></label>
              <textarea class="form-control col-xs-12" name="sample_input" rows="8"><?php if($p_type=='edit') echo htmlspecialchars($row[4])?></textarea>
          </div>
        </div>
        <div class="row">
          <div class="form-group col-xs-12 col-sm-9">
              <label><?php echo _('Sample Output')?></label>
              <textarea class="form-control col-xs-12" name="sample_output" rows="8"><?php if($p_type=='edit') echo htmlspecialchars($row[5])?></textarea>
          </div>
        </div>
        <div class="row">
          <div class="form-group col-xs-12 col-sm-9">
              <label><?php echo _('Hints')?></label>
              <textarea class="form-control col-xs-12" name="hint" rows="8"><?php if($p_type=='edit') echo htmlspecialchars($row[6])?></textarea>
          </div>
        </div>
        <div class="row">
          <div class="form-group col-xs-12 col-sm-9">
              <label><?php echo _('Tags')?></label>
              <input class="form-control col-xs-12" type="text" name="source" value="<?php if($p_type=='edit') echo htmlspecialchars($row[7])?>">
          </div>
        </div>
        <div class="row">
          <div class="form-group col-xs-12 col-sm-9">
            <div class="alert alert-danger collapse" id="alert_error"></div> 
            <button type="submit" class="btn btn-primary"><?php echo _('Submit')?></button>
          </div>
        </div>
      </form>
      <?php } ?>
      <hr>
      <footer>
        <p>&copy; <?php echo"{$year} {$oj_copy}";?></p>
      </footer>
    </div>
    <div class="html-tools">
      <div class="panel panel-default" id="tools">
        <div class="panel-heading">
          <h3 class="panel-title"><i class="fa fa-fw fa-code"></i> <?php echo _('HTML Toolbar')?></h3>
        </div>
        <div class="panel-body">
          <table class="table table-responsive table-bordered table-condensed table-striped">
            <thead>
            <tr>
              <th><?php echo _('Function')?></th>
              <th><?php echo _('Code')?></th>
            </tr>
            </thead>
            <tbody>
              <tr>
                <td><button class="btn btn-default" id="tool_less"><?php echo _('Smaller than(&lt;)')?></button></td>
                <td>&amp;lt;</td>
              </tr>
              <tr>
                <td><button class="btn btn-default" id="tool_greater"><?php echo _('Greater than(&gt;)')?></button></td>
                <td>&amp;gt;</td>
              </tr>
              <tr>
                <td><button class="btn btn-default" id="tool_img"><?php echo _('Image')?></button></td>
                <td>&lt;img src=&quot;...&quot;&gt;</td>
              </tr>
              <tr>
                <td><button class="btn btn-default" id="tool_sup"><?php echo _('Superscript')?></button></td>
                <td>&lt;sup&gt;...&lt;/sup&gt;</td>
              </tr>
              <tr>
                <td><button class="btn btn-default" id="tool_sub"><?php echo _('Subscript')?></button></td>
                <td>&lt;sub&gt;...&lt;/sub&gt;</td>
              </tr>
              <tr>
                <td><button class="btn btn-default" id="tool_samp"><?php echo _('Monospace')?></button></td>
                <td>&lt;samp&gt;...&lt;/samp&gt;</td>
              </tr>
              <tr>
                <td><button class="btn btn-default" id="tool_inline"><?php echo _('Inline TeX')?></button></td>
                <td>[inline]...[/inline]</td>
              </tr>
              <tr>
                <td><button class="btn btn-default" id="tool_tex"><?php echo _('TeX')?></button></td>
                <td>[tex]...[/tex]</td>
              </tr>
            </tbody>
          </table>
          <div class="btn-group text-center" style="margin-top:10px">
            <button class="btn btn-success" id="btn_upload"><?php echo _('Upload Image')?></button>
            <button class="btn btn-primary" id="btn_hide"><?php echo _('Hide Toolbar')?><i class="fa fa-fw fa-angle-left"></i></button>
          </div>
        </div>
      </div>
    </div>
    <script src="/assets/js/common.js?v=<?php echo $web_ver?>"></script>
    <script type="text/javascript"> 
      $(document).ready(function(){
        var loffset=window.screenLeft+200;
        var toffset=window.screenTop+200;
        function show_help(way){
          if(way=='float'){
            $('#div_cmp_pre').show();
            $('#input_cmp_help').html('<?php echo _('Output can ONLY contain Real Numbers. Please select precision.')?>');
          }else{
            $('#div_cmp_pre').hide();
            if(way=='tra')
              $('#input_cmp_help').html('<?php echo _('Generic comparsion. Trailing space is ignored.')?>');
            else if(way=='int')
              $('#input_cmp_help').html('<?php echo _('Output can ONLY contain Integers.')?>');
            else if(way=='spj')
              $('#input_cmp_help').html('<?php echo _('Please ensure there exists a "spj.cpp" in your data folder.')?>');
          }
        }
        (function(){
          var option='';
          for(var i=0;i<10;i++){
            option+='<option value="'+i+'">'+i+'</option>';
          }
          $('#input_cmp_pre').html(option);
            show_help($('#input_cmp').val());
        })();
        $('#input_cmp').change(function(E){show_help($(E.target).val());});
		$('#btn_hide').click(function(){
          $('#tools').fadeOut();
		  $('#showtools').fadeIn();
        });
		$('#btn_show').click(function(){
          $('#tools').fadeIn();
		  $('#showtools').fadeOut();
        });
        $('#btn_upload').click(function(){
          window.open("upload.php",'upload_win2','left='+loffset+',top='+toffset+',width=400,height=300,toolbar=no,resizable=no,menubar=no,location=no,status=no');
        });
        $('#edit_form textarea').focus(function(e){cur=e.target;});
        $('#edit_form input').blur(function(e){
          e.target.value=$.trim(e.target.value);
          var o=$(e.target);
          if(!e.target.value)
            o.addClass('error');
          else
            o.removeClass('error');
        });
        $('#edit_form').submit(function(){
          var str=$('#input_title').val();
            if(!str||str==''){
            $('html, body').animate({scrollTop:0}, '200');
            return false;
          }
          str=$('#input_memory').val();
          if(!str||str==''){
            $('html, body').animate({scrollTop:0}, '200');
            return false;
          }
          str=$('#input_time').val();
          if(!str||str==''){
            $('html, body').animate({scrollTop:0}, '200');
            return false;
          }
          str=$('#input_score').val();
          if(!str||str==''){
            $('html, body').animate({scrollTop:0}, '200');
            return false;
		  }
		  $.ajax({
            type:"POST",
            url:"ajax_editproblem.php",
            data:$('#edit_form').serialize(),
            success:function(msg){
              if(/success/.test(msg)) window.location="problempage.php?problem_id=<?php echo $prob_id?>";
              else $('#alert_error').html('<i class="fa fa-fw fa-remove"></i> '+msg).slideDown();
            }
          });
          return false;
        });
        $('#tools').click(function(e){
          if(!($(e.target).is('button')))return false;
          if(typeof(cur)=='undefined')return false;
          var op=e.target.id;
          var slt=GetSelection(cur);
          if(op=="tool_greater")
            InsertString(cur,'&gt;');
          else if(op=="tool_less")
            InsertString(cur,'&lt;');
          else if(op=="tool_img"){
            var url=prompt('<?php echo _('Please enter the image link')?>',"");
            if(url){
              InsertString(cur,slt+'<img src="'+url+'">');
            }
          }else if(op=="tool_inline"||op=="tool_tex"){
            op=op.substr(5);
            InsertString(cur,'['+op+']'+slt+'[/'+op+']');
          }else if(op=="btn_upload"||op=="btn_hide"){
            return false;
          }else{
            op=op.substr(5);
            InsertString(cur,'<'+op+'>'+slt+'</'+op+'>');
          }
          return false;
        });
      });
    </script>
  </body>
</html>
<?php }?>