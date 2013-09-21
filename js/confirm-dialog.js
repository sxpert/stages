/*******************************************************************************
 *
 * Gestion des offres de stage de M2 en Astro
 * (c) Raphaël Jacquot 2011
 * Fichier sous licence GPL-3
 *
 ******************************************************************************/

function buildDialog(action, message) {
	var div = document.createElement('div');
	div.id = 'dialog-confirm';
	div.setAttribute('title', action);
	div.style.fontSize='10pt';
	var icon = document.createElement('div');
	icon.className = 'ui-icon ui-icon-alert';
	icon.style.float = 'left';
	icon.style.margin = '0 7px 7px 0';
	div.appendChild(icon);
	var texts = document.createElement('div');
	texts.style.marginLeft = '20pt';
	for(var i=0;i<message.length;i++) {
		texts.appendChild(document.createTextNode(message[i]));
		texts.appendChild(document.createElement('br'));
	}
	// remove last child
	texts.removeChild (texts.lastChild);
	div.appendChild(texts);
	document.body.appendChild(div);
	return div;
}

function askConfirm (div, userdata, url, successfunc, errorfunc) {
	$(div).dialog({
    resizable: false,
    modal: true,
    buttons: {
	    "Oui": function() {
	      $(this).dialog("close");
			  $.ajax({
			    type:    'POST',
		  	  url:     url,
		 			data:    userdata,
		  		success: successfunc,
		  		error:   errorfunc,
		  		datatype: 'json'
		  	});
     	},
			"Non !": function () {
		    $(this).dialog("close");
		  }
		}
  });
}

function alertDialog(title, message, okfunc) {
	$('<div>').attr('title',title).css('font-size','10pt').append ([
		$('<div>').text(message),
	]).dialog({
		resizable: false,
		modal: true,
		buttons: {
			'Ok': function () {
				$(this).dialog('close');
				if (okfunc!==undefined)
					okfunc();
				},
		}
	});
}

