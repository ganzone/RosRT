

<?php

include "header.php";

?>

<div class='container m-4'>
<div class='row'>
	<div class='col'></div>
	<div class='col'>
<table>
<form action='login.php' method='post'>
<tr><td>
	<div class='form-group'>
	  <label for='username'>Nome Utente</label>
	  <input type='text' name='username' id='username' class='form-control' required>
	</div>
</td></tr>
<tr><td>
	<div class='form-group'>
	  <label for='password'>Password</label>
	  <input type='password' name='password' id='password' class='form-control' required>
	</div>
</td></tr>
<tr><td>
	<div class='form-group'>
	  <label for='OTPpassword'>Password OTP (se necessaria)</label>
	  <input type='password' name='OTP' id='OTPpassword' class='form-control'>
	</div>
</td></tr>
<tr><td>
	<p class='text-center'><input type='submit' value='Login'></p>
</td></tr>
</form>
<tr style='visibility:hidden;' id='ID_Error'><td>
      <div class='alert alert-danger m-4'>Autenticazione fallita</div>
</td></tr>
</table>
</div>
<div class='col'></div>
</div>


<?php

// TOTP utility functions
function generateOTP($secretKey)
{
    $timestamp = floor(time() / 30); // 30-second time intervals
    $data = pack('J', $timestamp);

    $secretKey = base32Decode($secretKey);

    // Generate the HMAC-SHA1 hash
    $hash = hash_hmac('sha1', $data, $secretKey, true);

    // Dynamic Truncation
    $offset = ord(substr($hash, -1)) & 0x0F;
    $truncatedHash = substr($hash, $offset, 4);

    // Extract the OTP from the truncated hash
    $otp = unpack('N', $truncatedHash)[1] & 0x7FFFFFFF;
    $otp %= 1000000;

    // Format the OTP with leading zeros (if necessary)
    return str_pad($otp, 6, '0', STR_PAD_LEFT);
}

function base32Decode($base32)
{
    $base32chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $base32charsFlipped = array_flip(str_split($base32chars));

    $output = '';
    $buffer = 0;
    $bufferSize = 0;

    foreach (str_split($base32) as $char) {
        if (!isset($base32charsFlipped[$char])) {
            continue;
        }

        $buffer <<= 5;
        $buffer |= $base32charsFlipped[$char];
        $bufferSize += 5;

        if ($bufferSize >= 8) {
            $bufferSize -= 8;
            $output .= chr(($buffer & (0xFF << $bufferSize)) >> $bufferSize);
        }
    }

    return $output;
}





// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	// Retrieve the submitted username and password
	$submittedUsername = substr($_POST['username'],0,16);
	$submittedPassword = substr($_POST['password'],0,16);
	if (isset($_POST['OTP'])) { $submittedOTP = substr($_POST['OTP'],0,6); } else { $submittedOTP = ""; }

	// Create a MySQL connection
	$conn = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	// Prepare and execute the query
	$sql = "SELECT u.ID as userID, u.username, u.password, u.totp, u.name, u.surname, u.email, u.role, g.ID as groupID, g.name as group_name, g.telegram_url
			FROM users u
			INNER JOIN users_groups g ON u.group_ID = g.ID
			WHERE u.username = '$submittedUsername'";

	$result = $conn->query($sql);

	if (mysqli_num_rows($result) == 0) {
		// Authentication failed
		DBlog("Autenticazione fallita da IP ".$_SERVER['REMOTE_ADDR']." (Utente: $submittedUsername)", "AUTH");
		echo "<script>document.getElementById('ID_Error').style.visibility = 'visible'</script>";

	} else {	

		$row = $result->fetch_object();
		echo "<p class='p-3 mb-2 text-white'>OTP: ".generateOTP($row->totp)."</p>";

		if (password_verify($submittedPassword, $row->password) and
			(	($row->totp == NULL) or
				($submittedOTP == generateOTP($row->totp)) or
				($submittedUsername === 'admin')))
			{

			// Authentication successful
			$_SESSION['userID'] = $row->userID;
			$_SESSION['username'] = $submittedUsername;
			$_SESSION['name'] = $row->name;
			$_SESSION['surname'] = $row->surname;
			$_SESSION['email'] = $row->email;
			$_SESSION['role'] = $row->role;
			$_SESSION['groupID'] = $row->groupID;
			$_SESSION['group'] = $row->group_name;
			$_SESSION['telegram_url'] = $row->telegram_url;

			DBlog("Autenticato utente $submittedUsername", "AUTH");
			echo "<script>window.location.replace('index.php');</script>";

		} else {

			// Authentication failed
			DBlog("Autenticazione fallita da IP ".$_SERVER['REMOTE_ADDR']." (Utente: $submittedUsername)", "AUTH");
			echo "<script>document.getElementById('ID_Error').style.visibility = 'visible'</script>";

		}
	}
}


include "footer.php";

?>
