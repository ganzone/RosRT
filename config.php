<?php

// Change the following configuration to match your MySQL database
$DB_SERVER = "1.2.3.4";
$DB_USERNAME = "your-username";
$DB_PASSWORD = "your-password";
$DB_DATABASE = "your-db";


// Other variables
$MAX_RESULTS = 1000;
$DELETED_DAYS = 90; 		// Giorni mantenimento targhe
$DELETED_DAYS_IMAGES = 6; 	// Giorni mantenimento immagini


// Root folder to start scanning
$SOURCE_FOLDER = '/your-path/your-ftp-files';


// E-Mail Server
$MAIL_SERVER = "your-mailserver";
$MAIL_PORT = 25;
$MAIL_AUTH = false;
$MAIL_SMTPSecure = false;
$MAIL_SMTPAutoTLS = false;
$MAIL_USERNAME = "";
$MAIL_PASSWORD = "";
$MAIL_FROM = "your-sending-mailbox";


// Logging
$LOGDIR = "/var/log/rosrt";

?>
