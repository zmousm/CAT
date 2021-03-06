<?php
/* * *********************************************************************************
 * (c) 2011-15 GÉANT on behalf of the GN3, GN3plus and GN4 consortia
 * License: see the LICENSE file in the root directory
 * ********************************************************************************* */
?>
<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . "/config/_config.php");

require_once("auth.inc.php");
require_once("IdP.php");
require_once("Helper.php");
require_once("CAT.php");
require_once("Logging.php");
require_once("UserManagement.php");

require_once("common.inc.php");
require_once("input_validation.inc.php");

authenticate();

$Cat = new CAT();
$Cat->set_locale("web_admin");

header("Content-Type:text/html;charset=utf-8");

// if we have a pushed close button, submit attributes and send user back to the overview page
// if external DB sync is disabled globally, the user never gets to this page. If he came here *anyway* -> send him back immediately.
if ((isset($_POST['submitbutton']) && $_POST['submitbutton'] == BUTTON_CLOSE ) || CONFIG['DB']['enforce-external-sync'] == FALSE)
    header("Location: ../overview_federation.php");

// if not, must operate on a proper IdP
$my_inst = valid_IdP($_GET['inst_id']);
$user = new User($_SESSION['user']);
$mgmt = new UserManagement();

// DB link administrationis only permitted by federation operator himself
$isFedAdmin = $user->isFederationAdmin($my_inst->federation);

// if not, send the user away

if (!$isFedAdmin) {
    echo sprintf(_("You do not have the necessary privileges to manage the %s DB link state of this institution."), CONFIG['CONSORTIUM']['name']);
    exit(1);
}

// okay... we are indeed entitled to "do stuff"
// make a link if the admin has submitted the required info

if (isset($_POST['submitbutton']) && $_POST['submitbutton'] == BUTTON_SAVE) {
    // someone clever pushed the button without selecting an inst?
    if (!isset($_POST['inst_link']))
        header("Location: ../overview_federation.php");
    // okay, he did sumbit an inst. It's either a (string) handle from a promising 
    // candidate, or "other" as selected from the drop-down list
    if ($_POST['inst_link'] != "other") {
        $my_inst->setExternalDBId(valid_string_db($_POST['inst_link']));
    } else if (isset($_POST['inst_link_other'])) {
        $my_inst->setExternalDBId(valid_string_db($_POST['inst_link_other']));
    }
    header("Location: ../overview_federation.php");
}
?>
<h1>
    <?php printf(_("%s Database Link Status for IdP '%s'"), CONFIG['CONSORTIUM']['name'], $my_inst->name); ?>
