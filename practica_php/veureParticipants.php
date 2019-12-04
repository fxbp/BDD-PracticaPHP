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
		<h1>Consulta Participants</h1>
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
			$visible2=false;
				
			if(!empty($_POST['cursa']) ){
				$visible=false;
				$visible2=true;
				
				
			}
		?>
		
		<div id="contenidorFormAmple" style=" <?php if(!$visible){echo "display:none;";}  ?> ">
		<form method="post" action="veureParticipants.php" >
			<p><b> Selecciona una cursa</b></p>
				<?php
					$query="select codi, codi || ' --> ' || nom as descrip from curses";
					$select=oci_parse($conn,$query);
					oci_execute($select);
					ompleCombo($select,'cursa','curses',true,true);
					oci_free_statement($select);
				?>
			<input type="submit" value="Veure participants" />
		</form>
		</div>
		
		<div id="contenidorFormAmple" style="  <?php if(!$visible2){echo "display:none;";} ?>">
			<p><b>Participants de la cursa <?php echo $_POST['cursa']; ?> </b></p>
			
			<table class="llista">
			
				<tr>
					<th>Usuari</th>
					<th>Personatge</th>
					<th>Vehicle</th>
					<th>Temps</th>
				</tr>
				<?php
					$query="select u.alias as nom, p.alias, c.vehicle, ".
						"case when iniciReal is not null then ".
						" nvl(TO_CHAR (TRUNC (SYSDATE) + NUMTODSINTERVAL (temps*3600, 'second'),'hh24:mi:ss')  ,'Abandonat') else ".
						"	TO_CHAR (TRUNC (SYSDATE) + NUMTODSINTERVAL (temps*3600, 'second'),'hh24:mi:ss') end as temps ".
						"from usuaris u inner join personatges p  on u.alias=p.usuari ".
						"inner join participantsCurses c on p.alias=c.personatge inner join curses on c.cursa=curses.codi ".
						"where c.cursa='".$_POST['cursa']."'";
			
					$select=oci_parse($conn,$query);
					oci_execute($select);
					
					$i=0;
					while(($row=oci_fetch_array($select,OCI_ASSOC+OCI_RETURN_NULLS)) !==false){
						if($i%2==0)
							$classname="evenRow";
						else
							$classname="oddRow";
					
						echo '<tr class="'.$classname.'" >';
						foreach ($row as $item) {
							echo "<td>".($item !== null ? htmlentities($item, ENT_QUOTES) : "&nbsp;")."</td>\n";
						}
						echo "</tr>\n";
									
					$i++; 
					}
				?>
			</table>
			
			
	
		</div>
		
		<?php 
			oci_close($conn);
		?>
		
	</body>
</html>