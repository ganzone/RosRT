
<?php

include "header.php";

$paramLicensePlate = "";
$paramZone = "";
$paramDateFrom = date("Y-m-d", strtotime("-$DELETED_DAYS day"));
$paramDateTo = date("Y-m-d", strtotime("+1 day"));
$paramHourFrom = "00:00";
$paramHourTo = date("23:59");
$paramColor = "";
$paramBrand = "";

if (isset($_GET['q']))			      { $QueryMode = true; } else { $QueryMode = false; }
if (isset($_GET['licensePlate'])) { $paramLicensePlate	= substr($_GET['licensePlate'],0,10); }
if (isset($_GET['zone']))		      { $paramZone 		  = substr($_GET['zone'],0,15); } 
if (isset($_GET['dateFrom']))		  { $paramDateFrom	= substr($_GET['dateFrom'],0,10); }
if (isset($_GET['dateTo']))		    { $paramDateTo		= substr($_GET['dateTo'],0,10); }
if (isset($_GET['hourFrom']))		  { $paramHourFrom	= substr($_GET['hourFrom'],0,5); }
if (isset($_GET['hourTo']))		    { $paramHourTo		= substr($_GET['hourTo'],0,5); }
if (isset($_GET['color']))        { $paramColor     = substr($_GET['color'],0,10); }
if (isset($_GET['brand']))        { $paramColor     = substr($_GET['brand'],0,15); }

?>

<div class="container m-4">
<form method="GET" action="">
<input type="hidden" name="q">

<div class="row">
<div class="col">
<!--<div class="d-flex align-items-center justify-content-center">-->
  <div class="form-group">
    <div class="input-group">

<?php

if ($paramLicensePlate != "") {
	echo "<label class='active' for='licensePlate'>Targa auto/moto</label>";
	echo "<input type='text' class='form-control' id='licensePlate' name='licensePlate' size='64' aria-describedby='licensePlateDesc' value='$paramLicensePlate'>";
} else {
	echo "<label for='licensePlate'>Targa auto/moto</label>";
	echo "<input type='text' class='form-control' id='licensePlate' name='licensePlate' size='64' aria-describedby='licensePlateDesc'>";
}
?>

    </div>
    <small id="licensePlateDesc" class="form-text">Esempi: AB123CD , 123 , AB*CD, 1?3</small>
  </div>

</div>
<div class="col">
  <div class="form-group">
	<label class="active" for="dateFrom">Data inizio:</label>
	<?php echo "<input type='date' id='dateFrom' name='dateFrom' value='$paramDateFrom'>"; ?>
  </div>
</div>
<div class="col">
  <div class="form-group">
	<label class="active" for="hourFrom">Ora inizio:</label>
	<?php echo "<input type='time' id='hourFrom' name='hourFrom' value='$paramHourFrom'>"; ?>
  </div>  
</div>

</div><div class="row">

<div class="col">
 	<div class="select-wrapper">
		<label for="siteSelect">Zona</label>
		<select id='siteSelect' style='width: 350px;' name='zone'>
			<option value="">Tutte</option>
			<option value="Castiglioncello" <?php if ($paramZone == "Castiglioncello") { echo "selected"; } ?> >Castiglioncello</option>
			<option value="Morelline" <?php if ($paramZone == "Morelline") { echo "selected"; } ?> >Morelline</option>
			<option value="Solvay" <?php if ($paramZone == "Solvay") { echo "selected"; } ?> >Rosignano Solvay</option>
		</select>
	</div>
</div>
<div class="col">
  <div class="form-group">
	<label class="active" for="dateTo">Data fine:</label>
	<?php echo "<input type='date' id='dateTo' name='dateTo' value='$paramDateTo'>"; ?>
  </div>
</div>
<div class="col">
  <div class="form-group">
	<label class="active" for="hourTo">Ora fine:</label>
	<?php echo "<input type='time' id='hourTo' name='hourTo' value='$paramHourTo'>"; ?>
  </div>
</div>

</div><div class="row">

	<div class="col">
		<button type="submit" class="btn btn-primary">Cerca</button>
	</div>

<?php
if ($QueryMode) {
        echo "<div class='col'>
		<div class='fw-bold'>
			<p>
				Risultati visualizzati: <span id='ID_CountResults'></span>
			</p>
		</div>
	      </div>
	      <div class='col'>
		<div class='fw-bold'>Download <a href='export.php?".$_SERVER['QUERY_STRING']."'>
			<svg class='icon icon-primary'><use href='svg/sprites.svg#it-download'></use></svg></a>
		</div>
	      </div>";
}
?>

</div>

