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
		<h1>Alta de personatge</h1>
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
			
			if(!empty($_POST['person']) ){
				
				$sentencia= 'INSERT INTO personatges(alias,despesaMensual, dataCreacio, usuari, tipusPersonatge) values(\''.$_POST['person'].'\','.$_POST['despesa'].',to_date(\''.$_POST['creacio'].'\', \'MM/dd/YYYY\'), \''.$_POST['usuari'].'\', case when \''.$_POST['tipusPerson'].'\' =\'blank\' then null else \''.$_POST['tipusPerson'].'\' end ) ';
				$insert=oci_parse($conn, $sentencia);
				$result=oci_execute($insert);
				
				if(!$result){
					$e=oci_error($insert);
					echo "<p id=\"error\">El personatge ja existeix. Entra un altre alias.</p>\n";
					
				}
				else{
					$visible=false;
					
					echo '<div id="contenidorFormAmple" >';
					
					echo "<p> L'usuari ".$_POST['usuari']." ha creat el nou personatge d'alias: ".$_POST['person']."</p>";
					
					echo '</div>';
					
					
				}
				
				
				oci_free_statement($insert);
				
			}
			elseif(empty($_POST['usuari'])){
				$visible=false;
				echo "<p> <b>Selecciona l'usuari al qual vols crear un nou personatge </b> </p>";
								
				$stid=oci_parse($conn, 'select alias from usuaris');
				oci_execute($stid);
				
				echo "<form method=\"post\" action=\"altaPersonatge.php\">\n";
				
				ompleCombo($stid,'usuari','usuaris');
				
				echo "<input type=\"submit\" value=\"Mostra\" />";
				echo "</form>";
				
				oci_free_statement($stid);
			}
			
			
			if(!empty($_POST['usuari']) && $_POST['usuari'] != 'blank' && $visible){
				echo '<div id="extern" >';
				echo '<div id="contenidorDades" >';
				echo "<p>Dades de l'usuari: </p>";
				$sdades=oci_parse($conn, 'select alias, nom, cognoms, saldo from usuaris where alias = \''.$_POST['usuari'].'\'');
				oci_execute($sdades);
				
				echo "<table class=\"llista\">\n";
				echo "<tr>\n";
				echo "<th>Alias</th><th>Nom</th><th>Cognoms</th><th>Saldo</th>\n";
				echo "<tr>\n";
				while (($row = oci_fetch_array($sdades, OCI_ASSOC+OCI_RETURN_NULLS)) != false) {
					 echo "<tr>\n";
					 foreach ($row as $item) {
						 echo "<td>".($item !== null ? htmlentities($item, ENT_QUOTES) : "&nbsp;")."</td>\n";
						
					}
					echo "</tr>\n";
				} 
				echo "</table>";
				oci_free_statement($sdades);
				echo "</div>";
				echo '<div id="contenidorDades" >';
				
				echo "<p>Entra les dades del nou personatge</p>";
				
				echo "<form method=\"post\" action=\"altaPersonatge.php\">\n";
				echo "<table class=\"formulariMig\">\n";
				echo "<tr>\n";
				echo "<th>Alias</th><td><input type=\"text\" name=\"person\" size=\"15\" required=\"required\" value=\"".$_POST['person']."\"/> </td>";
				echo "</tr><tr>\n";
				echo "<th>Despesa Mensual</th><td><input type=\"number\" name=\"despesa\" size=\"8\" min=\"0\" max=\"99999\" step=\"any\" value=\"".(empty($_POST['despesa'])?0:$_POST['despesa'])."\" > </td>";
				echo "</tr><tr>\n";
				echo "<th>Data creació</th><td><input type=\"date\" name=\"creacio\" size=\"15\" value=\"".(empty($_POST['creacio'])?date("m/d/Y"):$_POST['creacio'])."\"/> </td>";
				echo "</tr><tr>\n";
				
				echo "<th>Tipus personatge</th><td>";
				$queryTipus='select nom from tipusPersonatges';
				$selectTipus=oci_parse($conn, $queryTipus);
				oci_execute($selectTipus);
				ompleCombo($selectTipus,'tipusPerson','tipusPersons',false,false);
				
				echo "</td>";
				
				echo "</tr>\n";
				echo "</table> \n";
				echo "<input type=\"hidden\" name=\"usuari\" value=\"".$_POST['usuari']."\" />";
				echo "<input type=\"submit\" value=\"Crea personatge\" /> \n";
				$_POST['usuari']=$_POST['usuari'];
				echo "</form>";
				echo '</div>';
				echo '</div>';
				oci_free_statement($sdades);
				oci_free_statement($tipus);
				
			}
			
			oci_close($conn);

		?>
	
		
		
		
	</body>
</html>