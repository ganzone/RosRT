
<?php

include "header.php";

$QueryMode = "LIST";
if (isset($_GET['q']) and ($_GET['q'] === "d")) { $QueryMode = "DELETE"; }
if (isset($_GET['q']) and ($_GET['q'] === "e")) { $QueryMode = "EDIT"; }
if (isset($_GET['q']) and ($_GET['q'] === "a")) { $QueryMode = "ADD"; }
if ($QueryMode != "LIST") {
	if (isset($_GET['ID'])) {				$paramID 			= substr($_GET['ID'],0,36); }
	if (isset($_GET['PlateNum'])) {			$paramPlateNum		= substr($_GET['PlateNum'],0,10); }
	if (isset($_GET['PlateComments'])) {	$paramPlateComments	= substr($_GET['PlateComments'],0,64); }
	if (isset($_GET['Status'])) {			$paramStatus		= substr($_GET['Status'],0,1); }
}
if (isset($_GET['show']) and ($_GET['show'] === "all")) { $paramShowAll = true; } else { $paramShowAll = false; }

// Create a MySQL connection
$conn = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}


# Aggiunge riga in watchlist
if ($QueryMode === "ADD") {
	$sql = "INSERT INTO watchlist (plate_num, comments, added_by)
	VALUES ('$paramPlateNum', '$paramPlateComments', '$SESSION_USERNAME')";
    
	$conn->query($sql);
	DBlog("Aggiunta targa $paramPlateNum ($paramPlateComments)", "WATCHLIST");
}


# Cancella riga in watchlist se utente appartiene al gruppo
if ($QueryMode === "DELETE") {
	$sql = "SELECT g.name as group_desc, w.plate_num
			FROM watchlist w
			INNER JOIN users u ON u.username = added_by
			INNER JOIN users_groups g ON u.group_ID = g.ID
			WHERE w.ID = '$paramID'
			";
	$result = $conn->query($sql);
	$row = $result->fetch_object();
	$plate_num = $row->plate_num;

	if (($row->group_desc === $SESSION_GROUP) or ($SESSION_USERNAME === "admin")) {
		$sql = "DELETE FROM watchlist WHERE ID = '$paramID'";

		$result = $conn->query($sql);
		DBlog("Eliminata targa $plate_num", "WATCHLIST");
	}
}


# Modifica riga in watchlist se utente appartiene al gruppo
if ($QueryMode === "EDIT") {
	$sql = "SELECT g.name as group_desc, w.plate_num
			FROM watchlist w
			INNER JOIN users u ON u.username = added_by
			INNER JOIN users_groups g ON u.group_ID = g.ID
			WHERE w.ID = '$paramID'
			";
	$result = $conn->query($sql);
	$row = $result->fetch_object();
	$plate_num = $row->plate_num;

	if (($row->group_desc === $SESSION_GROUP) or ($SESSION_USERNAME === "admin")) {
		$sql = "UPDATE watchlist SET
		comments = '$paramPlateComments',
		status = $paramStatus
		WHERE ID = '$paramID'";

		$conn->query($sql);
		DBlog("Modificata targa [Targa=$plate_num , Stato=$paramStatus , Commento=$paramPlateComments]", "WATCHLIST");
	}
}

?>


<div class="container m-4">
<form method="GET" action="">
<input type="hidden" name="q" value="a">
<div class="row">

	<div class="col">
		<div class="form-group">
			<div class="input-group">
			<label for='PlateNum'>Targa auto/moto</label>
			<input type='text' class='form-control' id='PlateNum' name='PlateNum' size='64' aria-describedby='PlateNumDesc'>
			</div>
			<small id="PlateNumDesc" class="form-text">Esempi: AB123CD , 123 , AB*CD, 1?3</small>
		</div>
	</div>
	<div class="col">
		<div class="form-group">
			<label for="PlateComments">Descrizione</label>
			<input type='text' id='PlateComments' name='PlateComments' maxlength='64'>
		</div>
	</div>
	<div class="col">
		<button type="submit" class="btn btn-primary">Aggiungi</button>
	</div>

	</form>

	<script>
		function cbShowAll_OnClick() {
			if (document.getElementById("cbShowAll").checked) {
				location.href = location.pathname + "?show=all";
			} else {
				location.href = location.pathname + "";
			}
		}
	</script>

	<div class="col">
		<div class="form-check">
			<input type="checkbox" id="cbShowAll" onclick="cbShowAll_OnClick();" <?php if ($paramShowAll) { echo "checked='checked'"; } ?> >
			<label for="cbShowAll">Mostra tutti i gruppi</label>
		</div>
	</div>

</div> <!-- Row -->

