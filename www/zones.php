
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
	$sql = "SELECT z.zone_desc
			FROM zones z
			ORDER BY z.zone_desc";

	$result = $conn->query($sql);


    // Display the results in a table
    echo "<table class='table table-default text-nowrap'>";
    echo "<tr><th>Nome Zona</th></tr>";

    while ($row = $result->fetch_object()) {
      	echo "	<tr>
					<td>$row->zone_desc</td>
				</tr>"; 
    }

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
