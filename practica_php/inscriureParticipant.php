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
		<h1>Inscriure un personatge a una cursa</h1>
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
			
			$visiblePrimer= true;
			$visibleSegon=false;
			
			if(!empty($_POST['personatge'])){
				$visiblePrimer=false;
				//els camps personatge i vehicle son obligatoris, comprovar que no existeixin ja per una cursa
				
				$queryPerson="select count(*) as NUM from participantsCurses where cursa='".$_POST['cursa']."' and personatge='".$_POST['personatge']."' ";
				$queryCar="select count(*) as NUM from participantsCurses where cursa='".$_POST['cursa']."' and vehicle='".$_POST['vehicle']."' ";
	
				$selPerson=oci_parse($conn,$queryPerson);
				$selCar=oci_parse($conn,$queryCar);
				
				$existeixPerson=exist($selPerson,'NUM');
				oci_free_statement($selPerson);
								
				$existeixCar=exist($selCar,'NUM');
				oci_free_statement($selCar);
												
				if($existeixPerson){
					$visibleSegon=true;
					echo '<p id="error">El personatge ja està inscrit a la cursa</p>';
				}
				
				if($existeixCar){
					$visibleSegon=true;
					echo '<p id="error">El vehicle ja està inscrit a la cursa </p>';
				}
				
				if(!$existeixCar and !$existeixPerson){
					
					$query="insert into participantsCurses(cursa, vehicle, personatge) values('".$_POST['cursa']."', ".
							"'".$_POST['vehicle']."', '".$_POST['personatge']."' )";
					$insert=oci_parse($conn, $query);
					$result=oci_execute($insert);
					
					
					if($result){
						//descomptar saldo
						$query="update usuaris set saldo=saldo - (select inscripcio from curses where codi='".$_POST['cursa']."')".
								" where alias='".$_POST['usuari']."'";
						$update=oci_parse($conn, $query);
						oci_execute($update);
						oci_free_statement($update);
						
						echo '<div id="contenidorFormAmple" >';
						echo "<p>S'ha realitzat la següent inscripció:</p>";
						echo "<ul>";
						echo "<li><b>Codi cursa: </b>".$_POST['cursa']."</li>";
						echo "<li><b>Usuari: </b>".$_POST['usuari']."</li>";
						echo "<li><b>Personatge: </b>".$_POST['personatge']."</li>";
						echo "<li><b>Vehicle: </b>".$_POST['vehicle']."</li>";
						echo "</ul>";
						echo "</div>";
						
					}
					else{
						$e=oci_error($insert);
						echo $e['message'];
					}
					
				}
			}
			elseif(!empty($_POST['cursa']) and $_POST['cursa']!=='blank' and !empty($_POST['usuari']) and $_POST['usuari']!=='blank' ){
				$visiblePrimer=false;
				$visibleSegon=true;		

				$query="select count(*) as NUM from usuaris where alias='".$_POST['usuari']."' and ".
				"saldo < (select inscripcio from curses where codi='".$_POST['cursa']."' )";
				$selSaldo=oci_parse($conn,$query);
				$noSaldo=exist($selSaldo,'NUM');
				
				if($noSaldo){
					$visiblePrimer=true;
					$visibleSegon=false;
					
					echo '<p id="error">L\'usuari que has triat no te prou saldo</p>';
				}
			}
				
			
			
		?>
		
		<div id="contenidorFormAmple" style="  <?php if(!$visiblePrimer){echo "display:none";} ?>;">
			<form method="post" action="inscriureParticipant.php">
				<p><b>Selecciona la cursa i l'usuari que es vol inscriure:</b></p>
				<table class="formulari">
					<tr>
						<th>Cursa</th>
						<td>
						<?php
							$curses="select codi , codi || '  -->  ' || nom as cursa from curses where iniciReal is null or iniciReal > to_date('".date("m/d/Y")."', 'MM/dd/YYYY')";
							$nomCurses=oci_parse($conn,$curses);
							oci_execute($nomCurses);
							ompleCombo($nomCurses,'cursa','curses',true,true);
							oci_free_statement($nomCurses);
						?>
						</td>
						</tr>
						<tr>
						<th>Usuari</th>
						<td>
						<?php
							$usuari="select alias, alias || '  -->  ' || nom ||' ' || cognoms as nom from usuaris";
							$nomUsuari=oci_parse($conn,$usuari);
							oci_execute($nomUsuari);
							ompleCombo($nomUsuari,'usuari','usuaris',true,true);
							oci_free_statement($nomUsuari);
						?>
						</td>

					</tr>
					
				</table>
				<input type="submit" value="Triar participant" />
			</form>
		</div>
		<div id="contenidorFormAmple" style="  <?php if(!$visibleSegon){echo "display:none";} ?>;">
			<form method="post" action="inscriureParticipant.php">
				<p><b>Selecciona el personatge i el vehicle:</b></p>
				<table class="formulari">
					<tr>
						<th>Personatge</th>
						<td>
						<?php
							$queryPerson="select alias , alias || ' --> ' || tipusPersonatge as descrip from personatges where usuari ='".$_POST['usuari']."' ";
							$selPerson=oci_parse($conn,$queryPerson);
							oci_execute($selPerson);
							ompleCombo($selPerson,'personatge','personatges',true,true);
							oci_free_statement($selPerson);
						?>
						</td>
						</tr>
						<tr>
						<th>Vehicle</th>
						<td>
						<?php
							$queryCar="select codi, codi || '  -->  ' || descripcio as descrip from vehicles where propietari = '".$_POST['usuari']."'";
							$selCar=oci_parse($conn,$queryCar);
							oci_execute($selCar);
							ompleCombo($selCar,'vehicle','vehicles',true,true);
							oci_free_statement($selCar);
						?>
						</td>

					</tr>
					
				</table>
				<input type="hidden" name="usuari" value="<?php echo $_POST['usuari']; ?>" />
				<input type="hidden" name="cursa" value="<?php echo $_POST['cursa']; ?>" />
				<input type="submit" value="Inscriure" />
			</form>
		</div>
		
		
		
		<?php 
			oci_close($conn);
		?>
		
	</body>
</html>