<style>body { font-family: monospace; }</style>
<?php
	require_once('config.php');
	$hash = openssl_random_pseudo_bytes(256);
	echo 'PUBLIC PIN: ' . substr(hexdec($hash),2,6) . '<br />';
	echo 'SECRET PIN: ' . substr(hexdec(hash('sha256',$secret . substr(hexdec($hash),2,6))),3,6);
?>
