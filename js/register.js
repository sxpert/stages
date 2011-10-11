function update_adresse_labo(labo) {
    var wrapper='<div class="wrapper"/>';
    var addrdiv='<br/><div id="labo-address">test de truc</div>';
    // add the labo-address stuff if required
    if ($('#labo-address').length==0)
	$('[name="labo"]').wrap(wrapper).after(addrdiv);
    $.getJSON('/ajax/labo-infos.php',{ id:$('[name="labo"]').val() },
	     function (data) {
		 
	     });
}