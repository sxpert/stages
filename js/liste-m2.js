function disable_m2(event) {
  event.preventDefault();
  // get the m2 name and id
  target = $(event.target);
  m2_id = target.attr('data-id');
  m2_name = target.attr('data-name');;
  ok = confirm("désactivation du M2:\n"+m2_name);
  if (ok) {
    data = {
      id: m2_id,
    };
    $.ajax({
      method: 'POST',
      url: '/disable-m2.php',
      data: data,
      dataType: 'json',
      success: (data) => {
	// find the div
        row = target.parent().parent();
	row.removeClass('active');
        target.remove();
      },
    });
  }
}

$(function() {
  $('.button').on('click', disable_m2);
});
