<?php

/* * *********************************************************************************
 * (c) 2011-15 GÉANT on behalf of the GN3, GN3plus and GN4 consortia
 * License: see the LICENSE file in the root directory
 * ********************************************************************************* */
?>
<?php

/**
 * This is the main (and currently: only) configuration file for CAT
 *
 * @package Configuration
 */

/**
 * This classes' members hold the configuration for CAT
 *
 * @author Stefan Winter <stefan.winter@restena.lu>
 * @author Tomasz Wolniewicz <twoln@umk.pl>
 *
 * @package Configuration
 */
const CONFIG = [

    /**
     * Defines parameters how this tool will present itself to users
     * productname: short display name of the tool
     * productname_long: long display name of the tool
     * from-mail: the "From" in email addresses sent by the tool. Typically an unattended mailbox only for sending.
     * support-contact/url: URL pointing to CAT support, can be an email address, possibly with some additional attrinutes like body or a help page address
     * support-contact/display: the displayed part of the support contact link.
     * support-contact/developer-mail: email address where development questions should be addressed to
     * abuse-mail: email address where copyright holders can complain. Should be read by a human.
     * defaultlocale: language to use if user has no preferences in his browser, nor explicitly selects a language
     * @var array 
     */
    'APPEARANCE' => [
        'productname' => 'eduroam CAT',
        'productname_long' => 'eduroam Configuration Assistant Tool',
        'from-mail' => 'cat-invite@your-cat-installation.example',
        'support-contact' => [
            'url' => 'mailto:cat-support@our-cat-installation.example?body=Only%20English%20language%20please!',
            'display' => 'cat-support@our-cat-installation.example',
            'developer-mail' => 'cat-develop@our-cat-installation.example',
        ],
        'abuse-mail' => 'my-abuse-contact@your-cat-installation.example',
        'invitation-bcc-mail' => NULL,
        'defaultlocale' => 'en',
        'MOTD' => "Release Candidate. All bugs to be shot on sight!",
        // # signs before the colour code
        'colour1' => '#BCD7E8',
        'colour2' => '#0A698E',
        // the web server certificate may be checked by browsers against a CRL or OCSP Responder
        // to tell captive portal admins which hosts to allow, list the URLs here (they show up
        // in "About CAT" then)
        'webcert_CRLDP' => ['list', 'of', 'CRL', 'pointers'],
        'webcert_OCSP' => ['list', 'of', 'OCSP', 'pointers'],
    ],

    /**
     * Defines various general parameters of the roaming consortium.
     * name: the display name of the consortium
     * ssid: an array of default SSIDs for this consortium; they are automatically added to all installers.
     * interworking-consortium-oi: Organisation Identifier of the roaming consortium for Interworking/Hotspot 2.0; 
     *                             a profile with these OIs will be added to all installers
     * tkipsupport: whether the default SSIDs should be configured for WPA/TKIP and WPA2/AES (TRUE) or only for WPA2/AES (FALSE)
     * homepage: URL of the consortium's general homepage.
     * signer_name: if installers are configured for digital signature, this parameter should contain the "O" name
     *           in the certificate. If left empty, signatures are not advertised even if configured and working
     * allow_self_service_registration: if set to NULL, federation admins need to invite new inst admins manually
     *                                  if set to a federation ID string, e.g. "DE" for Germany, new admins can
     *                                  self-register and will be put into that federation.
     * registration_API_keys: allows select federations to make bulk registrations for new IdPs (e.g. if they have
     *                        an own, opaque, customer management system. The API will be documented at a later stage
     * LOGOS: there are several variants of the consortium logo scattered in the
     *        source. Please change them at the appropriate places:
     *        - web/resources/images/consortium_logo.png
     *        - web/favicon.ico
     *        - devices/ms/Files/eduroam_150.bmp
     *        - devices/ms/Files/eduroam32.ico
     * 
     * certfilename, keyfilename, keypass: if you want to send S/MIME signed mails, just configure the signing cert
     *                                     with these parameters. All must be non-NULL for signing to happen. If you
     *                                     don't need a keypass, make it an empty string instead.
     * silverbullet options:
     *         default_maxusers: an institution is not allowed to create more than that amount of users
     *             the value can be overriden as a per-federation option in fed-operator UI
     *         realm_suffix: user credentials have a realm which always includes the inst ID and profile ID and the name
     *             of the federation; for routing aggregation purposes /all/ realms should end with a common suffix though
     *             if left empty, realms would end in the federation name only
     * @var array
     */
    'CONSORTIUM' => [
        'name' => 'eduroam',
        'ssid' => ['eduroam'],
        'tkipsupport' => FALSE,
        'homepage' => 'http://www.eduroam.org',
        'signer_name' => 'GÉANT Association',
        'selfservice_registration'   => NULL,
#        'deployment-voodoo'         => "Operations Team",
        'ssid'                       => ['eduroam'],
        'interworking-consortium-oi' => ['001bc50460'],
        'registration_API_keys'      =>  [
            // 'secretvalue' => 'UK',
            // 'othervalue' => 'DE',
        ],
        'certfilename' => NULL,
        'keyfilename' => NULL,
        'keypass' => NULL,
        'silverbullet_default_maxusers' => 200,
        'silverbullet_realm_suffix' => '.hosted.eduroam.org',
    ],

    /**
     * Various paths.
     * logdir: directory where all logs will be written to (debug and audit logs)
     * installerdir: generated installers will be saved under this base directory. Path is relative to the web/ subdirectory.
     * openssl: absolute path to the openssl executable. If you just fill in "openssl" the one from the system $PATH will be taken.
     * eapol_test: absolute path to the eapol_test executable. If you just fill in "eapol_test" the one from the system $PATH will be taken.
     * makensis: absolute path to the makensis executable. If you just fill in "makensis" the one from the system $PATH will be taken.
     *   See also NSIS_VERSION further down
     * @var array
     */
    'PATHS' => [
        'logdir' => '/var/log/CAT/',
        'openssl' => 'openssl',
        'c_rehash' => 'c_rehash',
        'eapol_test' => 'eapol_test',
        'makensis' => 'makensis',
    ],

    /**
     * Configuration for the simpleSAMLphp instance which authenticates CAT administrative users.
     * ssp-path-to-autoloader: points to the simpleSAMLphp autoloader location
     * ssp-authsource: which authsource should we point to?
     * attribute in which authsource transmits unique user identifier. Required. If multi-valued, first submitted value is taken.
     * attribute in which authsource transmits user's mail address. Receiving this attribute is optional.
     * attribute in which authsource transmits user's real name. Receiving this attribute is optional.
     * @var array
     */
    'AUTHENTICATION' => [
        'ssp-path-to-autoloader' => '/srv/www/simplesamlphp/lib/_autoload.php',
        'ssp-authsource' => 'default-sp',
        'ssp-attrib-identifier' => 'eptid',
        'ssp-attrib-email' => 'mail',
        'ssp-attrib-name' => 'cn',
    ],

    /**
      * Configuration for GeoIP2 
      * Beware, the legacy version does not really work with IPv6 addresses
      * version: set to 2 if you wish to use GeoIP2, to 1 for the legacy version or set to 0 to turn off geolocation service
      * geoip2-path-to-autoloader: points to the GeoIP2 autoloader 
      * geoip2-path-to-db: points to the GeoIP2 city database
      * @var array
      */
      
    'GEOIP' => [
        'version' => 0,
        'geoip2-path-to-autoloader' => '/usr/share/GeoIP2/vendor/autoload.php',
        'geoip2-path-to-db' => '/usr/share/GeoIP2/DB/GeoLite2-City.mmdb',
    ],

    /**
     * Configures the host to use to send emails to the outside world. We assume
     * the host is able to listen on the new Submission port (TCP/587). 
     * host: Submission host
     * user: username for the login to the host
     * pass: password for the username
     * @var array
     */
    'MAILSETTINGS' => [ // we always use Submission
        'host' => 'submission.capable.mta',
        'user'=> 'mailuser',
        'pass' => 'mailpass',
    ],
    
    /**
     * List of all supported languages in CAT. Comment some if you want to disable them
     * @var array
     */
    'LANGUAGES' => [
      'ca' => ['display' => 'Català',      'locale' => 'ca_ES.utf8',    'latin_based' => TRUE],
      'de' => ['display' => 'Deutsch',     'locale' => 'de_DE.utf8',    'latin_based' => TRUE],
      'en' => ['display' => 'English(GB)', 'locale' => 'en_GB.utf8',    'latin_based' => TRUE],
      'es' => ['display' => 'Español',     'locale' => 'es_ES.utf8',    'latin_based' => TRUE],
      'gl' => ['display' => 'Galego',      'locale' => 'gl_ES.utf8',    'latin_based' => TRUE],
      'hr' => ['display' => 'Hrvatski',    'locale' => 'hr_HR.utf8',    'latin_based' => TRUE],
      'it' => ['display' => 'Italiano',    'locale' => 'it_IT.utf8',    'latin_based' => TRUE],
      'nb' => ['display' => 'Norsk',       'locale' => 'nb_NO.utf8',    'latin_based' => TRUE],
      'pl' => ['display' => 'Polski',      'locale' => 'pl_PL.utf8',    'latin_based' => TRUE],
      'sl' => ['display' => 'Slovenščina', 'locale' => 'sl_SI.utf8',    'latin_based' => TRUE],
      'sr' => ['display' => 'Srpski',      'locale' => 'sr_RS@latin',    'latin_based' => TRUE],
      'fi' => ['display' => 'Suomi',       'locale' => 'fi_FI.utf8',    'latin_based' => TRUE],
      'el' => ['display' => 'Ελληνικά',    'locale' => 'el_GR.utf8',    'latin_based' => FALSE],
      'hu' => ['display' => 'Magyar',      'locale' => 'hu_HU.utf8',    'latin_based' => TRUE],
      'pt' => ['display' => 'Português',   'locale' => 'pt_PT.utf8',    'latin_based' => TRUE],

// For the following languages, partial translations exist in Transifex, but
// they are not complete enough for display. Their Transifex content is not
// necessarily ported to SVN yet. Contact the authors if you want the current
// state of translation of these languages.
//
// these two were in for 1.0 but didn't make 1.1
//     'sk' => array('display' => 'Slovenčina',  'locale' => 'sk_SK.utf8',    'latin_based' => TRUE),
//     'fr' => array('display' => 'Français',    'locale' => 'fr_FR.utf8',    'latin_based' => TRUE),
//
// and these were never complete
//
//      'nl' => array('display' => 'Nederlands', 'locale' => 'nl_NL.utf8',    'latin_based' => TRUE),
//      'sv' => array('display' => 'Svenska', 'locale' => 'sv_SE.utf8',    'latin_based' => TRUE),
//      'cy' => array('display' => 'Cymraeg', 'locale' => 'cy_GB.utf8',    'latin_based' => TRUE),
    ],

    /**
     * Configures the reachability tests, both for plain RADIUS/UDP and RADIUS/TLS.
     * UDP-hosts: an array of RADIUS servers to which login probes will be sent
     * TLS-discoverytag: the DNS NAPTR label that should be used for finding RADIUS/TLS servers
     * TLS-acceptableOIDs: defines which policy OID is expected from RADIUS/TLS servers and clients
     * TLS-clientcerts: for full two-way auth, the TLS handshake must have access to client certificates.
     * You can specify known-good certificates (expected=pass) and known-bad ones (expected=fail)
     * For each accredited CA you should provide four server certificates: valid, expired, revoked, wrong policy
     * so that all corner cases can be tested. Be sure to set "expected" to match
     * your expectations regarding the outcome of the connection attempt.
     * 
     * @var array
     */
    'RADIUSTESTS' => [
        'UDP-hosts' => [
            ['display_name' => 'Recon Viper 1',
                'ip' => '192.0.2.1',
                'secret' => 'somesecret',
                'timeout' => 5],
            ['display_name' => 'Recon Viper 2',
                'ip' => '198.51.100.17',
                'secret' => 'whatever',
                'timeout' => 5],
        ],
        'TLS-discoverytag' => 'aaa+auth:radius.tls',
        'TLS-acceptableOIDs' => [
            'client' => '1.3.6.1.4.1.25178.3.1.1',
            'server' => '1.3.6.1.4.1.25178.3.1.2',
        ],

        'TLS-clientcerts' => [
          'CA1' => [
            'status' => 'ACCREDITED',
            'issuerCA' => '/DC=org/DC=pki1/CN=PKI 1',
            'certificates' => [
              [
                'status' => 'CORRECT',
                'public' => 'ca1-client-cert.pem',
                'private' => 'ca1-client-key.pem',
                'expected' => 'PASS'],
              [
                'status' => 'WRONGPOLICY',
                'public' => 'ca1-nopolicy-cert.pem',
                'private' => 'ca1-nopolicy-key.key',
                'expected' => 'FAIL'],
              [
                'status' => 'EXPIRED',
                'public' => 'ca1-exp.pem',
                'private' => 'ca1-exp.key',
                'expected' => 'FAIL'],
              [
                'status' => 'REVOKED',
                'public' => 'ca1-revoked.pem',
                'private' => 'ca1-revoked.key',
                'expected' => 'FAIL'],
            ]
          ],
          'CA-N' => [
            'status' => 'NONACCREDITED',
            'issuerCA' => '/DC=org/DC=pkiN/CN=PKI N',
            'certificates' => [
               [
                'status' => 'CORRECT',
                'public' => 'caN-client-cert.pem',
                'private' => 'caN-client-cert.key',
                'expected' => 'FAIL'],
               ]
          ]
      ],
      'accreditedCAsURL' => '',
    ],

    /**
     * Set of database connection details. The third entry is only needed if you set $ENFORCE_EXTERNAL_DB_SYNC to TRUE.
     * See the extra notes on external sync enforcement below.
     * 
     * @var array
     */
    'DB' => [
        'INST' => [
            'host' => 'db.host.example',
            'db' => 'cat',
            'user' => 'someuser',
            'pass' => 'somepass'],
        'USER' => [
            'host' => 'db.host.example',
            'db' => 'cat',
            'user' => 'someuser',
            'pass' => 'somepass'],
        /*   If you use this tool in conjunction with an external customer management database, you can configure that every 
         * institution entry in CAT MUST correspond to a customer entry in an external database. If you want this, set this
         * config variable to TRUE.
         * ### BEWARE: You need to write custom code for the mapping of CAT IDs to the external DB YOURSELF. ###
         * ### The functions where you need to add custom code are:
         * 
         * Federation::listExternalEntities();
         * Federation::getExternalDBEntityDetails($external_id);
         * IdP::getExternalDBSyncCandidates();
         * IdP::getExternalDBSyncState();
         * IdP::setExternalDBId($identifier);
         * 
         * The code for the consortium "eduroam" is already written and may serve as template. See the functions in question. */
        'EXTERNAL' => [
            'host' => 'customerdb.otherhost.example',
            'db' => 'customer_db',
            'user' => 'customerservice',
            'pass' => '2lame4u'],
         'enforce-external-sync' => TRUE,
         /* if you feed your user database from a third-party source and do not want CAT to update it on its own, you can 
          * make it read-only
          */
         'userdb-readonly' => FALSE,
    ],

    /**
     * Maximum size of files to be uploaded. Clever people can circumvent this; in the end, the hard limit is configured in php.ini
     * @var int
     */
    'MAX_UPLOAD_SIZE' => 10000000,

    /**
     * Verbosity of some of the core code. The following debug levels are supported:
     *   1 = production (silence)
     *   2 = normal debug
     *   3 = more debug
     *   4 = annoyingly much debug output
     *   5 = way too much debug output (level 4 + SQL query dump)
     *
     * @var int
     *
     */
    'DEBUG_LEVEL' => 5,

    /**
     * NSIS version - with version 3 UTF installers will be created
     * see also $PATHS['makensis']
     */
    'NSIS_VERSION' => 2,

    'SUPERADMINS' =>  [
        'eptid:someuser',
        'http://sommeopenid.example/anotheruser',
        'I do not care about security!',
    ],
];