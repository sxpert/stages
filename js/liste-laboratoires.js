
$('.visible-checkbox').change(function () {
	var lab=$(this).prop("value");
	var chk=$(this).prop("checked");
	$.getJSON("/ajax/toggle-labo.php?id="+lab+"&visible="+chk, function (data) {
		if (data) {
			var toggle = data['toggle'];
			if (toggle!==null)
				if (toggle!='OK') alert ('something went wrong');
		}
		
	});
});

