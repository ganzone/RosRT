
<?php

include "header.php";

// Create a MySQL connection
$conn = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$paramLicensePlate = "";
$paramZone = "";
$paramDateFrom = date("Y-m-d", strtotime("-$DELETED_DAYS day"));
$paramDateTo = date("Y-m-d", strtotime("+1 day"));
$paramHourFrom = "00:00";
$paramHourTo = date("00:00");
$paramVehicleColor = "";
$paramVehicleBrand = "";

if (isset($_GET['q']))			      { $QueryMode = true; } else { $QueryMode = false; }
if (isset($_GET['licensePlate'])) { $paramLicensePlate	= substr($_GET['licensePlate'],0,10); }
if (isset($_GET['zone']))		      { $paramZone 		      = substr($_GET['zone'],0,15); } 
if (isset($_GET['dateFrom']))		  { $paramDateFrom	    = substr($_GET['dateFrom'],0,10); }
if (isset($_GET['dateTo']))		    { $paramDateTo		    = substr($_GET['dateTo'],0,10); }
if (isset($_GET['hourFrom']))		  { $paramHourFrom	    = substr($_GET['hourFrom'],0,5); }
if (isset($_GET['hourTo']))		    { $paramHourTo		    = substr($_GET['hourTo'],0,5); }
if (isset($_GET['color']))        { $paramVehicleColor  = substr($_GET['color'],0,10); }
if (isset($_GET['brand']))        { $paramVehicleBrand  = substr($_GET['brand'],0,30); }


// Recupera elenco marche auto
$VehicleBrands = array();
$stmt = $conn->prepare ("SELECT vehicle_brand from events group by vehicle_brand order by vehicle_brand");
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($result);
while ($stmt->fetch()) {
  array_push($VehicleBrands, $result);
}

// Recupera elenco colori auto
$VehicleColors = array();
$stmt = $conn->prepare ("SELECT vehicle_color from events group by vehicle_color order by vehicle_color");
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($result);
while ($stmt->fetch()) {
  array_push($VehicleColors, $result);
}


// Recupera elenco zone
$Zones = array();
$stmt = $conn->prepare ("SELECT zone_desc from zones group by zone_desc order by zone_desc");
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($result);
while ($stmt->fetch()) {
  array_push($Zones, $result);
}


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
	echo "<input type='text' class='form-control' id='licensePlate' name='licensePlate' size='64' style='width: 300px;' aria-describedby='licensePlateDesc' value='$paramLicensePlate'>";
} else {
	echo "<label for='licensePlate'>Targa auto/moto</label>";
	echo "<input type='text' class='form-control' id='licensePlate' name='licensePlate' size='64' style='width: 300px;' aria-describedby='licensePlateDesc'>";
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
<div class="col">
 	<div class="select-wrapper">
		<label for="brand">Marca auto:</label>
		<select id='brand' style='width: 350px;' name='brand'>
    <?php

    foreach ($VehicleBrands as $Brand) {
      if ($paramVehicleBrand == $Brand) {
        echo "<option value='$Brand' selected>$Brand</option>";
      } else {
        echo "<option value='$Brand'>$Brand</option>";
      }
    }

    ?>
    </select>
  </div>
</div>


</div><div class="row">

<div class="col">
 	<div class="select-wrapper">
		<label for="siteSelect">Zona</label>
		<select id='siteSelect' style='width: 300px;' name='zone'>
    <option value=''></option>";
    <?php
    foreach ($Zones as $Zone) {
      if ($Zone == $paramZone) {
        echo "<option value='$Zone' selected>$Zone</option>";
      } else {
        echo "<option value='$Zone'>$Zone</option>";
      }
    }
    ?>
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
<div class="col">
 	<div class="select-wrapper">
		<label for="color">Colore auto:</label>
		<select id='color' style='width: 350px;' name='color'>
    <?php
    foreach ($VehicleColors as $Color) {
      if ($Color == $paramVehicleColor) {
        echo "<option value='$Color' selected>$Color</option>";
      } else {
        echo "<option value='$Color'>$Color</option>";
      }
    }
    ?>
    </select>
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
  
    $stmt = $conn->prepare("
      SELECT e.*, c.camera_desc FROM (
        SELECT ID, timestamp, camera_ip, plate_num, vehicle_brand, vehicle_color
        FROM events
        WHERE plate_num LIKE ?
          AND timestamp >= ?
          AND timestamp <= ?
          AND vehicle_brand LIKE ?
          AND vehicle_color LIKE ?
        ORDER BY timestamp DESC
        LIMIT $MAX_RESULTS
      ) e
      INNER JOIN cam_info c ON c.camera_ip = e.camera_ip
      INNER JOIN zones z ON c.zone_ID = z.ID
      WHERE z.zone_desc LIKE ?
      ORDER BY timestamp DESC
      ");

    $stmt_count = $conn->prepare("
      SELECT count(count_ID) FROM (
        SELECT count(ID) as count_ID, camera_ip
        FROM events e
        WHERE plate_num LIKE ?
          AND timestamp >= ?
          AND timestamp <= ?
          AND vehicle_brand LIKE ?
          AND vehicle_color LIKE ?
        GROUP BY camera_ip
      ) e
      INNER JOIN cam_info c ON c.camera_ip = e.camera_ip
      INNER JOIN zones z ON c.zone_ID = z.ID
      WHERE z.zone_desc LIKE ?
      ");

    $paramLicensePlateQuery = "%".str_replace("?", "_", str_replace("*", "%", $paramLicensePlate))."%";
    $paramZoneQuery = "%$paramZone%";
    $paramDateFromQuery = "$paramDateFrom $paramHourFrom";
    $paramDateToQuery = "$paramDateTo $paramHourTo";
    $paramVehicleBrand == "%$paramVehicleBrand%";
    
    $paramVehicleColorQuery = "%$paramVehicleColor%";

    $stmt->bind_param("ssssss", $paramLicensePlateQuery, $paramDateFromQuery, $paramDateToQuery, $paramVehicleBrandQuery, $paramVehicleColorQuery, $paramZoneQuery);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($resultID, $resultTimeStamp, $resultCameraIP, $resultPlateNum, $resultVehicleBrand, $resultVehicleColor, $resultCameraDesc);

    $stmt_count->bind_param("ssssss", $paramLicensePlateQuery, $paramDateFromQuery, $paramDateToQuery, $paramVehicleBrandQuery, $paramVehicleColorQuery, $paramZoneQuery);
    $stmt_count->execute();
    $stmt_count->store_result();
    $stmt_count->bind_result($resultNumRows);
    $stmt_count->fetch();
    $stmt_count->close();

    DBLog("Eseguita ricerca [Targa=$paramLicensePlate , Zona=$paramZone , From=$paramDateFromQuery , To=$paramDateToQuery, MarcaAuto=$paramVehicleBrand, ColoreAuto=$paramVehicleColor]", "SEARCH");

    // Display the results in a table
    echo "<table class='table table-default'>";
    echo "<tr><th>Targa</th><th>Data/Ora</th><th>Luogo</th><th>Marca</th><th>Colore</th><th>Foto</th></tr>";

    $result_num = 0;
    while ($stmt->fetch()) {
      if ($result_num >= $MAX_RESULTS) { break 1; } else { $result_num++; }

      echo "<tr>";
      echo "	<td><p class='text text-uppercase font-monospace fw-bold'>$resultPlateNum</p></td>";
      echo "	<td>$resultTimeStamp</td>";
      echo "	<td>$resultCameraDesc</td>";
      echo "	<td>$resultVehicleBrand</td>";
      echo "	<td>$resultVehicleColor</td>";
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
