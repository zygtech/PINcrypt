<?php
	require_once('config.php');
	if (substr($_FILES['file']['name'],-4)=='.pin') {
		$zip = new ZipArchive();
		$zip_status = $zip->open($_FILES['file']['tmp_name']);
		if ($zip_status === true) {
			if ($zip->setPassword(hash('sha256',$_POST['pin'] . $secret))) {
				if (!$zip->extractTo(__DIR__))
					die("Extraction failed (wrong password?)");
			}
			$zip->close();
		} else die("Failed opening archive: ". @$zip->getStatusString() . " (code: ". $zip_status .")");
		header('Content-Description: Original ZIP File');
		header('Content-Type: application/x-zip-compressed');
		header('Content-Disposition: attachment; filename="' . substr($_FILES['file']['name'],0,strlen($_FILES['file']['name'])-4) . '.zip"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		echo file_get_contents(substr($_FILES['file']['name'],0,strlen($_FILES['file']['name'])-4) . '.zip');	
		unlink(substr($_FILES['file']['name'],0,strlen($_FILES['file']['name'])-4) . '.zip');
	} elseif (substr($_FILES['file']['name'],-4)=='.zip') {
		$zip = new ZipArchive;
		$res = $zip->open($_FILES['file']['name'], ZipArchive::CREATE);
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
			echo file_get_contents($_FILES['file']['name']);
			unlink($_FILES['file']['name']);
		} else die('Failed creating archive: ' . substr($_FILES['file']['name'],0,strlen($_FILES['file']['tmp_name'])-4) . '.pin"');
	}
?>