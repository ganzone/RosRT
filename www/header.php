<?php

include "../config.php";
include "logs_utils.php";

session_start();

// Controllo autenticazione utente
if (basename($_SERVER['SCRIPT_NAME']) === "login.php") {
  if (isset($_GET['logout'])) {
   	session_destroy();
		session_start();
	}

	$LOGGED_IN = false;

} else {

  // Se utente non autenticato, redirezione a pagina di login
  if (!isset($_SESSION['username'])) {
	  header("Location: login.php");
	  exit();
	}

  // Se utente autenticato, impostazioni variabili sessione
	$LOGGED_IN = true;
  $SESSION_USERID       = $_SESSION['userID'];
  $SESSION_USERNAME     = $_SESSION['username'];
  $SESSION_NAME         = $_SESSION['name'];
  $SESSION_SURNAME      = $_SESSION['surname'];
  $SESSION_EMAIL        = $_SESSION['email'];
  $SESSION_ROLE         = $_SESSION['role'];
  $SESSION_GROUPID      = $_SESSION['groupID'];
  $SESSION_GROUP        = $_SESSION['group'];
  $SESSION_TELEGRAM_URL = $_SESSION['telegram_url'];

}

?>


<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Rilevazione Targhe - Comune di Rosignano Marittimo</title>

  <link rel="stylesheet" href="css/bootstrap-italia.min.css" />
</head>

<body>

<header class="it-header-wrapper">
  <div class="it-header-slim-wrapper">
    <div class="container">
      <div class="row">
        <div class="col-12">
          <div class="it-header-slim-wrapper-content">
            <!--<a class="d-none d-lg-block navbar-brand" href="#">Comune di Rosignano Marittimo</a>-->
	    <div class="nav-mobile">
<!--
	      <nav aria-label="Navigazione secondaria">
                <a class="it-opener d-lg-none" data-bs-toggle="collapse" href="#menuC1" role="button" aria-expanded="false" aria-controls="menuC1">
                  <span>Comune di Rosignano Marittimo</span>
                  <svg class="icon" aria-hidden="true">
                    <use href="svg/sprites.svg#it-expand"></use>
                  </svg>
                </a>
		<div class="link-list-wrapper collapse" id="menuC1">
                  <ul class="link-list">
                    <li><a class="dropdown-item list-item" href="#">Link 1</a></li>
                    <li><a class="list-item active" href="#" aria-current="page">Link 2 (Attivo)</a></li>
                  </ul>
		</div>
              </nav>
-->
	    </div>
	    <div class="it-header-slim-right-zone">
<!--
	      <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                  <span class="visually-hidden">Selezione lingua: lingua selezionata</span>
                  <span>ITA</span>
                  <svg class="icon d-none d-lg-block">
                    <use href="svg/sprites.svg#it-expand"></use>
                  </svg>
                </a>
                <div class="dropdown-menu">
                  <div class="row">
                    <div class="col-12">
                      <div class="link-list-wrapper">
                        <ul class="link-list">
                          <li><a class="dropdown-item list-item" href="#"><span>ITA <span class="visually-hidden">selezionata</span></span></a></li>
                          <li><a class="dropdown-item list-item" href="#"><span>ENG</span></a></li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
	      </div>
-->
<?php

if ($LOGGED_IN) {
	echo "<div class='nav-item dropdown'>
		<a class='nav-link dropdown-toggle' href='#' data-bs-toggle='dropdown' aria-expanded='false'>
		<div class='btn btn-primary btn-icon btn-sm'>
		<span class='rounded-icon'>
		  <svg class='icon d-none d-lg-block icon-primary'>
		    <use href='svg/sprites.svg#it-user'></use>
		  </svg>
		</span><span>$SESSION_NAME $SESSION_SURNAME</span>
		</div>
		</a>
		<div class='dropdown-menu'>
		  <div class='row'>
		    <div class='col-12'>
		      <div class='link-list-wrapper'>
			<div class='link-list'>
			  <ul class='link-list'>
			    <li><a class='dropdown-item list-item' href='login.php?logout'><span>Esci</span></a></li>
			  </ul>
			</div>
		      </div>
		    </div>
		  </div>
		</div>
	      </div>
	";
}

?>
<!--
	 	  <div class='it-access-top-wrapper'>
		  <a class='btn btn-primary btn-sm' href='login.php?logout'>Esci</a>
