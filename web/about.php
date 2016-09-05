<?php
require 'inc/global.php';
bindtextdomain("about", "locale"); 
bind_textdomain_codeset("about", 'UTF-8');
require 'inc/ojsettings.php';
require 'inc/checklogin.php';
$inTitle=_('About');
$Title=$inTitle .' - '. $oj_name;
?>
<!DOCTYPE html>
<html>
  <?php require 'head.php';?>
  <body>
    <?php require 'page_header.php';?>  
          
    <div class="container about-page">
      <div class="row">
        <div class="col-xs-12" style="font-size:16px">
          <div class="page-header">
            <h2><?php echo _('About')?></h2>
          </div>
		  <div>
            <p><?php echo _('CWOJ is a Free, <a href="https://github.com/CDFLS/CWOJ">Open-source</a> Online Judge Solution designed for OI trainings, based on Bashu OnlineJudge. Started on 11/20/2015, we keep seeking a better OJ solution for individuals, schools and other educational organizations.')?></p>
		  </div>
          <hr>
          <div>
            <p><b><?php echo _('Version')?></b><p>
            <table class="table table-bordered table-condensed table-last-left-aligned">
              <tbody>
                <tr>
                  <td style="width:20%"><?php echo _('Web')?></td>
                  <td><?php echo $web_ver?></td>
                </tr>
                <tr>
                  <td><?php echo _('Daemon')?></td>
                  <td><?php echo $daemon_ver?></td>
                </tr>
              </tbody>
            </table>
            <p><b><?php echo _('CWOJ Team')?></b></p>
            <table class="table table-bordered table-condensed table-last-left-aligned">
              <tbody>
                <tr>
                  <td style="width:20%">jimmy19990</td>
                  <td><?php echo dgettext('about', 'Main Developer & Maintainer.')?></td>
                </tr>
                <tr>
                  <td>Void</td>
                  <td><?php echo dgettext('about', 'Main Content (Problems & Contests) Manager.')?></td>
                </tr>
                <tr>
                  <td>t123yh</td>
                  <td><?php echo dgettext('about', 'Reverse Proxy & Temporary Server Provider, CWOJ Daemon Developer.')?></td>
                </tr>
                <tr>
                  <td>dreamfly</td>
                  <td><?php echo dgettext('about', 'An exported Salted Fish.')?></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
	  </div>
      <div class="row">
        <div class="col-xs-12" style="font-size:16px">
          <div class="page-header">
            <h2><?php echo _('Q&As')?></h2>
          </div>
          <div>
            <p><h3><?php echo _('Q: What compiler are we using and what are the compile options?')?></h3></p>
            <p><?php echo _('A: We are using GNU GCC/G++ for C/C++, Free Pascal for Pascal. The compile options are: ')?></p>
            <table class="table table-striped table-bordered table-condensed table-last-left-aligned" id="tab_options">
              <tbody>
                <tr>
                  <td><samp>C++</samp></td>
                  <td><code>g++ -static -std=c++03 -fno-asm -s -Wall -O2 -lm -DONLINE_JUDGE</code></td>
                </tr>
                <tr>
                  <td><samp>C</samp></td>
                  <td><code>gcc -static -std=c99 -fno-asm -s -Wall -O2 -lm -DONLINE_JUDGE</code></td>
                </tr>
                <tr>
                  <td><samp>Pascal</samp></td>
                  <td><code>fpc -Xs -Sgic -O2 -dONLINE_JUDGE</code></td>
                </tr>
                <tr>
                  <td><samp>C++11</samp></td>
                  <td><code>g++ -static -std=c++11 -s -Wall -O2 -lm -DONLINE_JUDGE</code></td>
                </tr>
              </tbody>
            </table>
          </div>
          <hr>
          <div>
            <p><h3><?php echo _('Q: Is it necessary to write File Operation codes?')?></h3></p>
            <p><?php echo _('A: You can only use Standard Input/Output. File Operations are not allowed here, otherwise wish you a happy Runtime Error!')?>
          </div>
          <hr>
          <div>
            <p><h3><?php echo _('Q: What are the meanings of the judge\'s replies?')?></h3></p>
            <p><?php echo _('A: Check out the form below:')?></p>
            <table class="table table-condensed table-last-left-aligned" id="about_msg">
              <tbody>
                <tr>
                  <td><span class="label label-ac">Accepted</span></td>
                  <td><p><?php echo _('Congratulations! Your code has passed all the test cases.')?></p></td>
                </tr>
                <tr>
                  <td><span class="label label-ce">Compile Error</span></td>
                  <td><p><?php echo _('The compiler could not compile your program. Of course, warning messages are not error messages.')?></p></td>
                </tr>
                <tr>
                  <td><span class="label label-le">Memory Exceeded</span></td>
                  <td><p><?php echo _('Your code tried to use more memory than the problem limit. Try optimizing your algorithm.')?></p></td>
                </tr>
                <tr>
                  <td><span class="label label-re">Runtime Error</span></td>
                  <td><p><?php echo _('Your code failed during the execution (illegal file access, stack overflow, pointer reference out of range, floating point exception, divided by zero...).')?></p></td>
                </tr>
                <tr>
                  <td><span class="label label-le">Time Out</span></td>
                  <td><p><?php echo _('Your program consumed more time than the time limit. Try optimizing your algorithm.')?></p></td>
                </tr>
                <tr>
                  <td><span class="label label-wa">Wrong Answer</span></td>
                  <td><p><?php echo _('The code has failed to produce the desired output.')?></p></td>
                </tr>
                <tr>
                  <td><span class="label label-ve">Validator Error</span></td>
                  <td><p><?php echo _('The checker program has exhibited abnormal behavior while validating the output of your code. Please contact the administrators.')?></p></td>
                </tr>
              </tbody>
            </table>
          </div>
          <hr>
        </div>
      </div>
      <hr>
      <footer>
        <p>&copy; <?php echo"{$year} {$oj_copy}";?></p>
      </footer>
    </div>
    </div>
    <script src="/assets/js/common.js?v=<?php echo $web_ver?>"></script>
    <script type="text/javascript"> 
        $(document).ready(function(){
            $('#nav_about').parent().addClass('active');
        });
    </script>
  </body>
</html>