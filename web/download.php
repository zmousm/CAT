<?php
/***********************************************************************************
 * (c) 2011-15 GÉANT on behalf of the GN3, GN3plus and GN4 consortia
 * License: see the LICENSE file in the root directory
 ***********************************************************************************/
?>
<?php 
/**
 * Download front-end for the user GUI
 * This file is obsolete and left for backwards compatibility reasons only
 *
 * @author Tomasz Wolniewicz <twoln@umk.pl>
 * @package UserGUI
 */

include(dirname(dirname(__FILE__))."/config/_config.php");
require_once("UserAPI.php");
require_once("Logging.php");
require_once('ProfileFactory.php');
$Gui = new UserAPI();
$loggerInstance = new Logging();


$profile_id = $_REQUEST['profile'];
$inst_id = $_REQUEST['idp'];
$device = $_REQUEST['device'];
$generated_for = $_REQUEST['generatedfor'];

$Gui->set_locale('devices');


if ($generated_for != "admin" && $generated_for != "user") {
    $loggerInstance->debug(2,"Invalid downloads triggered (neither for admin nor user???)");
    print("Invalid downloads triggered (neither for admin nor user???)");
    exit(1);
}

//print_r($_REQUEST);

$loggerInstance->debug(4,"download: profile:$profile_id; inst:$inst_id; device:$device\n");

// first block will test if the user input was valid.

$p = ProfileFactory::instantiate($profile_id);

if(!$p->institution || $p->institution !== $inst_id) {
  header("HTTP/1.0 404 Not Found");
  return;
}

// now we generate the installer

$Gui->downloadInstaller($device,$profile_id, $generated_for);