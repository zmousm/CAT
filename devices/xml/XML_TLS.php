<?php

require_once('DeviceConfig.php');
require_once('XML.php');

class Device_XML_TLS extends Device_XML {
    final public function __construct() {
        parent::__construct();
      $this->supportedEapMethods  =
            [
              EAP::$TLS,
       ];
      $this->lang_scope = 'single';
    }
}

?>
