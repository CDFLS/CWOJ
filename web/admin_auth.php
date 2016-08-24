<?php
require 'inc/global.php';
require 'inc/ojsettings.php';
require 'inc/checklogin.php';
require 'inc/privilege.php';

if(!check_priv(PRIV_PROBLEM) && !check_priv(PRIV_SYSTEM))
  include '403.php';
else{
require 'inc/database.php';
if(isset($_POST['paswd'])){

  if(!function_exists('my_rsa'))
	require 'inc/checkpwd.php';
  if(password_right($_SESSION['user'], $_POST['paswd'])){
    $_SESSION['admin_tfa']=1;
    if(isset($_SESSION['admin_retpage'])){
      $ret = $_SESSION['admin_retpage'];
	}
    else
      $ret = "index.php";
	unset($_SESSION['admin_retpage']);  
    header("Location: $ret");
    exit(0);
  }
}

$inTitle=_('Admin Verification');
$Title=$inTitle .' - '. $oj_name;
?>
<!DOCTYPE html>
<html>
  <?php require 'head.php'; ?>
  <body>
    <?php require 'page_header.php'; ?>  
          
    <div class="container admin-page">
      <div class="row">
        <div class="col-xs-12">
          <form class="form-inline text-center" method="post">
            <div class="form-group">
              <div class="input-group">
               <input type="password" class="form-control" autofoucs id="input_adminpass" name="paswd" placeholder="<?php echo _('Your pasword...')?>">
               <span class="input-group-btn">
                  <button type="submit" class="btn btn-default"><?php echo _('Go')?></button>
               </span>
              </div>
			</div>
          </form>
        </div>
      </div>
      <hr>
      <footer>
        <p>&copy; <?php echo"{$year} {$oj_copy}";?></p>
      </footer>
    </div>
    <script src="/assets/js/common.js?v=<?php echo $web_ver?>"></script>
    <script type="text/javascript">
      $(document).ready(function(){
        $('#input_adminpass').focus();
      });
    </script>
  </body>
</html>
<?php }?>