function ms_append_select(variable) {
    
    var wrapper = $('#'+variable);
    var wl = wrapper.children().length;

    var tsel = '<select name="'+variable+'[]">';
    eval('var values = '+variable+'_values');
    for (i=0; i<values.length;i++) {
	var v = values[i];
	tsel+='<option value="'+v[0]+'">'+v[1]+'</option>';
    }
    tsel+='</select>';
    var tmin = '<button>-</button>';
    var tbr = '<br/>';
    var tplus = '<button>+</button>';

    function remove (event) {
	event.preventDefault();
	if ($(this).siblings().length == 3) return;
	$(this).prev().remove();
	$(this).next().remove();
	$(this).remove();
    };

    function append (event) {
	event.preventDefault();
	ms_append_select(variable);
    };

    if (wl==0) {
	wrapper.append(tsel);
	wrapper.append(tmin);
	wrapper.children('button').last().click(remove);
	wrapper.append(tbr);
	wrapper.append(tplus);
	wrapper.children('button').last().click(append);
	
    } else {
	var b = wrapper.children('br').last();
	b.after(tbr);
	b.after(tmin);
	b.next().click(remove);
	b.after(tsel);
    }
}
  