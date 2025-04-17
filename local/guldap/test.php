<?php
$host = 'cytosine.campus.gla.ac.uk';

$r = ldap_connect($host);
ldap_set_option($r, LDAP_OPT_DEBUG_LEVEL, 7);
ldap_set_option($r, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_start_tls($r);
