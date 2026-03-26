<?php

# ANPR Upload Data to MySQL
# 2023-05 - Gorini F.

$logfile = "/var/log/anpr/axis-receiver.log";
$log = fopen($logfile, "a");

$mysql_server = "127.0.0.1";
$mysql_user = "anpruser";
$mysql_pass = "AnprPass@2023";
$mysql_db = "anpr";


$conn = new mysqli($mysql_server, $mysql_user, $mysql_pass, $mysql_db);
if ($conn->connect_error) {
	fwrite($log, "Error connecting to DB: " . connect_error);
	die("Connection failed: " . connect_error);
}

$image = addslashes(file_get_contents($_FILES['image']['tmp_name']));
$json = file_get_contents($_FILES['event']['tmp_name']);
$json_data = json_decode($json, true);

$plate_num = $json_data["plateASCII"];
$plate_country = $json_data["plateCountry"];
$camera_ip = $_SERVER['REMOTE_ADDR'];

#$sql = "INSERT INTO events (plate_num, plate_country, camera_ip, plate_img) VALUES (".
$sql = "INSERT INTO events (plate_num, plate_country, camera_ip) VALUES (".
	"'".$plate_num."',".
	"'".$plate_country."',".
	"'".$camera_ip."'".
#	"'".$image."'".
	")";


if ($conn->query($sql) === TRUE) {
	#fwrite($log, "SQL OK\n");
} else {
	#fwrite($log, "SQL Error: ". $conn->error."\n");
}


fclose($log);


################### FROM AXIS DOCUMENTATION
#
# Create files in the following directory
#$uploaddir = 'axis-log/';

# Set path and filename where to store the event
#$uploadfile = $uploaddir . basename($_FILES['event']['name']);

# Store the JSON file on disk
#if (move_uploaded_file($_FILES['event']['tmp_name'], $uploadfile)) {
  # All good, do other things here
#} else {
  # Failed moving file, do proper error handling
#}

# Set path end filename where to store the image
#$uploadfile = $uploaddir . basename($_FILES['image']['name']);

# Store the image file on disk
#if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadfile)) {
  # All good, do other things here
#} else {
  # Failed moving file, do proper error handling
#}


?>