</h1>
<hr/>
<p>
    <?php
    if ($my_inst->getExternalDBSyncState() == EXTERNAL_DB_SYNCSTATE_SYNCED) {

        printf(_("This institution is linked to the %s database."), CONFIG['CONSORTIUM']['name']) . "</p>";
        echo "<p>" . sprintf(_("The following information about the IdP is stored in the %s DB and %s DB:"), CONFIG['APPEARANCE']['productname'], CONFIG['CONSORTIUM']['name']) . "</p>";
        echo "<table><tr><td>" . sprintf(_("Information in <strong>%s Database</strong>"), CONFIG['APPEARANCE']['productname']) . "</td><td>" . sprintf(_("Information in <strong>%s Database</strong>"), CONFIG['CONSORTIUM']['name']) . "</td></tr>";
        echo "<tr><td>";
        // left-hand side: CAT DB
        echo "<table>";
        $names = $my_inst->getAttributes("general:instname");

        foreach ($names as $name) {
            $thename = unserialize($name['value']);
            if ($thename['lang'] == "C")
                $language = "default/other";
            else
                $language = CONFIG['LANGUAGES'][$thename['lang']]['display'];

            echo "<tr><td>" . sprintf(_("Institution Name (%s)"), $language) . "</td><td>" . $thename['content'] . "</td></tr>";
        }

        $admins = $my_inst->owner();

        foreach ($admins as $admin) {
            $user = new User($admin['ID']);
            $username = $user->getAttributes("user:realname");
            if (count($username) == 0)
                $username[0]['value'] = _("Unnamed User");
            echo "<tr><td>" . _("Administrator [invited as]") . "</td><td>" . $username[0]['value'] . " [" . $admin['MAIL'] . "]</td></tr>";
        }
        echo "</table>";
        // end of left-hand side
        echo "</td><td>";
        // right-hand side: external DB
        $externalid = $my_inst->getExternalDBId();
        if (!$externalid) { // we are in SYNCED state so this cannot happen
            throw new Exception("We are in SYNCSTATE_SYNCED but still there is no external DB Id available for the institution!");
        }

        $extinfo = Federation::getExternalDBEntityDetails($externalid);

        echo "<table>";
        foreach ($extinfo['names'] as $lang => $name)
            echo "<tr><td>" . sprintf(_("Institution Name (%s)"), $lang) . "</td><td>$name</td>";
        foreach ($extinfo['admins'] as $number => $admin_details)
            echo "<tr><td>" . _("Administrator email") . "</td><td>" . $admin_details['email'] . "</td></tr>";
        echo "</table>";
        // end of right-hand side
        echo "</td></tr></table>";
    } else if ($my_inst->getExternalDBSyncState() == EXTERNAL_DB_SYNCSTATE_NOT_SYNCED) {
        $temparray = [];
        printf(_("This institution is not yet linked to the %s database."), CONFIG['CONSORTIUM']['name']) . " ";
        echo "<strong>" . _("This means that its profiles are not made available on the user download page.") . "</strong> ";
        printf(_("You can link it to the %s database below."), CONFIG['CONSORTIUM']['name'], CONFIG['CONSORTIUM']['name']);
        $candidates = $my_inst->getExternalDBSyncCandidates();
        echo "<br/><form name='form-link-inst' action='inc/manageDBLink.inc.php?inst_id=$my_inst->identifier' method='post' accept-charset='UTF-8'>";
        printf(_("Please select an entity from the %s DB which corresponds to this CAT institution."), CONFIG['CONSORTIUM']['name']) . " ";
        if ($candidates !== FALSE)
            printf(_("Particularly promising entries (names in CAT and %s DB are a 100%% match) are on top of the list."), CONFIG['CONSORTIUM']['name']);
        echo "<table>";
        echo "<tr><th>" . _("Link to this entity?") . "</th><th>" . _("Name of the institution") . "</th><th>" . _("Administrators") . "</th></tr>";
        if ($candidates !== FALSE) {
            foreach ($candidates as $candidate) {
                $info = Federation::getExternalDBEntityDetails($candidate);
                echo "<tr><td><input type='radio' name='inst_link' value='$candidate' onclick='document.getElementById(\"submit\").disabled = false;'>$candidate</input></td><td>";
                foreach ($info['names'] as $lang => $name)
                    echo "[$lang] $name<br/>";
                echo "</td><td>";
                foreach ($info['admins'] as $number => $admin_details)
                    echo "[E-Mail] " . $admin_details['email'] . "<br/>";
                echo "</td></tr>";
                $temparray[] = $candidate;
            }
        }
        // we might have been wrong in our guess...
        $fed = new Federation(strtoupper($my_inst->federation));
        $unmappedentities = $fed->listExternalEntities(TRUE);
        // only display the "other" options if there is at least one
        $buffer = "";

        foreach ($unmappedentities as $v) {
            $buffer .= "<option value='" . $v['ID'] . "'>[ID " . $v['ID'] . "] " . $v['name'] . "</option>";
        }

        if ($buffer != "") {
            echo "<tr><td><input type='radio' name='inst_link' id='radio-inst-other' value='other'>Other:</input></td>";
            echo "<td><select id='inst_link_other' name='inst_link_other' onchange='document.getElementById(\"radio-inst-other\").checked=true; document.getElementById(\"submit\").disabled = false;'>";
            echo $buffer;
            echo "</select></td></tr>";
        }
        // issue a big red warning if there are no link candidates at all in the federation
        if (empty($buffer) && empty($candidates))
            echo "<tr><td style='color:#ff0000' colspan='2'>There are no unmapped institutions in the external database for this federation!</td></tr>";
        echo "</table><button type='submit' name='submitbutton' id='submit' value='" . BUTTON_SAVE . "' disabled >" . _("Create Link") . "</button></form>";
    }
    ?>
</p>
<br/>
<?php
$pending_invites = $mgmt->listPendingInvitations($my_inst->identifier);
$loggerInstance = new Logging();
$loggerInstance->debug(4, "Displaying pending invitations for $my_inst->identifier.\n");
if (count($pending_invites) > 0) {
    echo "<strong>" . _("Pending invitations for this IdP") . "</strong>";
    echo "<table>";
    foreach ($pending_invites as $invitee)
        echo "<tr><td>" . $invitee['mail'] . "</td></tr>";
    echo "</table>";
}
?>
<br/>
<hr/>
<form action='inc/manageDBLink.inc.php?inst_id=<?php echo $my_inst->identifier; ?>' method='post' accept-charset='UTF-8'>
    <button type='submit' name='submitbutton' value='<?php echo BUTTON_CLOSE; ?>' onclick='removeMsgbox();
            return false'><?php echo _("Close"); ?></button>
</form>
