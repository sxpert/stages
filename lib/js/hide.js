/*******************************************************************************
 *
 * Gestion des offres de stage de M2 en Astro
 * (c) Raphaël Jacquot 2011
 * Fichier sous licence GPL-3
 *
 ******************************************************************************/

function init_hidden(id) {
    var item = $('#'+id);
    // initialise a none pour cacher
    item.css('display', 'none');
    var prev = item.prev();
    prev.replaceWith('<a class="header" href=""><span style="font-family:opensymbol;">'+
		     String.fromCharCode(0xE313)+'</span>'+prev.text()+'</a>');
    prev = item.prev();
    item.prev().click(function(e) {
	e.preventDefault();
	prev.blur();
	var disp = item.css('display');
	if (disp=='block') {
	    // hide item
	    item.css('display', 'none');
	    // change image to 
	    prev.children().first().text(String.fromCharCode(0xE313));
	} else {
	    // show item
	    item.css('display', 'block');
	    prev.children().first().text(String.fromCharCode(0xE315));
	}
	return 0;
    });
}