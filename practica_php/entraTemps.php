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
		<h1>Entrar Temps a Participants</h1>
		<ul id="menu">
			<li><a href="home.php">Menu</a></li>
			<li><a href="altaPersonatge.php">Alta de personatge</a></li>
			<li><a href="crearCursa.php">Nova cursa</a></li>
			<li><a href="inscriureParticipant.php">Inscripcions</a></li>
			<li><a href="veureParticipants.php">Participació curses</a></li>
			<li><a href="tancarCursa.php">Tancar Inscripcions</a></li>
			<li><a href="entraTemps.php">Entra Temps</a></li>
			<li><a href="gestioSinistre.php">Gestionar Sinistres</a></li>
			<li><a href="veureSinistre.php">Veure Sinistres</a></li>
						
			
		</ul>
		
		
		<?php
			
			$visible= true;
			$visible2=false;
				
			if(!empty($_POST['cursa']) ){
				$visible=false;
				$visible2=true;
				
				
			}
			elseif(!empty($_POST['race'])){
				$visible=false;
				
				$min=-1;
				while( list($key, $value) = each($_POST["temps"])){
					list($cursa, $usuari, $personatge, $vehicle)=split(";",$key);
					
					
				
					if(!empty($value)){
						list($hora, $minut, $segon)=split(":",$value);
						$segon=$segon/3600.0;
						$minut=$minut/60.0;
						$hores=$hora+$minut+$segon;
						
						if($min==-1 or $hores<$min){
							$min=$hores;
						}
						
						$query="update participantsCurses set temps=Round(".$hores.",2) ".
								" where cursa='".$cursa."' and vehicle='".$vehicle."' and personatge='".$personatge."'";
								
						$update=oci_parse($conn,$query);
						oci_execute($update);
						
					}
					
				}
				if($min !== -1){
					$queryCursa="update curses set millorTemps=".$min." where codi='".$_POST['race']."' and (millorTemps is null or millorTemps>".$min.") ";
					$updateCursa=oci_parse($conn,$queryCursa);
					oci_execute($updateCursa);
				}
				oci_free_statement($update);
				
			
				echo '<div id="contenidorFormAmple" </div>';
				echo "<p>S'han actualitzat els temps de la cursa: <b>".$cursa."</b>";
				echo "</div>";
			}
		?>
		
		<div id="contenidorFormAmple" style=" <?php if(!$visible){echo "display:none;";}  ?> ">
		<form method="post" action="entraTemps.php" >
			<p><b> Selecciona una cursa</b></p>
				<?php
					$query="select codi, codi || ' --> ' || nom as descrip from curses where iniciReal is not null";
					$select=oci_parse($conn,$query);
					oci_execute($select);
					ompleCombo($select,'cursa','curses',true,true);
					oci_free_statement($select);
				?>
			<input type="submit" value="Veure participants" />
		</form>
		</div>
		
		<div id="contenidorFormAmple" style="  <?php if(!$visible2){echo "display:none;";} ?>">
			<p>Posa els temps als participants. Si no hi ha temps a un participant es considerara abandonat</p>
			<form method="post" action="entraTemps.php">
			<table class="llista">
			
				<tr>
					<th>Usuari</th>
					<th>Personatge</th>
					<th>Vehicle</th>
					<th>Temps</th>
				</tr>
				<?php
					$query="select u.alias as nom, p.alias, c.vehicle ".
						
						"from usuaris u inner join personatges p  on u.alias=p.usuari ".
						"inner join participantsCurses c on p.alias=c.personatge ".
						"where c.cursa='".$_POST['cursa']."' and c.temps is null";
			
					$select=oci_parse($conn,$query);
					oci_execute($select);
					
					$i=0;
					while(($row=oci_fetch_array($select,OCI_ASSOC+OCI_RETURN_NULLS)) !==false){
						if($i%2==0)
							$classname="evenRow";
						else
							$classname="oddRow";
					
						echo '<tr class="'.$classname.'" >';
						$valor=$_POST['cursa'];
						foreach ($row as $item) {
							echo "<td>".($item !== null ? htmlentities($item, ENT_QUOTES) : "&nbsp;")."</td>\n";
							$valor=$valor.";".($item !== null ? htmlentities($item, ENT_QUOTES) : "&nbsp;");
						}
						
						echo '<td><input type="text" name="temps['.$valor.']" pattern="(?:[01]|2(?![4-9])){1}\d{1}:[0-5]{1}\d{1}:[0-5]{1}\d{1}" placeholder="hh:mm:ss" /> </td>';
						
						echo "</tr>\n";
									
					$i++; 
					}
				?>
			</table>
				<input type="hidden" name="race" value="<?php echo $_POST['cursa'].";"; ?> "/>
				<input type="submit" value="Actualitza temps" />
			</form>
			
			
	
		</div>
		
		<?php 
			oci_close($conn);
		?>
		
	</body>
</html>