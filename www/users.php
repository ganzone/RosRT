
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
		<div class="callout warning">
			<div class="callout-inner">
				<div class="callout-title">
					<svg class="icon"><use href="svg/sprites.svg#it-help-circle"></use></svg>
					<span class="visually-hidden">Attenzione</span> 
					<span class="text">Attenzione</span>
				</div>
				<p>Caratteri permessi da TOTP: ABCDEFGHIJKLMNOPQRSTUVWXYZ234567<br />Solo maiuscolo - Min 16 caratteri</p>
				<a href="gauth/index.html"><button type="button" class="btn-secondary">Genera codice QR Google Authenticator</button></a>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row">
<div class="col">

<?php

    // Prepare and execute the query
	$sql = "SELECT u.ID, u.username, u.name, u.surname, u.email, u.role, u.totp, g.ID as group_ID, g.name as group_name
			FROM users u
			INNER JOIN users_groups g ON u.group_ID = g.ID
			ORDER BY u.username";

	$result = $conn->query($sql);


    // Display the results in a table
    echo "<table class='table table-default text-nowrap'>";
    echo "<tr><th>Utente</th><th>Nome</th><th>Cognome</th><th>Gruppo</th><th>Ruolo</th><th>Email</th><th>TOTP Seed</th></tr>";

    while ($row = $result->fetch_object()) {
		if ($row->role == 0) { $strRole = "Admin"; } else { $strRole = "User"; }

      	echo "	<tr>
					<td>$row->username</td>
					<td>$row->name</td>
					<td>$row->surname</td>
					<td>$row->group_name</td>
					<td>$strRole</td>
					<td>$row->email</td>
					<td>$row->totp</td>
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
