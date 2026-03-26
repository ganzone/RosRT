
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
	If ($SESSION_ROLE == 0) {
        $sql = "SELECT * FROM vw_logs";
    } else {
        $sql = "SELECT * FROM vw_logs WHERE group_desc = '$SESSION_GROUP' AND (category = 'WATCHLIST' or category = 'SEARCH')";
    }
	$result = $conn->query($sql);

    // Display the results in a table
    echo "<table class='table table-default text-nowrap'>";
    echo "<tr><th>Data/Ora</th><th>Utente</th><th>Gruppo</th><th>Categoria</th><th>Descrizione</th></tr>";

    while ($row = $result->fetch_object()) {
      	echo "	<tr>
					<td>$row->timestamp</td>
                    <td>$row->username</td>
                    <td>$row->group_desc</td>
                    <td>$row->category</td>
                    <td>$row->log_text</td>
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
