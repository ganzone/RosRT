
<?php

include "header.php";

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
    $sql = "SELECT c.camera_desc, c.camera_ip, z.zone_desc, c.hour_adjust, q, c.last_timestamp
	FROM cam_info c
        INNER JOIN (select camera_ip, count(camera_ip) as q from events group by camera_ip) e on c.camera_ip = e.camera_ip
        INNER JOIN zones z ON c.zone_ID = z.ID
        ORDER BY zone_desc, c.camera_desc
	      ";
    $result = $conn->query($sql);


    // Display the results in a table
    echo "<table class='table table-default'>";
    echo "<tr><th>Zona</th><th>Telecamera</th><th>Targhe rilevate ($DELETED_DAYS gg)</th><th>Ultima rilevazione</th></tr>";

    $total_q = 0;
    while ($row = $result->fetch_object()) {
      echo "<tr>
            	<td>$row->zone_desc</td>
            	<td>$row->camera_desc</td>
		<td align='right'>".number_format($row->q,"0",",",".")."</td>
		<td>$row->last_timestamp</td>
	    </tr>"; 

      $total_q += $row->q;
    }

    echo "<td></td><td><p class='fw-bold' align='right'>Totale</p></td><td><p class='fw-bold' align='right'>".number_format($total_q,"0",",",".")."</p></td>";

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
