<?php

// These are ok.
preg_match_all('`[a-z]+`', $subject, $matches);
stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_SSLv23_CLIENT);

// These are not.
preg_match_all('`[a-z]+`', $subject);
stream_socket_enable_crypto($fp, true);

bcscale( 10 ); // Ok.
bcscale(); // PHP 7.3+.

$ip = getenv('REMOTE_ADDR'); // OK
$ip = getenv(); // PHP 7.1+