-->
           </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="it-nav-wrapper">
    <div class="it-header-center-wrapper">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="it-header-center-content-wrapper">
              <div class="it-brand-wrapper">
                <a href=".">
                  <img src="assets/Rosignano_Marittimo-Stemma.svg" width="80px" />
                  &nbsp;&nbsp;&nbsp;&nbsp;

                  <!--
                  <svg class="icon" aria-hidden="true">
                    <use href="svg/sprites.svg#it-pa"></use>
                  </svg>
                  -->

                  <div class="it-brand-text">
                    <div class="it-brand-title">Comune di Rosignano Marittimo</div>
                    <div class="it-brand-tagline d-none d-md-block">RosRT - Rilevazione Targhe Auto/Moto</div>
                  </div>
                </a>
              </div>
<!--
	      <div class="it-right-zone">
		<div class="it-socials d-none d-md-flex">
                  <span>Seguici su</span>
                  <ul>
                    <li>
                      <a href="#" aria-label="Facebook" target="_blank">
                        <svg class="icon">
                          <use href="svg/sprites.svg#it-facebook"></use>
                        </svg>
                      </a>
                    </li>
                    <li>
                      <a href="#" aria-label="Github" target="_blank">
                        <svg class="icon">
                          <use href="svg/sprites.svg#it-github"></use>
                        </svg>
                      </a>
                    </li>
                    <li>
                      <a href="#" aria-label="Twitter" target="_blank">
                        <svg class="icon">
                          <use href="svg/sprites.svg#it-twitter"></use>
                        </svg>
                      </a>
                    </li>
                  </ul>
                </div>
                <div class="it-search-wrapper">
                  <span class="d-none d-md-block">Cerca</span>
                  <a class="search-link rounded-icon" aria-label="Cerca nel sito" href="#">
                    <svg class="icon">
                      <use href="svg/sprites.svg#it-search"></use>
                    </svg>
                  </a>
                </div>
	      </div>
-->
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="it-header-navbar-wrapper" id="navbar">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <!--start nav-->
            <nav class="navbar navbar-expand-lg has-megamenu" aria-label="Navigazione principale">
              <button class="custom-navbar-toggler" type="button" aria-controls="navC1" aria-expanded="false" aria-label="Mostra/Nascondi la navigazione" data-bs-toggle="navbarcollapsible" data-bs-target="#navC1">
                <svg class="icon">
                  <use href="svg/sprites.svg#it-burger"></use>
                </svg>
              </button>
              <div class="navbar-collapsable" id="navC1" style="display: none;">
                <div class="overlay" style="display: none;"></div>
                <div class="close-div">
                  <button class="btn close-menu" type="button">
                    <span class="visually-hidden">Nascondi la navigazione</span>
                    <svg class="icon">
                      <use href="svg/sprites.svg#it-close-big"></use>
                    </svg>
                  </button>
                </div>
                <div class="menu-wrapper">
                  <ul class="navbar-nav">
		    <li class="nav-item active">
			<a class="nav-link <?php if(basename($_SERVER['SCRIPT_NAME']) === 'index.php') { echo "active"; } ?>" href="index.php" aria-current="page"><span>Ricerca Targhe</span></a>
		    </li>
		    <li class="nav-item active">
			<a class="nav-link <?php if(basename($_SERVER['SCRIPT_NAME']) === 'watchlist.php') { echo "active"; } ?>" href="watchlist.php" aria-current="page"><span>Watch List</span></a>
		    </li>
		    <li class="nav-item active">
			<a class="nav-link <?php if(basename($_SERVER['SCRIPT_NAME']) === 'status.php') { echo "active"; } ?>" href="status.php" aria-current="page"><span>Stato Telecamere</span></a>
		    </li>
		    <li class="nav-item active">
			<a class="nav-link <?php if(basename($_SERVER['SCRIPT_NAME']) === 'logs.php') { echo "active"; } ?>" href="logs.php" aria-current="page"><span>Log</span></a>
		    </li>

