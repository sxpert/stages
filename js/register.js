/*******************************************************************************
 *
 * Gestion des offres de stage de M2 en Astro
 * (c) Raphaël Jacquot 2011
 * Fichier sous licence GPL-3
 *
 ******************************************************************************/

function update_adresse_labo(labo) {
    $.getJSON('/ajax/labo-infos.php',{ id:$('[name="labo"]').val() },
	     function (data) {
		 if (data) {
		     pa = data['post_addr'];
		     if (pa===null) pa = '[adresse inconnue]';
		     pc = data['post_code'];
		     if (pc===null) pc = '[code postal inconnu]';
		     c = data['city'];
		     if (c===null) c = '[ville inconnue]';
		     address = pa+'<br/>'+pc+' '+c;   
		 }
		 else address = '&nbsp;';
		 $('#labo-address').empty().append(address);
	     });
}



var wrapper='<div class="wrapper"/>';
var addlab='<button id="addlabo">ajouter un laboratoire</button>';
var addrdiv='<br/><div id="labo-address">&nbsp;</div>';
// add the labo-address stuff if required
function register_init() {
    
    if ($('#labo-address').length==0)
	$('[name="labo"]').wrap(wrapper).after(addrdiv);
    /*
    $('#addlabo').bind('click',function(event) {
	alert('ajouter un laboratoire n\'est pas encore implémenté');
	
	return false;
    });
    */
}
