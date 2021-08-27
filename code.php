<?php
	require_once('config.php');
	$fails = substr_count(file_get_contents('Banned.db'),hash_file('sha256',$_FILES['file']['tmp_name']));
	if (substr($_FILES['file']['name'],-4)=='.pin' && $fails<6) {
		$zip = new ZipArchive();
		$zip_status = $zip->open($_FILES['file']['tmp_name']);
		if ($zip_status === true) {
			if ($zip->setPassword(hash('sha256',$_POST['pin'] . $secret))) {
				if (!$zip->extractTo(__DIR__)) {
					file_put_contents('Banned.db',hash_file('sha256',$_FILES['file']['tmp_name']) . "\n",FILE_APPEND);
					die("Extraction failed (wrong password?). Attempts left: " . strval(5 - $fails));
				}
				$filename = $zip->getNameIndex(0);
			}
			$zip->close();
		} else die("Failed opening archive: ". @$zip->getStatusString() . " (code: ". $zip_status .")");
		header('Content-Description: Original File');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		echo file_get_contents($filename);	
		unlink($filename);
	} elseif ($fails<6) {
		$zip = new ZipArchive;
		$res = $zip->open($_FILES['file']['name'] . '.zip', ZipArchive::CREATE);
		if ($res === TRUE) {
			$zip->addFile($_FILES['file']['tmp_name'],basename($_FILES['file']['name']));
			$zip->setEncryptionName($_FILES['file']['name'], ZipArchive::EM_AES_256, hash('sha256',substr(hexdec(hash('sha256',$secret .  $_POST['pin'])),3,6) . $secret));
			$zip->close();
			header('Content-Description: Encoded PIN File');
			header('Content-Type: application/x-zip-compressed');
			header('Content-Disposition: attachment; filename="' . substr($_FILES['file']['name'],0,strlen($_FILES['file']['name'])-4) . '.pin"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			echo file_get_contents($_FILES['file']['name'] . '.zip');
			unlink($_FILES['file']['name'] . '.zip');
		} else die('Failed creating archive: ' . substr($_FILES['file']['name'],0,strlen($_FILES['file']['tmp_name'])-4) . '.pin"');
	} else die('Wrong password entered more than 5 times!');
?>
