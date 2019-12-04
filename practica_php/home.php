#!/usr/bin/php-cgi
<?php
// exemple de sessions en bas.udg.edu (cal crear abans tmp dins public_html amb permisos 700)
	$emmagatzemarSessions="/u/alum/u1939691/public_html/tmp";
	ini_set('session.save_path',$emmagatzemarSessions);
	session_start();
	if (empty($_SESSION['user'])){ // mirem si ja està assignada (per quan tornem d'altres pàgines)
		$_SESSION['user']=$_POST['user'];
	}
	if (empty($_SESSION['pwd'])){ 
		$_SESSION['pwd']=$_POST['pwd'];
	}
	if (empty($_SESSION['user']) or empty($_SESSION['pwd'])){
		header('Location: practica_php.html');
		exit;// no seguir carregan la pagina
	}

	$conn = oci_connect($_SESSION['user'], $_SESSION['pwd'], 'oracleps');
	if(!$conn){
		header('Location: practica_php.html');
		exit;// no seguir carregan la pagina
	}
	
?>
<!DOCTYPE html>
<html>
	<head>
		<title>GP UDG - Home</title>
		<link type="text/css" rel="stylesheet" href="gpUdg.css">
		
		
		
	</head>
	<body>
		<h1>Menú GP UDG</h1>
		<ul id="menu">
			<li><a href="altaPersonatge.php">Alta de personatge</a></li>
			<li><a href="crearCursa.php">Nova cursa</a></li>
			<li><a href="inscriureParticipant.php">Inscripcions</a></li>
			<li><a href="veureParticipants.php">Participació curses</a></li>
			<li><a href="tancarCursa.php">Tancar Inscripcions</a></li>
			<li><a href="entraTemps.php">Entrar temps</a></li>
			<li><a href="gestioSinistre.php">Gestionar Sinistres</a></li>
			<li><a href="veureSinistre.php">Veure Sinistres</a></li>
			
		</ul>
		
		<h2> Benvingut al GP de la UDG </h2>
		<div id="contenidorFormAmple">
		<p>Resum: </p>
		<ul>
			<li>Alta de personatge: Dona d'alta un personatge nou per el un usuari. </li>
			<li>Nova cursa: Crear una nova cursa oberta a inscirpcions. </li>
			<li>Inscripcions: Permet que els usuaris inscriguin un dels seus personatges a alguna cursa oberta</li>
			<li>Participació curses: Permet veure quins personatges estan inscrits en una cursa.</li>
			<li>Tancar inscripcions: Permet tancar les inscripcions d'una cursa. </li>
			<li>Entrar temps: Permet entrar els temps de tots els participants inscrits en una cursa </li>
			<li>Gestionar Sinistres: Fa la gestio dels sinistres d'una cursa. Repara tots els vehicles que han abandonat utilitzant tantes eines com la gravetat indiqui per cada vehicle</li>
			<li>Veure sinistres: Permet veure el cost dels sinistres per un usuari concret.</li>
			
		</ul>
		<hr>
		<br/>
		
		
		<p><b>Autor: </b>Francesc Xavier Bullich Parra - u1939691</p>
		<p><b>Curs: </b>GEINF 2n 1 semestre</p>
		<p><b>Professor: </b>Joan Surrell</p>
		<p><b>Grup: </b>2 Dijous 8-10</p>
		
		</div>
		
	<?php 
		oci_close($conn); 
	?>
	</body>
</html>