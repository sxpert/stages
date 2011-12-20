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
