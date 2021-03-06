<?php
/* * *********************************************************************************
 * (c) 2011-15 GÉANT on behalf of the GN3, GN3plus and GN4 consortia
 * License: see the LICENSE file in the root directory
 * ********************************************************************************* */
?>
<?php
require_once(dirname(dirname(dirname(__FILE__))) . "/config/_config.php");

require_once("Federation.php");
require_once("IdP.php");
require_once("ProfileRADIUS.php");
require_once("Helper.php");
require_once("CAT.php");

require_once("../resources/inc/header.php");
require_once("../resources/inc/footer.php");
require_once("inc/common.inc.php");
require_once("inc/input_validation.inc.php");
require_once("inc/option_html.inc.php");

$cat = defaultPagePrelude(sprintf(_("%s: IdP Enrollment Wizard (Step 3)"), CONFIG['APPEARANCE']['productname']));
?>
<script src="js/option_expand.js" type="text/javascript"></script>
<!-- JQuery --> 
<script type="text/javascript" src="../external/jquery/jquery.js"></script> 
<script type="text/javascript" src="../external/jquery/jquery-migrate-1.2.1.js"></script> 
<script type="text/javascript" src="../external/jquery/jquery-ui.js"></script> 
<!-- EAP sorting code -->
<style>
    li.eap1 {list-style-type: none;}
    ol.eapmethods { list-style-position: inside; margin: 0; padding: 0px; padding-top: 20px; padding-bottom: 0px; width: 20em; }
    ol.eapmethods li{
        background: #CCF;
        border-left-style: inset;
        border-left-width: 1px;
        border-left-color: #8BBACB;
        border-top-style: inset;
        border-top-width: 1px;
        border-top-color: #8BBACB;
        border-right-style: outset;
        border-right-width: 2px;
        border-right-color: #043D52;
        border-bottom-style: outset;
        border-bottom-width: 2px;
        border-bottom-color: #043D52;
        border-radius: 6px;
        box-shadow: 4px 4px 4px #888888;
        background-image:url('../resources/images/icons/strzalka5.png');
        background-repeat:no-repeat;
        background-position:95% 50%;
        margin: 2px 0px 2px 0px;
        padding: 3px;
        padding-left: 1em;
        padding-right: 0px;
    }

    table.eaptable td {
        background:#F0F0F0;
    }

    table.eaptable th {
        background:#F0F0F0;
    }

    #eap_bottom_row td {
        border-top-color: #888;
        border-top-style: solid;
        border-top-width: 2px;
    }

    #eap_bottom_row th {
        border-top-color: #888;
        border-top-style: solid;
        border-top-width: 2px;
    }

    #supported_eap {
        background: green;
        padding: 5px;
    }

    #unsupported_eap {
        background: red;
        padding: 5px;
    }

</style>
<script>
    $(function () {
        $("#sortable1, #sortable2").sortable({
            connectWith: "ol.eapmethods",
            tolerance: 'pointer',
            out: function (event, ui) {
                ui.item.toggleClass("eap1");
            },
            stop: function (event, ui) {
                $(".eapm").removeAttr('value');
                $(".eapmv").removeAttr('value');
                $("#sortable1").children().each(function (index) {
                    i = index + 1;
                    v = $(this).html();
                    $("#EAP-" + v).val(v);
                    $("#EAP-" + v + "-priority").val(i);
                });
            }
        }).disableSelection();
    });
</script>
<!-- EAP sorting code end -->
<?php
// initialize inputs
$my_inst = valid_IdP($_GET['inst_id'], $_SESSION['user']);
$anonLocal = "anonymous";
$useAnon = FALSE;
$checkuserOuter = FALSE;
$checkuserValue = "anonymous";
$verify = FALSE;
$hint = FALSE;
$realm = "";
$prefill_name = "";
$blacklisted = FALSE;

