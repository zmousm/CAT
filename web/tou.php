<?php
/***********************************************************************************
 * (c) 2011-15 GÉANT on behalf of the GN3, GN3plus and GN4 consortia
 * License: see the LICENSE file in the root directory
 ***********************************************************************************/
?>
<?php
// error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
/**
 * This file contains the implementation of the simple CAT user interdace
 * 
 * @author Tomasz Wolniewicz <twoln@umk.pl>
 * 
 * @package UserGUI
 * 
 */

include(dirname(dirname(__FILE__)) . "/config/_config.php");
require_once("UserAPI.php");
require_once("Logging.php");
$loggerInstance = new Logging();
$loggerInstance->debug(4,"\n----------------------------------TOU.PHP------------------------\n");
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?php echo CAT::get_lang()?>">
    <head lang="<?php echo CAT::get_lang()?>"> 
        <title><?php echo CONFIG['APPEARANCE']['productname_long'];?></title>
<link media="only screen and (max-device-width: 480px)" href="<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']),'/') ?>/resources/css/cat-basic.css.php" type= "text/css" rel="stylesheet" />
<link media="only screen and (min-device-width: 481px)" href="<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') ?>/resources/css/cat-basic-large.css" type= "text/css" rel="stylesheet" />
        <meta charset="utf-8" /> 
<script type="text/javascript">
function showTOU(){
  document.getElementById('all_tou_link').style.display = 'none';
  document.getElementById('tou_2').style.display = 'block';

}
</script>

    </head>
    <body style="">
        <img src="<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') ?>/resources/images/consortium_logo.png" style="padding-right:20px; padding-top:20px; float:right" alt="logo" />
         <?php
            print '<div id="motd">'.( isset(CONFIG['APPEARANCE']['MOTD']) ? CONFIG['APPEARANCE']['MOTD'] : '&nbsp' ).'</div>'; 

        print '<h1><a href="' . dirname($_SERVER['SCRIPT_NAME']) . '?lang=' . CAT::get_lang() . '">' . CONFIG['APPEARANCE']['productname'] . '</a></h1>';
print '<div id="tou">';
include("user/tou.php");
print '</div>';

// this variable gets set during "make distribution" only
$RELEASE = "THERELEASE";
echo "".CONFIG['APPEARANCE']['productname']." - ";
if ($RELEASE != "THERELEASE") 
    echo sprintf(_("Release %s"), $RELEASE);
else
    echo _("Unreleased SVN Revision");
echo " &copy; 2011-13 DANTE Ltd. on behalf of the GN3 and GN3plus consortia</div>";?>
</body>
</html>
