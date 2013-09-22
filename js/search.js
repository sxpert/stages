/*******************************************************************************
 *
 * Gestion des offres de stage de M2 en Astro
 * (c) Raphaël Jacquot 2011
 * Fichier sous licence GPL-3
 *
 ******************************************************************************/

function set_all(mode) {
    $('input[name="multisel[]"]').each(
	function() {
	    $(this).prop('checked', mode);
	});
}

function select_all(event) {
    event.preventDefault();
    set_all(true);
}

function deselect_all(event) {
    event.preventDefault();
    set_all(false);
}

function count() {
    var nb=0;
    $('input[name="multisel[]"]').each(
        function() {
            if ($(this).prop('checked')) nb++;
        });
    return nb;
}

function print(event) {
    if (count()==0) {
        alert('Vous devez sélectionner au moins une offre à imprimer');
	return false;
    }
}

function search_init() {
    $('#select').click(select_all);
    $('#deselect').click(deselect_all);
    $('#print').click(print);
}

$(".m2-checkbox").click( function (event) {
	event.preventDefault();
	var $t = $(event.target);
	var v = $t.val();
	var r = /(\d+),(\d+)/;
	var match = r.exec(v);
	if (match===null)
		return;
	
	var offre_id = match[1];
	var m2_id = match[2];
	var $span_m2 = $t.parents('span.m2');
	var $offre = $t.parents('div.offre');
	var idx = $offre.children('span.m2').index($span_m2);
	var sujet = $offre.children('a').children('span.sujet')[0].textContent;
	var $f = $t.parents('form#list').children('div.header').children('span.m2');
	var $m2 = $($f[idx]).children('span').contents().filter(function () { return this.nodeType === 3; });
	var shortdesc = $m2[0].textContent;
	var ville = $m2[1].textContent;
	console.log (offre_id+' '+m2_id);
	console.log (sujet);
	console.log (shortdesc, ville);
	// create url
	var div = buildDialog ("Dévalider cette offre ?",
		[ 'Souhaitez vous dévalider l\'offre ',
			'«'+sujet+'»',
			'pour le M2R '+shortdesc+', '+ville,
			'Êtes vous sûr de vouloir dévalider cette offre ?']);
	var data = { offre_id: offre_id, m2_id: m2_id };
	var url = '/ajax/invalidate-offre.php';
	var success = function (data) {
		console.log (data);
		if (data.ok===false) {
			alertDialog('Erreur', data.error, function () {
				console.log('error: '+data.error);
			});
		} else {
			$t.replaceWith("&nbsp;");
		}
	};
	var error = function () {
		alert ('Une erreur est survenue lors de l\'action');
	};
	askConfirm(div, data, url, success, error);
});
