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
		<h1>Gestionar Sinistres</h1>
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
				
				$querySinistres="insert into sinistres(codi,cursa,vehicle,personatge,dataReparacio,gravetat) ".
									"select sinistres_seq.nextval, p.cursa, p.vehicle, p.personatge, sysdate as dataReparacio, ".
									"			case when s.totalSinistre is null then 1 when s.totalSinistre=1 then 2 else 3 end as gravetat ".
									"from participantsCurses p left outer join ". 
									"										(select vehicle, count(vehicle) as totalSinistre ".
									"										from sinistres ".
									"										group by vehicle) s ". 
									"	on p.vehicle=s.vehicle ".
									"where p.cursa='".$_POST['cursa']."' and p.temps is null";
				
				$insertSinistres=oci_parse($conn,$querySinistres);
				oci_execute($insertSinistres);
				
				$queryLinies="insert into sinistres_linies(sinistre, linia,eina,cost, compatibilitat) ".
							"	select sinis, linia, eina, preureparacio, compatibilitatDefecte ".
							"	from ( ".
							"		select s.codi sinis, comp.* , gravetat ,preuhoralloguer,  preuhoralloguer*3 preureparacio from ( ".
							"			select codi,  cursa, propietari, eg.eina, grup, compatibilitatDefecte, nvl(horesusada,0) , ".
							"					dense_rank() over (partition by codi order by eg.compatibilitatDefecte desc, nvl(horesusada,0), eg.eina) linia ".
							"			from einesGrupVehicles eg inner join vehicles v on eg.grup=v.grupVehicle ".
							"				inner join participantsCurses p on p.vehicle=v.codi ".
							"				left outer join einesVehicles ev on p.vehicle=ev.vehicle and eg.eina=ev.eina ".
							"			where cursa='".$_POST['cursa']."' and temps is null ".
							"		) comp inner join sinistres s on comp.codi=s.vehicle and comp.cursa=s.cursa ".
							"			inner join eines e on comp.eina=e.codi ".
							"		where linia<=gravetat ".
							"	) linies ".
							"	order by sinis, linia ";
				
				
				$insertLinies=oci_parse($conn,$queryLinies);
				oci_execute($insertLinies);
				
				$queryEines="Merge into einesVehicles ev ".
							"using (	select s.codi, s.cursa, s.vehicle, s.personatge, sl.eina, sl.linia, sl.cost, s.gravetat, sl.compatibilitat ".
							"		from sinistres s inner join sinistres_linies sl on s.codi=sl.sinistre ".
							"		where s.cursa='".$_POST['cursa']."' ".
							"	   ) linies ".
							"	on (ev.eina=linies.eina and ev.vehicle=linies.vehicle) ".
							"	when matched then Update  ".
							"		set ev.horesUsada=horesUsada+3, ".
							"			ev.compatibilitat=linies.compatibilitat ".
							"	when not matched then insert (vehicle, eina, compatibilitat,horesUsada) ".
							"		values(linies.vehicle,linies.eina,linies.compatibilitat,3)";
				
				$mergeEines=oci_parse($conn,$queryEines);
				oci_execute($mergeEines);
				
				oci_free_statement($insertSinistres);
				oci_free_statement($insertLinies);
				oci_free_statement($mergeEines);
			}
		?>
		
		<div id="contenidorFormAmple" style=" <?php if(!$visible){echo "display:none;";}  ?> ">
		<form method="post" action="gestioSinistre.php" >
			<p> <b>De quina cursa vols reparar els vehicles?</b></p>
				<?php
					$query="select curses.codi, curses.codi || ' --> ' || nom as descrip ".
							"from curses left outer join sinistres on curses.codi=sinistres.cursa ". 
							"where iniciReal is not null and sinistres.codi is null";
					$select=oci_parse($conn,$query);
					oci_execute($select);
					ompleCombo($select,'cursa','curses',true,true);
					oci_free_statement($select);
				?>
			<input type="submit" value="Repara vehicles" />
		</form>
		</div>
		
		<div id="contenidorFormAmple" style="  <?php if(!$visible2){echo "display:none;";} ?>">
			<p><b>S'han creat els seguents sinistres:</b></p>
			<table class="llista">
			
				<tr>
					<th>Personatge</th>
					<th>Vehicle</th>
					<th>Gravetat</th>
					<th>Eina</th>
					<th>Cost</th>
					<th>Compatibilitat</th>

				</tr>
				<?php
					$query="select personatge, vehicle, gravetat, eina, cost, COMPATIBILITAT ".
							"from sinistres s inner join sinistres_linies sl on s.codi=sl.sinistre ".
							"where s.cursa='".$_POST['cursa']."' ".
							"order by personatge, compatibilitat desc";
			
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
		
		<div id="contenidorFormAmple" style="  <?php if(!$visible2){echo "display:none;";} ?>">
			<p><b>S'han actualitzat les seguents eines:</b></p>
			<table class="llista">
			
				<tr>
					<th>Vehicle</th>
					<th>Eina</th>
					<th>Hores usada</th>
					<th>Compatibilitat</th>
				</tr>
				<?php
					$query="select ev.vehicle, ev.eina, ev.horesUsada, ev.compatibilitat ".
							"from einesVehicles ev inner join sinistres s on ev.vehicle=s.vehicle ".
							"	inner join sinistres_linies sl on s.codi=sl.sinistre and ev.eina=sl.eina ".
							"where s.cursa='".$_POST['cursa']."' ".
							"order by ev.vehicle, ev.eina";
			
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