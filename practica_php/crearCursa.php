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
<!DOCTYPE html>
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
		<h1>Crear una nova cursa</h1>
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
			
			$visible= true;
				
			if(!empty($_POST['idCursa']) ){
				$visible=false;
				
				$data=$_POST['inici'];
				
				if(empty($data)){
					$data='blank';
				}
						
				$queryId="Select count(codi) as NUM from curses where codi='".$_POST['idCursa']."'";
				$existeix=false;
				$select=oci_parse($conn,$queryId);
				$existeixCodi=exist($select,'NUM');
					
				if($existeixCodi){
					$visible=true;
					echo "<p id=\"error\">El codi de la cursa ja existeix.</p>\n";
				}
				else{
				
					$sentencia="INSERT INTO curses (codi, nom, premi, inscripcio, iniciPrevist)".
					" values( '".$_POST['idCursa']."','".$_POST['nomCursa']."',".$_POST['premi'].",".$_POST['preu'].
					", case when '".$data."' = 'blank' then null else to_date('".$_POST['inici']."', 'MM/dd/YYYY' ) end  )";
					
					$insert=oci_parse($conn, $sentencia);
					
					$result=oci_execute($insert);
					
					if(!$result){
						$visible=true;
						$error=oci_error($insert);
						echo $error['message']."\n";
						echo $sentencia."\n";
						
					}
					else{
						echo '<div id="contenidorFormAmple" >';
						echo "<p>S'ha creat la seguent cursa:<p>";
						echo "<ul>";
						echo "<li><b>Codi: </b>".$_POST['idCursa']."</li>";
						echo "<li><b>Nom: </b>".$_POST['nomCursa']."</li>";
						echo "<li><b>Premi: </b>".$_POST['premi']."</li>";
						echo "<li><b>Preu inscripció: </b>".$_POST['preu']."</li>";
						echo "<li><b>Inici previst: </b>".$_POST['inici']."</li>";
						echo "</ul>";
						echo "</div>";
					}
					
					oci_free_statement($insert);
				}
				
				oci_free_statement($select);
			}
		?>
		
		<div id="contenidorFormAmple" style=" visibility: <?php if($visible){echo "visible";}else{echo "hidden";} ?>;">
			<form method="post" action="crearCursa.php">
				<table class="formulari">
					<tr>
						<th>Codi</th>
						<td><input type="text" maxlength="15" name="idCursa" required="required" value="<?php echo $_POST['idCursa'] ?>" /> </td>
					</tr>
					<tr>
						<th>Nom</th>
						<td><input type="text" maxlength="45" name="nomCursa" required="required" value="<?php echo $_POST['nomCursa'] ?>" /> </td>
					</tr>
					<tr>
						<th>Premi</th>
						<td><input type="number"  name="premi"  min="0" max="99999" step="1" required="required" value="<?php if(empty($_POST['premi'])){echo "10";}else{echo $_POST['premi'];} ?>" /> </td>
					</tr>
					<tr>
						<th>Inscripcio</th>
						<td><input type="number"  name="preu"  min="0" max="99999" step="any" required="required" value="<?php if(empty($_POST['preu'])){echo "1";}else{echo $_POST['preu'];} ?>" /> </td>
					</tr>
					<tr>
						<th>Inici Previst</th>
						<td><input type="date"  name="inici" value="<?php if(empty($_POST['inici'])){echo date("m/d/Y");}else{echo $_POST['inici'];} ?>" /> </td>
					</tr>
				</table>
				<input type="submit" value="Crear cursa" />
			</form>
		</div>

		
		
		<?php 
			oci_close($conn);
		?>
		
	</body>
</html>