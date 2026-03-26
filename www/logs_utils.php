<?php

// Salva messaggi di log nel database
function DBlog($LogText, $Category) {
    global $conn;

    if (isset($_SESSION['userID'])) {
        $User = $_SESSION['userID'];
    } else {
        $User = null;
    }

    $log_stmt = $conn->prepare("INSERT INTO logs (user_ID, log_text, category) VALUES (?, ?, ?)");
    $log_stmt->bind_param("sss", $User, $LogText, $Category);
    $log_stmt->execute();
    $log_stmt->close();
}

?>