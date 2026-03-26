<?php

include "../config.php";
include "logs_utils.php";

session_start();

// Check if the user is already authenticated
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}


$paramLicensePlate	= substr($_GET['licensePlate'],0,10);
$paramZone          = substr($_GET['zone'],0,15); 
$paramDateFrom		= substr($_GET['dateFrom'],0,10);
$paramDateTo		= substr($_GET['dateTo'],0,10);
$paramHourFrom		= substr($_GET['hourFrom'],0,5);
$paramHourTo		= substr($_GET['hourTo'],0,5);

// Create a MySQL connection
$conn = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


DBlog("Esportazione [Targa=$paramLicensePlate , Zone=$paramZone, From=$paramDateFrom $paramHourFrom , To=$paramDateTo $paramHourTo", "EXPORT");


// Prepare and execute the query
$stmt = $conn->prepare("
SELECT e.ID, e.timestamp, c.camera_desc, e.plate_num, z.zone_desc
FROM events e
INNER JOIN cam_info c ON c.camera_ip = e.camera_ip
INNER JOIN zones z ON c.zone_ID = z.ID
WHERE plate_num LIKE ?
AND timestamp >= ?
AND timestamp <= ?
AND z.zone_desc LIKE ?
ORDER BY timestamp
DESC
");


$paramLicensePlateQuery = "%".str_replace("?", "_", str_replace("*", "%", $paramLicensePlate))."%";
$paramZoneQuery = "%".$paramZone."%";
$paramDateFromQuery = $paramDateFrom." ".$paramHourFrom;
$paramDateToQuery = $paramDateTo." ".$paramHourTo;

$stmt->bind_param("ssss", $paramLicensePlateQuery, $paramDateFromQuery, $paramDateToQuery, $paramZoneQuery);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($resultID, $resultTimeStamp, $resultCameraDesc, $resultPlateNum, $resultZoneDesc);

// HTTP header
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=export.csv');

// Create temporary file
$output = fopen("php://output", "w");
fputs($output, $bom=( chr(0xEF) . chr(0xBB) . chr(0xBF) )); // To fix Excel import encoding problem

fputcsv($output, array("Targa", "Data/Ora", "Luogo", "Zona"));
while ($stmt->fetch()) {
fputcsv($output, array($resultPlateNum, $resultTimeStamp, $resultCameraDesc, $resultZoneDesc));
}

fclose($output);	    

// Close the statement and connection
$stmt->close();
$conn->close();

?>
