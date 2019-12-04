<?php
function ompleCombo($select,$postName,$id,$descrip=false,$required=false){
//Pre: $select es una consulta ben construida
//Post: construieix un select-option amb nom $postName i els valor del resultat de $select. amb opcions de obligatori($required)
//		i mostrar el nom o descripcio ($descrip)

	
	echo "<select name=\"".$postName."\" id=\"".$id."\"  ".($required?'required="required"':'')." >\n";
	echo "<option ".($required?'':'value="blank"')." > </option>\n";
	while ($row = oci_fetch_array($select, OCI_BOTH)) {
		 echo "<option value=\"".$row[0]."\">".$row[($descrip ? 1 : 0)]."</option>\n";
		
	}
	echo "</select>";
	
}

function exist($select,$nom){
//Pre: select es una consulta correcte
//Post: retorna cert si $nom existeix a la base de dades
	
	$existeixCodi=false;
	//nom: camp que es selecciona
	//num: variable que contidra el valor del camp seleccionat $nom
	oci_define_by_name($select, $nom, $num);
	oci_execute($select);
				
	while (oci_fetch($select)){
		if($num>0)					
			$existeixCodi=true;
	}
	
	return $existeixCodi;
				
}

?>