function update_adresse_labo(labo) {
    var wrapper='<div class="wrapper"/>';
    var addrdiv='<br/><div id="labo-address">&nbsp;</div>';
    // add the labo-address stuff if required
    if ($('#labo-address').length==0)
	$('[name="labo"]').wrap(wrapper).after(addrdiv);
    $.getJSON('/ajax/labo-infos.php',{ id:$('[name="labo"]').val() },
	     function (data) {
		 if (data) address = data['post_addr']+'<br/>'+data['post_code']+' '+data['city'];
		 else address = '&nbsp;';
		 $('#labo-address').empty().append(address);
	     });
}