if (isset($_GET['profile_id'])) { // oh! We should edit an existing profile, not create a new one!
    $wizardStyle = FALSE;
    $my_profile = valid_Profile($_GET['profile_id'], $my_inst->identifier);
    if (!$my_profile instanceof ProfileRADIUS) {
        throw new Exception("This page is only for editing RADIUS profiles!");
    }

    $useAnon = $my_profile->getAttributes("internal:use_anon_outer");
    if (count($useAnon) > 0) {
        $useAnon = $useAnon[0]['value'];
        $anonLocal = $my_profile->getAttributes("internal:anon_local_value")[0]['value'];
    }

    $checkuserOuter = $my_profile->getAttributes("internal:checkuser_outer");
    if (count($checkuserOuter) > 0) {
        $checkuserOuter = $checkuserOuter[0]['value'];
        $checkuserValue = $my_profile->getAttributes("internal:checkuser_value")[0]['value'];
    }

    $verify = $my_profile->getAttributes("internal:verify_userinput_suffix")[0]['value'];
    $hint = $my_profile->getAttributes("internal:hint_userinput_suffix")[0]['value'];
    $realm = $my_profile->getAttributes("internal:realm")[0]['value'];

    $prefill_name = $my_profile->name;
    $prefill_methods = $my_profile->getEapMethodsinOrderOfPreference();
    $profile_options = $my_profile->getAttributes();
    // is there a general redirect? it is one which have device = 0
    $blacklistedDevices = $my_profile->getAttributes("device-specific:redirect");
    $blacklisted = FALSE;
    foreach ($blacklistedDevices as $oneDevice) {
        if ($oneDevice['device'] == NULL) {
            $blacklistedArray = unserialize($oneDevice['value']);
            $blacklisted = $blacklistedArray['content'];
        }
    }
} else {
    $wizardStyle = TRUE;
    $my_profile = NULL;
    $prefill_methods = [];
    $profile_options = [];
}