</form>
</div>


<div class="row">
<div class="col">
<?php
if ($QueryMode) {
    // Create a MySQL connection
    $conn = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("
      SELECT e.*, c.camera_desc FROM (
	SELECT ID, timestamp, camera_ip, plate_num
	FROM events
	WHERE plate_num LIKE ?
	AND timestamp >= ?
	AND timestamp <= ?
	ORDER BY timestamp DESC
	LIMIT $MAX_RESULTS
	) e
      INNER JOIN cam_info c ON c.camera_ip = e.camera_ip
      INNER JOIN zones z ON c.zone_ID = z.ID
      WHERE z.zone_desc LIKE ?
      ORDER BY timestamp DESC
      ");

    $stmt_count = $conn->prepare("
	SELECT count(ID)
        FROM events
        WHERE plate_num LIKE ?
        AND timestamp >= ?
        AND timestamp <= ?");

    $paramLicensePlateQuery = "%".str_replace("?", "_", str_replace("*", "%", $paramLicensePlate))."%";
    $paramZoneQuery = "%".$paramZone."%";
    $paramDateFromQuery = $paramDateFrom." ".$paramHourFrom;
    $paramDateToQuery = $paramDateTo." ".$paramHourTo;

    $stmt->bind_param("ssss", $paramLicensePlateQuery, $paramDateFromQuery, $paramDateToQuery, $paramZoneQuery);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($resultID, $resultTimeStamp, $resultCameraIP, $resultPlateNum, $resultCameraDesc);

    $stmt_count->bind_param("sss", $paramLicensePlateQuery, $paramDateFromQuery, $paramDateToQuery);
    $stmt_count->execute();
    $stmt_count->store_result();
    $stmt_count->bind_result($resultNumRows);
    $stmt_count->fetch();
    $stmt_count->close();

    DBLog("Eseguita ricerca [Targa=$paramLicensePlate , Zona=$paramZone , From=$paramDateFromQuery , To=$paramDateToQuery]", "SEARCH");

    // Display the results in a table
    echo "<table class='table table-default'>";
    echo "<tr><th>Targa</th><th>Data/Ora</th><th>Luogo</th><th>Foto</th></tr>";

    $result_num = 0;
    while ($stmt->fetch()) {
      if ($result_num >= $MAX_RESULTS) { break 1; } else { $result_num++; }

      echo "<tr>";
      echo "	<td><p class='text text-uppercase font-monospace fw-bold'>$resultPlateNum</p></td>";
      echo "	<td>$resultTimeStamp</td>";
      echo "	<td>$resultCameraDesc</td>";
      echo "	<td>";

      if (strtotime($resultTimeStamp) >= strtotime("-". ($DELETED_DAYS_IMAGES - 1) ." days")) {
	echo "
<!-- View button -->
<button type='button' class='btn btn-primary btn-icon' data-bs-toggle='modal' data-bs-target='#ModalAnprWindow' onclick=document.getElementById(\"imgFrame\").src=\"getAnprImg.php?eventID=".$resultID."\" >
	<svg class='icon icon-white'><use href='svg/sprites.svg#it-camera'></use></svg>
</button>";
      }
      
      echo "</td></tr>";

    }

    echo "</table>";
?>

<!-- Modal window -->
<div class='modal fade' id='ModalAnprWindow' tabindex='-1' aria-labelledby='exampleModalLabel' aria-hidden='true' role='dialog'>
  <div class='modal-dialog modal-dialog-centered modal-xl' role='document'>
    <div class='modal-content'>
      <div class='modal-header'>
        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
      </div>
      <div class='modal-body'>
	<iframe src='' id='imgFrame' width='100%' height='700' allowfullscreen></iframe>
      </div>
      <div class='modal-footer'>
        <button type='button' class='btn btn-primary' data-bs-dismiss='modal'>Close</button>
      </div>
    </div>
  </div>
</div>

<?php
    echo "<script>";

    /*
    if ($stmt->num_rows > $MAX_RESULTS) { 
	    echo "        document.getElementById('ID_CountResults').innerHTML = '$MAX_RESULTS ($stmt->num_rows trovati)'";
    } else {
	    echo "        document.getElementById('ID_CountResults').innerHTML = '$stmt->num_rows'";
    }
    */
    echo "	document.getElementById('ID_CountResults').innerHTML = '$result_num ($resultNumRows trovati)'";
 
    echo "</script>";
    
    // Close the statement and connection
    $stmt->close();
    $conn->close();

  } // If $QueryMode

?>


</div>
</div>
</div>
</div>

<br />
<br />
<br />

<?php
  
include "footer.php";

?>
