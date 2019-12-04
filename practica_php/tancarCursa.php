#!/usr/bin/php-cgi
<?php
// exemple de sessions en bas.udg.edu (cal crear abans tmp dins public_html amb permisos 700)
$emmagatzemarSessions="/u/alum/u1939691/public_html/tmp";
ini_set('session.save_path',$emmagatzemarSessions);
session_start();

include("functions.php"); 

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
<html>
	<head>
		<link type="text/css" rel="stylesheet" href="gpUdg.css">
		<script src="//code.jquery.com/jquery-1.10.2.js"></script>
		<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
		<script>
			$(function(){
				 // Find any date inputs and override their functionality
				 $('input[type="date"]').datepicker();
				 $('input[type="date"]').addClass('date').attr('type','text');
			});
		</script>
		<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
	</head>
	<body>
		<h1>Tancar cursa</h1>
		<ul id="menu">
			<li><a href="home.php">Menu</a></li>
			<li><a href="altaPersonatge.php">Alta de personatge</a></li>
			<li><a href="crearCursa.php">Nova cursa</a></li>
			<li><a href="inscriureParticipant.php">Inscripcions</a></li>
			<li><a href="veureParticipants.php">Participació curses</a></li>
			<li><a href="tancarCursa.php">Tancar Inscripcions</a></li>
			<li><a href="entraTemps.php">Entrar temps</a></li>
			<li><a href="gestioSinistre.php">Gestionar Sinistres</a></li>
			<li><a href="veureSinistre.php">Veure Sinistres</a></li>
			
		</ul>
		
		
		<?php
			
			$visible=true;
				
			if(!empty($_POST['idCursa']) ){
				$visible=false;
				
				$query="update curses set iniciReal= to_date('".$_POST['dataFi']."', 'MM/dd/YYYY' ) where codi='".$_POST['idCursa']."'";
				$update=oci_parse($conn,$query);
				oci_execute($update);
				oci_free_statement($update);
				
				echo '<div id="contenidorFormAmple">';
				echo "<p>Cursa ".$_POST['idCursa']. " actualitzada</p>";
				echo "</div>";
			}
		?>
		
		<div id="contenidorFormAmple" style=" visibility: <?php if($visible){echo "visible";}else{echo "hidden";} ?>;">
			
			<p><b>Selecciona una cursa i indica l'hora d'inici real. (No s'admetran mes participants passada la data.)</b></p>
			<form method="post" action="tancarCursa.php">
				<table class="formulari">
					<tr>
						<th>Cursa</th>
						<td>
							<?php
								$query="select codi, codi || ' --> ' || nom as descrip from curses where iniciReal is null";
								$select=oci_parse($conn,$query);
								oci_execute($select);
								ompleCombo($select,'idCursa','curses',true,true);
								oci_free_statement($select);
							?>
						</td>
					</tr>
					<tr>
						<th>Data</th>
						<td><input type="date" name="dataFi" required="required" value="<?php echo date("m/d/Y"); ?>" /> </td>
					</tr>
					
					
				</table>
				<input type="submit" value="Tancar cursa" />
			</form>
		</div>
		
		<?php 
			oci_close($conn);
		?>
		
	</body>
</html>