<!--
                    <li class="nav-item"><a class="nav-link disabled" href="#" aria-disabled="true"><span>Link 2 (disabilitato)</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><span>Link 3</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><span>Link 4</span></a></li>
                    <li class="nav-item dropdown">
                      <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false" id="mainNavDropdownC1">
                        <span>Menu Dropdown</span>
                        <svg class="icon icon-xs">
                          <use href="svg/sprites.svg#it-expand"></use>
                        </svg>
                      </a>
                      <div class="dropdown-menu" role="region" aria-labelledby="mainNavDropdownC1">
                        <div class="link-list-wrapper">
                          <div class="link-list-heading">Sezione</div>
                          <ul class="link-list">
                            <li><a class="dropdown-item list-item" href="#"><span>Link lista 1</span></a></li>
                            <li><a class="dropdown-item list-item" href="#"><span>Link lista 2</span></a></li>
                            <li><a class="dropdown-item list-item" href="#"><span>Link lista 3</span></a></li>
                            <li><span class="divider"></span></li>
                            <li><a class="dropdown-item list-item" href="#"><span>Link lista 4</span></a></li>
                          </ul>
                        </div>
                      </div>
                    </li>
                    <li class="nav-item dropdown megamenu">
                      <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false" id="mainNavMegamenuC1">
                        <span>Megamenu</span>
                        <svg class="icon icon-xs">
                          <use href="svg/sprites.svg#it-expand"></use>
                        </svg>
                      </a>
                      <div class="dropdown-menu" role="region" aria-labelledby="mainNavMegamenuC1">
                        <div class="row">
                          <div class="col-12 col-lg-4">
                            <div class="link-list-wrapper">
                              <div class="link-list-heading">Sezione 1</div>
                              <ul class="link-list">
                                <li><a class="dropdown-item list-item" href="#"><span>Link lista 1</span></a></li>
                                <li><a class="dropdown-item list-item" href="#"><span>Link lista 2</span></a></li>
                                <li><a class="dropdown-item list-item" href="#"><span>Link lista 3</span></a></li>
                              </ul>
                            </div>
                          </div>
                          <div class="col-12 col-lg-4">
                            <div class="link-list-wrapper">
                              <ul class="link-list">
                                <li>
                                  <div class="link-list-heading">Sezione 2</div>
                                </li>
                                <li><a class="dropdown-item list-item" href="#"><span>Link lista 4</span></a></li>
                                <li><a class="dropdown-item list-item" href="#"><span>Link lista 5</span></a></li>
                                <li><a class="dropdown-item list-item" href="#"><span>Link lista 6</span></a></li>
                              </ul>
                            </div>
                          </div>
                          <div class="col-12 col-lg-4">
                            <div class="link-list-wrapper">
                              <ul class="link-list">
                                <li>
                                  <div class="link-list-heading">Sezione 3</div>
                                </li>
                                <li><a class="dropdown-item list-item" href="#"><span>Link lista 7</span></a></li>
                                <li><a class="dropdown-item list-item" href="#"><span>Link lista 8</span></a></li>
                                <li><a class="dropdown-item list-item" href="#"><span>Link lista 9</span></a></li>
                              </ul>
                            </div>
                          </div>
                        </div>
                      </div>
		    </li>
-->
<?php if ($SESSION_USERNAME === "admin") { ?>
  	</ul>
		<ul class='navbar-nav navbar-secondary'>
			<li class='nav-item'>
			<a class='nav-link <?php if(basename($_SERVER['SCRIPT_NAME']) === 'camlist.php') { echo "active"; } ?>' href='camlist.php' aria-disabled='true'><span>Telecamere</span></a></li>
			<li class='nav-item'>
			<a class='nav-link <?php if(basename($_SERVER['SCRIPT_NAME']) === 'zones.php'  ) { echo "active"; } ?>' href='zones.php' aria-disabled='true' ><span>Zone</span></a></li>
			<li class='nav-item'>
			<a class='nav-link <?php if(basename($_SERVER['SCRIPT_NAME']) === 'users.php'  ) { echo "active"; } ?>' href='users.php' aria-disabled='true' ><span>Utenti</span></a></li>
			<li class='nav-item'>
			<a class='nav-link <?php if(basename($_SERVER['SCRIPT_NAME']) === 'groups.php' ) { echo "active"; } ?>' href='groups.php' aria-disabled='true'><span>Gruppi</span></a></li>
			<li class='nav-item'>
			<a class='nav-link <?php if(basename($_SERVER['SCRIPT_NAME']) === 'settings.php') { echo "active"; } ?> disabled' href='settings.php' aria-disabled='true'><span>Impostazioni</span></a></li>
<?php } ?>
		</ul>
		</div>
              </div>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>
</header>


<?php

if (!$LOGGED_IN) {
	echo "<script>document.getElementById('navbar').style.display = 'none'</script>";
}

?>