<div class="row">
<div class="col">
<?php

    // Prepare and execute the query
    if ($paramShowAll) { $stmtShowAll = ""; } else { $stmtShowAll = "WHERE g.name = '$SESSION_GROUP'"; }

    $stmt = $conn->prepare("
	SELECT w.ID, plate_num, comments, date_added, added_by, status, g.name
	FROM watchlist w
	INNER JOIN users u ON u.username = added_by
	INNER JOIN users_groups g ON u.group_ID = g.ID
	$stmtShowAll
	ORDER BY g.name, date_added
	DESC
	");
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($resultID, $resultPlateNum, $resultComments, $resultDateAdded, $resultAddedBy, $resultStatus, $resultGroupName);


    // Display the results in a table
    echo "<table class='table table-default'>";
    echo "<tr><th>Targa</th><th>Commento</th><th>Aggiunta il</th><th>Aggiunta da</th><th>Gruppo</th><th>Stato</th><th></th><th></th></tr>";

    $result_num = 1;
    while ($stmt->fetch()) {
      if ($result_num >= $MAX_RESULTS) { break 1; } else { $result_num++; }

      echo "<tr>
            	<td><p class='text text-uppercase font-monospace fw-bold'>$resultPlateNum</p></td>
            	<td>$resultComments</td>
            	<td>$resultDateAdded</td>
            	<td>$resultAddedBy</td>
				<td>$resultGroupName</td>";
      if ($resultStatus) {
	      echo "<td><svg class='icon icon-success'><title>Abilitata</title><use href='svg/sprites.svg#it-check-circle'></use></svg></td>";
      } else {
	      echo "<td><svg class='icon icon-danger'><title>Disabilitata</title><use href='svg/sprites.svg#it-close-circle'></use></svg></td>";
      }

	  
	# Mostra tasti di modifica solo per le righe appartenenti allo stesso gruppo
    if (($SESSION_USERNAME === "admin") or ($SESSION_GROUP === $resultGroupName)) {
	    echo "<td>
		    	<a href='#' onclick=\"openModal('$resultID', '$resultPlateNum', '$resultComments', $resultStatus);return false;\" title='Modifica'>
			<svg class='icon icon-primary'><use href='svg/sprites.svg#it-pencil'></use></svg>
			</a>
                  </td>
		  <td><!--<a href='?q=d&ID=$resultID' title='Elimina'>-->
			<a href='#' onclick=\"openDeleteModal('$resultPlateNum','$resultID');\" title='Elimina'>
                        <svg class='icon icon-primary'><use href='svg/sprites.svg#it-delete'></use></svg></a>
                  </td>";
    } else {
	    echo "<td></td><td></td>";
    }

      echo "</tr>"; 
    }

    echo "</table>";

     // Close the statement and connection
    $stmt->close();
    $conn->close();

?>


<!-- Edit Modal Window -- START -->
<script>

    function openModal(PlateID, PlateNum, PlateComments, PlateStatus) {
		var modalWindow = new bootstrap.Modal(document.getElementById('EditModalWindow'));
		modalWindow.show();
		
		document.getElementById('ModalPlateID').value = PlateID;
		document.getElementById('ModalPlateNum').innerHTML = PlateNum;
		document.getElementById('ModalPlateComments').value = PlateComments;
		document.getElementById('ModalPlateStatus').checked = PlateStatus;
    }


    function saveModal() {
		PlateID = document.getElementById('ModalPlateID').value;
		PlateComments = document.getElementById('ModalPlateComments').value;
		if (document.getElementById('ModalPlateStatus').checked) {
			PlateStatus = 1;
		} else {
			PlateStatus = 0;
		}

		window.location.href = '?q=e&ID=' + PlateID + "&PlateComments=" + PlateComments + "&Status=" + PlateStatus;
    }

</script>



<div class="modal fade" id="EditModalWindow" tabindex="-1" aria-labelledby="ModalPlateNum" aria-hidden="true" role="dialog">
<div class="modal-dialog modal-dialog-centered modal-l" role="document">
<div class="modal-content">

	<div class="modal-header">
		<h2 class="modal-title h5">Targa: <span id="ModalPlateNum"></span></h2>
		<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
	</div>
	<div class="modal-body">
	<div class="container m-4">
	<form method="GET" action="">
		<input type="hidden" id="ModalPlateID" name="ID">
		<div class="form-group">
			<label for="ModalPlateComments" class="active">Commento:</label>
			<input type="text" id="ModalPlateComments" class="form-control">
		</div>
		<div class="form-check col-6">
		  <div class="toggles">
			<label for="ModalPlateStatus">Stato:
			<input type="checkbox" id="ModalPlateStatus">
			<span class="lever">
			</label>
		  </div>
		</div>
	</form>
	</div>
	
	</div>
	<div class='modal-footer'>
		<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
		<a href="#" onclick="saveModal();return false;"><button type="button" class="btn btn-primary">Salva</button></a>
	</div>
</div>
</div>
</div>
<!-- Edit Modal Window -- END -->


<!-- Confirm Delete Modal Window -- START -->
<script>

    function openDeleteModal(PlateNum, ID) {
	var modalWindow = new bootstrap.Modal(document.getElementById('DeleteModalWindow'));
	modalWindow.show();

	document.getElementById('deletePlateTitle').innerHTML = PlateNum;
	document.getElementById('DeleteButton').onclick = function() { window.location.href = "?q=d&ID=" + ID } ;
    }

</script>


<div class="modal popconfirm-modal fade" tabindex="-1" role="dialog" id="DeleteModalWindow" aria-labelledby="DeleteModalWindowTitle">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
	<h5 class="modal-title" id="DeleteModalWindowTitle">Eliminazione targa <span id="deletePlateTitle"></span></h5>
      </div>
      <div class="modal-body">
        <p>Sei sicuro ?</p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-dismiss="modal">Annulla</button>
        <button class="btn btn-primary btn-sm" type="button" id="DeleteButton">Elimina</button>
      </div>
    </div>
  </div>
</div>
<!-- Confirm Delete Modal Window -- END -->


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
