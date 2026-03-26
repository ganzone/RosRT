
<?php

// Pagina per finestra modulare di visualizzazione informazioni evento
// Recupera solo l'immagine cliccata e i dati dell'evento a partire dall'ID dell'evento


include "../config.php";
include "logs_utils.php";

session_start();

// Check if the user is already authenticated
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

?>


<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title></title>
  <link rel="stylesheet" href="css/bootstrap-italia.min.css" />
</head>

<body>

<div class="container">
  <div class="row">
    <div class="col">

  <?php
  if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $eventID = substr($_GET['eventID'], 0, 36);

    // Create a MySQL connection
    $conn = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and execute the query
    $stmt = $conn->prepare("SELECT timestamp, camera_desc, plate_num, image
                            FROM events
                            INNER JOIN cam_info ON cam_info.camera_ip = events.camera_ip
                            WHERE events.ID = ?");
    $stmt->bind_param("s", $eventID);
    $stmt->execute();

    // Store the results in variables
    //$stmt->bind_result($resultID, $resultTimeStamp, $resultCameraIP, $resultPlateNum, $resultFileName, $resultImage);
    $stmt->bind_result($resultTimeStamp, $resultCameraDesc, $resultPlateNum, $resultImage);

    $stmt->fetch();
    echo "Plate Number: $resultPlateNum - ";
    echo "Time: $resultTimeStamp - ";
    echo "Site: $resultCameraDesc";
    echo"<br /><br />";
    echo "<img src='data:image/jpeg;base64,".base64_encode($resultImage)."' width='100%' />";
   
    // Close the statement and connection    
    $stmt->close();

    DBlog("Visualizzazione [Targa=$resultPlateNum , Camera=$resultCameraDesc, Time=$resultTimeStamp]", "VIEW");

    $conn->close();

  }

?>

    </div>
  </div>
</div>

<script src="js/bootstrap-italia.bundle.min.js"></script>

</body>
</html>