?>
</head>
<body>
    <?php
    productheader("ADMIN-IDP", CAT::get_lang());
    ?>
    <h1>
        <?php
        if ($wizardStyle) {
            echo _("Step 3: Defining a user group profile");
        }
        else {
            printf(_("Edit profile '%s' ..."), $prefill_name);
        }
        ?>
    </h1>
    <?php echo instLevelInfoBoxes($my_inst);?>
    
    echo "<form enctype='multipart/form-data' action='edit_profile_result.php?inst_id=$my_inst->identifier" . ($edit_mode ? "&amp;profile_id=" . $my_profile->identifier : "") . "' method='post' accept-charset='UTF-8'>
                <input type='hidden' name='MAX_FILE_SIZE' value='" . CONFIG['MAX']_UPLOAD_SIZE . "'>";
    ?>
    <fieldset class="option_container">
        <legend>
            <strong><?php echo _("General Profile properties"); ?></strong>
        </legend>
        <?php
        if ($wizardStyle) {
            echo "<p>" . _("We will now define a profile for your user group(s).  You can add as many profiles as you like by choosing the appropriate button on the end of the page. After we are done, the wizard is finished and you will be taken to the main IdP administration page.") . "</p>";
        }
        ?>
        <h3><?php echo _("Profile Name and RADIUS realm"); ?></h3>
        <?php
        if ($wizardStyle) {
            echo "<p>" . _("First of all we need a name for the profile. This will be displayed to end users, so you may want to choose a descriptive name like 'Professors', 'Students of the Faculty of Bioscience', etc.") . "</p>";
            echo "<p>" . _("Optionally, you can provide a longer descriptive text about who this profile is for. If you specify it, it will be displayed on the download page after the user has selected the profile name in the list.") . "</p>";
            echo "<p>" . _("You can also tell us your RADIUS realm. ");
            if (count(CONFIG['RADIUSTESTS']['UDP-hosts']) > 0 || CONFIG['RADIUSTESTS']['TLS-discoverytag'] != "") {
                printf(_("This is useful if you want to use the sanity check module later, which tests reachability of your realm in the %s infrastructure. "), CONFIG['CONSORTIUM']['name']);
            }
            echo _("It is required to enter the realm name if you want to support anonymous outer identities (see below).") . "</p>";
        }

        echo prefilledOptionTable($profile_options, "expandable_profile_options", "profile", "Profile");
        ?>
        <button type='button' class='newoption' onclick='addDefaultProfileOptions()'><?php echo _("Add new option"); ?></button>
        <table>
            <?php
            ?>
            <tr>

                <td>
                    <label for="realm">
                        <?php echo _("Realm:"); ?>
                    </label>
                </td>
                <td>
                    <?php echo "<input id='realm' name='realm' value='$realm' onkeyup='
                                 if (this.value.length > 0)
                                      { this.form.elements[\"anon_support\"].removeAttribute(\"disabled\");
                                        document.getElementById(\"anon_support_label\").removeAttribute(\"style\");
                                        
                                        this.form.elements[\"checkuser_support\"].removeAttribute(\"disabled\");
                                        document.getElementById(\"checkuser_label\").removeAttribute(\"style\");
                                        
                                        this.form.elements[\"verify_support\"].removeAttribute(\"disabled\");
                                        
                                        document.getElementById(\"verify_label\").removeAttribute(\"style\");
                                        document.getElementById(\"hint_label\").removeAttribute(\"style\");

                                      } else
                                      { this.form.elements[\"anon_support\"].checked = false;
                                        this.form.elements[\"anon_support\"].setAttribute(\"disabled\", \"disabled\");
                                        this.form.elements[\"anon_local\"].setAttribute(\"disabled\", \"disabled\");
                                        document.getElementById(\"anon_support_label\").setAttribute(\"style\", \"color:#999999\");
                                        
                                        this.form.elements[\"checkuser_support\"].checked = false;
                                        this.form.elements[\"checkuser_support\"].setAttribute(\"disabled\", \"disabled\");
                                        this.form.elements[\"checkuser_local\"].setAttribute(\"disabled\", \"disabled\");
                                        document.getElementById(\"checkuser_label\").setAttribute(\"style\", \"color:#999999\");
                                        
                                        this.form.elements[\"verify_support\"].checked = false;
                                        this.form.elements[\"verify_support\"].setAttribute(\"disabled\", \"disabled\");
                                        
                                        this.form.elements[\"hint_support\"].checked = false;
                                        this.form.elements[\"hint_support\"].setAttribute(\"disabled\", \"disabled\");
                                        
                                        document.getElementById(\"verify_label\").setAttribute(\"style\", \"color:#999999\");
                                        document.getElementById(\"hint_label\").setAttribute(\"style\", \"color:#999999\");
                                      };'/>"; ?>

                </td>

            </tr>

        </table>
        <h3><?php echo _("Realm Options"); ?></h3>

        <?php
        if ($wizardStyle) {
            echo "<p>" . sprintf(_("Some installers support a feature called 'Anonymous outer identity'. If you don't know what this is, please read <a href='%s'>this article</a>."), "https://confluence.terena.org/display/H2eduroam/eap-types") . "</p>";
            echo "<p>" . _("On some platforms, the installers can suggest username endings and/or verify the user input to contain the realm suffix (sub-realms will pass this validation).") . "</p>";
            echo "<p>" . _("The realm check feature needs to know an outer ID which actually gets a chance to authenticate. If your RADIUS server lets only select usernames pass, it is useful to supply the inforamtion which of those (outer ID) username we can use for testing.") . "</p>";
        }
        ?>
        <p>


            <!-- UI table to align elements-->
        <table>
            <tr>
                <!-- checkbox for "verify-->
                <td>
                    <span id='verify_label' style='<?php echo ($realm == "" ? "color:#999999" : "" ); ?>'>
                        <?php echo _("Verify user input to contain realm suffix:"); ?>
                    </span>
                </td>
                <td>
                    <input type='checkbox' <?php
                        echo ($verify != FALSE ? "checked" : "" );
                        echo ($realm == "" ? "disabled" : "" );
                        ?> name='verify_support' onclick='
                                if (this.form.elements["verify_support"].checked !== true) {
                                    this.form.elements["hint_support"].setAttribute("disabled", "disabled");
                                } else {
                                    this.form.elements["hint_support"].removeAttribute("disabled");
                                }
                                ;'/>
                    <span id='hint_label' style='<?php echo ($realm == "" ? "color:#999999" : "" ); ?>'>
                        <?php echo _("Prefill user input with realm suffix:"); ?>
                    </span>
                    <input type='checkbox' <?php echo ($verify == FALSE ? "disabled" : "" ); ?> name='hint_support' <?php echo ( $hint != FALSE ? "checked" : "" ); ?> />
                </td>
            </tr>
            <tr>

                <!-- checkbox and input field for anonymity support, available only when realm is known-->
                <td>
                    <span id='anon_support_label' style='<?php echo ($realm == "" ? "color:#999999" : "" ); ?>'>
                        <?php echo _("Enable Anonymous Outer Identity:"); ?>
                    </span>
                </td>
                <td>
                    <input type='checkbox' <?php echo ($useAnon != FALSE ? "checked" : "" ) . ($realm == "" ? " disabled" : "" ); ?> name='anon_support' onclick='
                            if (this.form.elements["anon_support"].checked !== true) {
                                this.form.elements["anon_local"].setAttribute("disabled", "disabled");
                            } else {
                                this.form.elements["anon_local"].removeAttribute("disabled");
                            }
                            ;'/>
                    <input type='text' <?php echo ($checkuserOuter == FALSE ? "disabled" : "" ); ?> name='anon_local' value='<?php echo $anonLocal;?>'/>
                </td>    
            </tr>
            <tr>

                <!-- checkbox and input field for check realm outer id, available only when realm is known-->
                <td>
                    <span id='checkuser_label' style='<?php echo ($realm == "" ? "color:#999999" : "" ); ?>'>
                        <?php echo _("Use special Outer Identity for realm checks:"); ?>
                    </span>
                </td>
                <td>
                    <input type='checkbox' <?php echo ($checkuserOuter != FALSE ? "checked" : "" ) . ($realm == "" ? " disabled" : "" ); ?> name='checkuser_support' onclick='
                            if (this.form.elements["checkuser_support"].checked !== true) {
                                this.form.elements["checkuser_local"].setAttribute("disabled", "disabled");
                            } else {
                                this.form.elements["checkuser_local"].removeAttribute("disabled");
                            }
                            ;'/>
                    <input type='text' <?php echo ($checkuserOuter == FALSE ? "disabled" : "" ); ?> name='checkuser_local' value='<?php echo $checkuserValue;?>'/>
                </td>
            </tr>
        </table>
    </p>

    <h3><?php echo _("Installer Download Location"); ?></h3>

    <?php
    if ($wizardStyle) {
        echo "<p>" . _("The CAT has a download area for end users. There, they will, for example, learn about the support pointers you entered earlier. The CAT can also immediately offer the installers for the profile for download. If you don't want that, you can instead enter a web site location where you want your users to be redirected to. You, as the administrator, can still download the profiles to place them on that page (see the 'Compatibility Matrix' button on the dashboard).") . "</p>";
    }
    ?>
    <p>

        <?php
        echo "<span id='redirect_label' style='" . ($realm == "" ? "color:#999999" : "" ) . "'><label for='redirect'>" . _("Redirect end users to own web page:") . "</label></span>
                          <input type='checkbox'  name='redirect' id='redirect' " . ($blacklisted === FALSE ? "" : "checked " ) . "onclick='
                              if (this.form.elements[\"redirect\"].checked != true) {
                                this.form.elements[\"redirect_target\"].setAttribute(\"disabled\", \"disabled\");
                              } else {
                                this.form.elements[\"redirect_target\"].removeAttribute(\"disabled\");
                              };'/>
                          <input type='text' name='redirect_target' " . ($blacklisted !== FALSE ? "value='$blacklisted'" : "disabled" ) . "/>";
        ?>
    </p>

</fieldset>
<fieldset class="option_container">
    <legend><strong><?php echo _("Supported EAP types"); ?></strong></legend>
    <?php
    if ($wizardStyle) {
        echo "<p>" . _("Now, we need to know which EAP types your IdP supports. If you support multiple EAP types, you can assign every type a priority (1=highest). This tool will always generate an automatic installer for the EAP type with the highest priority; only if the user's device can't use that EAP type, we will use an EAP type further down in the list.") . "</p>";
    }
    ?>
    <?php

    function priority($eapType, $isenabled, $priority) {
        echo "<td><select id='$eapType-priority' name='$eapType-priority' " . (!$isenabled ? "disabled='disabled'" : "") . ">";
        for ($a = 1; $a < 7; $a = $a + 1) {
            echo "<option id='$eapType-$a' value='$a' " . ( $isenabled && $a == $priority ? "selected" : "" ) . ">$a</option>";
        }
        echo "</select></td>";
    }

    function inherited_options($idpwideoptions, $eapType, $isVisible) {
        echo "<td><div style='" . (!$isVisible ? "visibility:hidden" : "") . "' class='inheritedoptions' id='$eapType-inherited-global'>";

        $eapoptions = [];

        foreach ($idpwideoptions as $option) {
            if ($option['level'] == "IdP" && preg_match('/^eap/', $option['name'])) {
                $eapoptions[] = $option['name'];
            }
        }

        $eapoptionsNames = array_count_values($eapoptions);

        if (count($eapoptionsNames) > 0) {
            echo "<strong>" . _("EAP options inherited from Global level:") . "</strong><br />";
            foreach ($eapoptionsNames as $optionname => $count) {
            /// option count and enumeration
            /// Example: "(3x) Server Name"
                printf(_("(%dx) %s") . "<br />", $count, display_name($optionname));
            }
        }

        echo "</div></td>";
    }

    $methods = EAP::listKnownEAPTypes();
    ?>

    <?php
// new EAP sorting code  

    foreach ($methods as $a) {
        $display = display_name($a);
        $enabled = FALSE;
            foreach ($prefill_methods as $prio => $value) {
                if (display_name($a) == display_name($value)) {
                    $enabled = TRUE;
                    $countactive = $prio + 1;
                }
            }
    }
    ?>
    <div>
        <table style="border:none">
            <tr>
                <th style="vertical-align:top; padding:1em">
<?php echo _('Supported EAP types for this profile'); ?>
                </th>
                <td id="supported_eap">
                    <ol id="sortable1" class="eapmethods">
                        <?php
                        $D = [];
                        foreach ($prefill_methods as $prio => $value) {
                            print '<li>' . display_name($value) . "</li>\n";
                            $D[display_name($value)] = $prio;
                        }
                        ?>
                    </ol>
                </td>
                <td rowspan=3 style="text-align:center; width:12em; padding:1em">
<?php echo _('Use "drag &amp; drop" to mark an EAP method and move it to the supported (green) area. Prioritisation is done automatically, depending on where you "drop" the method.'); ?>
                </td>
            </tr>
            <tr id="eap_bottom_row">
                <td colspan="2"> </td>
            </tr>
            <tr>
                <th style="vertical-align:top; padding:1em">
<?php echo _('Unsupported EAP types'); ?>
                </th>
                <td style="vertical-align:top" id="unsupported_eap">
                    <ol id="sortable2" class="eapmethods">
                        <?php
                        foreach ($methods as $a) {
                            $display = display_name($a);
                            if (!isset($D[display_name($a)])) {
                                print '<li class="eap1">' . display_name($a) . "</li>\n";
                            }
                        }
                        ?>
                    </ol>
                </td>
            </tr>
        </table>
    </div>
    <?php
    foreach ($methods as $a) {
        $display = display_name($a);
        $v = isset($D[$display]) ? $D[$display] : '';
        print '<input type="hidden" class="eapm" name="' . $display . '" id="EAP-' . $display . '" value="' . $display . '">';
        print '<input type="hidden" class="eapmv" name="' . $display . '-priority" id="EAP-' . $display . '-priority" value="' . $v . '">';
    }
    ?>
    <br style="clear:both;" />
</fieldset>
<fieldset class="option_container" id="helpdesk_override">
    <legend><strong><?php echo _("Helpdesk Details for this profile"); ?></strong></legend>
    <p>
        <?php
        $idp_options = $my_inst->getAttributes();
        $has_support_options = [];
        foreach ($idp_options as $idp_option) {
            if (preg_match("/^support:/", $idp_option['name'])) {
                $has_support_options[$idp_option['name']] = "SET";
            }
        }
        if (count($has_support_options) > 0) {
            $text = "<ul>";
            foreach ($has_support_options as $key => $value) {
                $text .= "<li><strong>" . display_name($key) . "</strong></li>";
            }
            $text .= "</ul>";
            printf(ngettext("The option %s is already defined IdP-wide. If you set it here on profile level, this setting will override the IdP-wide one.", "The options %s are already defined IdP-wide. If you set them here on profile level, these settings will override the IdP-wide ones.", count($has_support_options)), $text);
        }
        ?>
    </p>
    <?php
    echo prefilledOptionTable($profile_options, "expandable_support_options", "support", "Profile");
    ?>
    <button type='button' class='newoption' onclick='addDefaultSupportOptions()'><?php echo _("Add new option"); ?></button>
</fieldset>
<fieldset class="option_container" id="eap_override">
    <legend><strong><?php echo _("EAP Details for this profile"); ?></strong></legend>
    <p>
        <?php
        $has_eap_options = [];
        foreach ($idp_options as $idp_option) {
            if (preg_match("/^eap:/", $idp_option['name'])) {
                $has_eap_options[$idp_option['name']] = "SET";
            }
        }
        if (count($has_eap_options) > 0) {
            $text = "<ul>";
            foreach ($has_eap_options as $key => $value) {
                $text .= "<li><strong>" . display_name($key) . "</strong></li>";
            }
            $text .= "</ul>";
            printf(ngettext("The option %s is already defined IdP-wide. If you set it here on profile level, this setting will override the IdP-wide one.", "The options %s are already defined IdP-wide. If you set them here on profile level, these settings will override the IdP-wide ones.", count($has_eap_options)), $text);
        }
        ?>
    </p>
    <?php
    echo prefilledOptionTable($profile_options, "expandable_eapserver_options", "eap", "Profile");
    ?>
    <button type='button' class='newoption' onclick='addDefaultEapServerOptions()'><?php echo _("Add new option"); ?></button>
</fieldset>
<fieldset class="option_container" id='media_override'>
    <legend><strong><?php echo _("Media Properties for this profile"); ?></strong></legend>
    <p>
        <?php
        $idp_options = $my_inst->getAttributes();
        $has_support_options = [];
        foreach ($idp_options as $idp_option) {
            if (preg_match("/^media:/", $idp_option['name'])) {
                $has_support_options[$idp_option['name']] = "SET";
            }
        }
        if (count($has_support_options) > 0) {
            $text = "<ul>";
            foreach ($has_support_options as $key => $value) {
                $text .= "<li><strong>" . display_name($key) . "</strong></li>";
            }
            $text .= "</ul>";
            printf(ngettext("The option %s is already defined IdP-wide. If you set it here on profile level, this setting will override the IdP-wide one.", "The options %s are already defined IdP-wide. If you set them here on profile level, these settings will override the IdP-wide ones.", count($has_support_options)), $text);
        }
        ?>
    </p>
    <?php
    echo prefilledOptionTable($profile_options, "expandable_media_options", "media", "Profile");
    ?>
    <button type='button' class='newoption' onclick='addDefaultMediaOptions()'><?php echo _("Add new option"); ?></button></fieldset>
<?php
if ($wizardStyle) {
    echo "<p>" . _("When you are sure that everything is correct, please click on 'Save data' and you will be taken to your IdP Dashboard page.") . "</p>";
}
echo "<p><button type='submit' name='submitbutton' value='" . BUTTON_SAVE . "'>" . _("Save data") . "</button><button type='button' class='delete' name='abortbutton' value='abort' onclick='javascript:window.location = \"overview_idp.php?inst_id=$my_inst->identifier\"'>" . _("Discard changes") . "</button></p></form>";
footer();
