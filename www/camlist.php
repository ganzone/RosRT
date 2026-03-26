
<?php

include "header.php";


$QueryMode = "LIST";
if (isset($_GET['q']) and ($_GET['q'] === "d")) { $QueryMode = "DELETE"; }
if (isset($_GET['q']) and ($_GET['q'] === "e")) { $QueryMode = "EDIT"; }
if (isset($_GET['q']) and ($_GET['q'] === "a")) { $QueryMode = "ADD"; }
if ($QueryMode != "LIST") {
	if (isset($_GET['ID'])) {		$paramID 		= substr($_GET['ID'],0,36); }
	if (isset($_GET['PlateNum'])) {		$paramPlateNum		= substr($_GET['PlateNum'],0,10); }
	if (isset($_GET['PlateComments'])) { 	$paramPlateComments	= substr($_GET['PlateComments'],0,64); }
	if (isset($_GET['Status'])) {		$paramStatus		= substr($_GET['Status'],0,1); }
}

// Create a MySQL connection
$conn = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}


?>

<div class="container m-4">

<div class="row">
<div class="col">
<?php

    // Prepare and execute the query
    $sql = "SELECT c.ID, c.camera_desc, c.camera_ip, z.zone_desc, c.hour_adjust, q, c.last_timestamp, c.last_platenum, c.support_vehiclebrand, c.support_vehiclecolor
			FROM cam_info c
        	INNER JOIN (select camera_ip, count(camera_ip) as q from events group by camera_ip) e ON c.camera_ip = e.camera_ip
			INNER JOIN zones z ON c.zone_ID = z.ID
        	ORDER BY c.camera_desc
			";
    $result = $conn->query($sql);


    // Display the results in a table
    echo "<table class='table table-default'>";
    echo "<tr><th>Descrizione</th><th>IP</th><th>Zona</th><th>H adj</th><th>Brand Supp</th><th>Color Supp</th><th>Targhe rilevate</th><th>Ultima rilevazione</th><th>Ultima targa</th></tr>";

    $total_q = 0;
	while ($row = $result->fetch_object()) {
		$total_q += $row->q;
		
      	echo "<tr>
            	<td>$row->camera_desc</td>
            	<td>$row->camera_ip</td>
            	<td>$row->zone_desc</td>
            	<td>$row->hour_adjust</td>
				<td>$row->support_vehiclebrand</td>
				<td>$row->support_vehiclecolor</td>
		<td align='right'>".number_format($row->q,0,",",".")."</td>
		<td>$row->last_timestamp</td>
		<td>$row->last_platenum</td>
      	    </tr>"; 
    }

	echo "<tr><td></td><td></td><td></td><td></td><td></td><td class='fw-bold' align='right'>Totale:</td><td class='fw-bold' align='right'>".number_format($total_q,0,",",".")."</td></tr>";
    echo "</table>";

     // Close the connection
    $conn->close();

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
