
<?php

// Elimina eventi/immagini piu' vecchi di $DELETED_DAYS giorni


include "config.php";

// Create a new database connection
$conn = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Cancellazione immagini
$query = "UPDATE events SET image = NULL , image_isnull = 1 WHERE image_isnull = 0 AND timestamp <= (now() - interval $DELETED_DAYS_IMAGES day)";
$stmt = $conn->prepare($query);
try {
	echo "EXECUTING: $query".PHP_EOL;
	$stmt->execute();
	echo "OK - Deleted event images older than $DELETED_DAYS_IMAGES days".PHP_EOL;
}
catch (Exception $e) {
	echo "ERROR purging ANPR data";
	echo $e->getMessage().PHP_EOL;
}


// Cancellazione eventi/targhe
$query = "DELETE FROM events WHERE timestamp <= (now() - interval $DELETED_DAYS day)";
$stmt = $conn->prepare($query);
try {
	echo "EXECUTING: $query".PHP_EOL;
	$stmt->execute();
	echo "OK - Deleted events older than $DELETED_DAYS days".PHP_EOL;
}
catch (Exception $e) {
	echo "ERROR purging ANPR data";
	echo $e->getMessage().PHP_EOL;
}

// Close the statement
$stmt->close();

// Close the database connection
$conn->close();
?>

