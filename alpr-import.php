<?php

# Importa immagini caricate dalle telecamere dalla cartella FTP al database

include "config.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require "phpmailer/phpmailer/src/Exception.php";
require "phpmailer/phpmailer/src/PHPMailer.php";
require "phpmailer/phpmailer/src/SMTP.php";


$conn = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Scansiona immagini ricorsivamente a partire dalla cartella iniziale
importImages($SOURCE_FOLDER);

$conn->close();


// Funzione per scansionare ricorsivamente le cartelle e importare immagini nel database
function importImages($folder) {
    global $conn;

    // Scan the folder
    $files = scandir($folder);

    // Iterate through each file
    foreach ($files as $file) {

		if ($file == '.' || $file == '..') {
			continue;
        }

        $filePath = $folder.'/'. $file;

        // Check if it's a directory
        if (is_dir($filePath)) {
            // Recursively scan subfolder
            importImages($filePath);
			continue;
        }

		// Check if it's a JPG file
		if (pathinfo($file, PATHINFO_EXTENSION) != 'jpg') {
			continue;
		}

		// Extract the relevant information from the file name
		echo "Analyzing file: $filePath...".PHP_EOL;
		$fileNameParts = explode('_', basename($file, ".jpg"));

		if (count($fileNameParts) < 4 or count($fileNameParts) > 6) {
			echo "ERROR importing: $filePath".PHP_EOL;
			echo "UNRECOGNIZED FILENAME".PHP_EOL;

			// Delete File
			unlink($filePath);
			echo "Deleted file:   $filePath".PHP_EOL;
			continue;
		}

		$date = $fileNameParts[0];
		$time = $fileNameParts[1];
		$camip = $fileNameParts[2];
		$plate = $fileNameParts[3];
		if (count($fileNameParts) == 5) {
			$color = $fileNameParts[4];
		}
		if (count($fileNameParts) == 6) {
			$brand = $fileNameParts[4];
			$color = $fileNameParts[5];
		}

		$image = file_get_contents($filePath);

		// Recupera valore di aggiustamento ore
		$sql = "SELECT hour_adjust FROM cam_info WHERE camera_ip = '$camip'";
		$result = $conn->query($sql);
		$hour_adjust = $result->fetch_object()->hour_adjust;

		// Calcolo timestamp
		$timestamp = date("Y-m-d H:i:s", strtotime($date." ".$time) + $hour_adjust * 3600);

		// Prepare the SQL statement
		if (count($fileNameParts) == 4) {
			$stmt = $conn->prepare("INSERT INTO events (timestamp, camera_IP, plate_num, filename, image) VALUES (?, ?, ?, ?, ?)");
			$stmt->bind_param("ssssb", $timestamp, $camip, $plate, $file, $image);
		}
		if (count($fileNameParts) == 5) {
			$stmt = $conn->prepare("INSERT INTO events (timestamp, camera_IP, plate_num, vehicle_color, filename, image) VALUES (?, ?, ?, ?, ?, ?)");
			$stmt->bind_param("sssssb", $timestamp, $camip, $plate, $color, $file, $image);
		}
		if (count($fileNameParts) == 6) {
			$stmt = $conn->prepare("INSERT INTO events (timestamp, camera_IP, plate_num, vehicle_brand, vehicle_color, filename, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
			$stmt->bind_param("ssssssb", $timestamp, $camip, $plate, $brand, $color, $file, $image);
		}

		$stmt->send_long_data(count($fileNameParts), $image);


		// Execute the statement
		try {
			$stmt->execute();
			echo "Imported file:  $filePath".PHP_EOL;
		} catch (Throwable $e) {
			echo "Error importing: $filePath".PHP_EOL;
			echo $e->getMessage().PHP_EOL;
			// Delete File
			unlink($filePath);
			echo "Deleted file:   $filePath".PHP_EOL;
			continue;
		} finally {
			// Close the statement
			$stmt->close();
		}

		// Controlla se la targa e' in watchlist e invia notifica Telegram ed E-Mail (DA MIGLIORARE, EVITARE QUERY AD OGNI IMMAGINE IMPORTATA)
		$sql = "SELECT *, g.telegram_url, g.ID as group_ID FROM watchlist w
				INNER JOIN users u ON w.added_by = u.username
				INNER JOIN users_groups g ON u.group_ID = g.ID";
		$result = $conn->query($sql);

		while ($plate_row = $result->fetch_object()) {
			
			if ($plate_row->status == 0) { continue; } // Riga di watchlist non attiva

			$pattern = "/^$plate_row->plate_num$/i";
			$pattern = str_replace("?", "[a-z0-9]?", $pattern); // Carattere Wildcard ?
			$pattern = str_replace("*", "[a-z0-9]*", $pattern); // Carattere Wildcard *

			if (!preg_match($pattern, $plate)) { continue; } // Targa NON in watchlist

			// Recupera descrizione telecamera
			$sql_camera_desc = "SELECT camera_desc FROM cam_info WHERE camera_ip = '$camip'";
			$camera_desc = $conn->query($sql_camera_desc)->fetch_object()->camera_desc;
			
			// Testo del messaggio
			$message = "Rilevata targa: $plate\nCommento: $plate_row->comments\nTelecamera: $camera_desc\nAggiunta da: $plate_row->added_by";
			$message_HTML = "					
			<b>Comune di Rosignano</b><br/><br/>
			<font face='Verdana,Arial,Helvetica,sans-serif'>
				Rilevata targa: $plate<br/>
				Commento: $plate_row->comments<br/>
				Telecamera: $camera_desc<br>
				Aggiunta da: $plate_row->added_by<br/>
				<br/>
				Immagine in allegato<br/>
			</font>
			";

			// INVIO NOTIFICA TELEGRAM
			$post_fields = array(
				"caption" => $message,
				"photo" => new CURLFile($filePath)
				);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
			curl_setopt($ch, CURLOPT_URL, $plate_row->telegram_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
			$output = curl_exec($ch);
			curl_close($ch);

			// INVIO NOTIFICA EMAIL
			$usermails_sql = "SELECT u.email FROM users u INNER JOIN users_groups g ON u.group_ID = g.ID WHERE g.ID = '$plate_row->group_ID'";
			$usermails_results = $conn->query($usermails_sql);

			while ($user = $usermails_results->fetch_object()) {

				if ($user->email == null) { continue; } // No mail address specified for user

				$mail = new PHPMailer(true);
				global $MAIL_SERVER;
				global $MAIL_AUTH;
				global $MAIL_USERNAME;
				global $MAIL_PASSWORD;
				global $MAIL_SMTPSecure;
				global $MAIL_SMTPAutoTLS;
				global $MAIL_PORT;
				global $MAIL_FROM;
				
				// Server Settings
				//$mail->SMTPDebug = SMTP::DEBUG_SERVER;
				$mail->isSMTP();
				$mail->isHTML(true);
				$mail->Host = $MAIL_SERVER;
				$mail->SMTPAuth = $MAIL_AUTH;
				$mail->Username = $MAIL_USERNAME;
				$mail->Password = $MAIL_PASSWORD;
				$mail->SMTPSecure = $MAIL_SMTPSecure;
				$mail->SMTPAutoTLS = $MAIL_SMTPAutoTLS;
				$mail->Port = $MAIL_PORT;
				$mail->Timeout = 60;

				// Recipients
				$mail->setFrom($MAIL_FROM);
				$mail->addAddress($user->email);
				
				// Attachments
				$mail->addAttachment($filePath);

				// Contents
				$mail->Priority = 1;
				$mail->Subject = "Comune di Rosignano Marittimo - Targa rilevata: $plate";
				$mail->Body = $message_HTML;

				//$mail->send();

			}

		}

		// Delete File
		unlink($filePath);
		echo "Deleted file:   $filePath".PHP_EOL;

	}
}

?>
