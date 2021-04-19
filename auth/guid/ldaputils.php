<?php

/**
 * Tries connect to specified ldap servers. Returns a valid LDAP
 * connection or false.
 *
 * @param string $host_url
 * @param integer $ldap_version either 2 (LDAPv2) or 3 (LDAPv3).
 * @param string $user_type the configured user type for this connection.
 * @param string $bind_dn the binding user dn. If an emtpy string, anonymous binding is used.
 * @param string $bind_pw the password for the binding user. Ignored for anonymous bindings.
 * @param boolean $opt_deref whether to set LDAP_OPT_DEREF on this connection or not.
 * @param string &$debuginfo the debugging information in case the connection fails.
 * @param boolean $start_tls whether to use LDAP with TLS (not to be confused with LDAP+SSL)
 * @return mixed connection result or false.
 */
function guid_connect_moodle($host_url, $ldap_version, $user_type, $bind_dn, $bind_pw, $opt_deref, &$debuginfo, $start_tls=false) {
    if (empty($host_url) || empty($ldap_version) || empty($user_type)) {
        $debuginfo = 'No LDAP Host URL, Version or User Type specified in your LDAP settings';
        return false;
    }

    $debuginfo = '';
    $urls = explode(';', $host_url);
    foreach ($urls as $server) {
        $server = trim($server);
        if (empty($server)) {
            continue;
        }

	$connresult = ldap_connect($server); // ldap_connect returns ALWAYS true

	// Reduce timeout
	ldap_set_option($connresult, LDAP_OPT_NETWORK_TIMEOUT, 5);

        if (!empty($ldap_version)) {
            ldap_set_option($connresult, LDAP_OPT_PROTOCOL_VERSION, $ldap_version);
        }

        // Fix MDL-10921
        if ($user_type === 'ad') {
            ldap_set_option($connresult, LDAP_OPT_REFERRALS, 0);
        }

        if (!empty($opt_deref)) {
            ldap_set_option($connresult, LDAP_OPT_DEREF, $opt_deref);
        }

        if ($start_tls && (!ldap_start_tls($connresult))) {
            $debuginfo .= "Server: '$server', Connection: '$connresult', STARTTLS failed.\n";
            continue;
        }

        if (!empty($bind_dn)) {
            $bindresult = @ldap_bind($connresult, trim($bind_dn), trim($bind_pw));
        } else {
            // Bind anonymously
            $bindresult = @ldap_bind($connresult);
        }

        if ($bindresult) {
            return $connresult;
        }

        $debuginfo .= "Server: '$server', Connection: '$connresult', Bind result: '$bindresult'\n";
    }

    // If any of servers were alive we have already returned connection.
    return false;
}
