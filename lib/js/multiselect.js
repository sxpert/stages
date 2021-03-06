/*******************************************************************************
 *
 * Gestion des offres de stage de M2 en Astro
 * (c) Raphaël Jacquot 2011
 * Fichier sous licence GPL-3
 *
 ******************************************************************************/

function ms_append_select(variable, selected) {
    if (!selected) selected=null;

    var vname = variable['name'];
    var width = variable['width'];
    var wrapper = $('#'+vname);
    var wl = wrapper.children().length;

    var tsel = '<select name="'+vname+'[]"';
    if (width!=null) tsel+=' style="width:'+width+';"';
    tsel+='>';
    var values=variable['values'];
    for (i=-1; i<values.length;i++) {
	if (i==-1) var v = [ 0, ''];
	else var v = values[i];
	var s = '';
	if (selected&&(v[0]==selected)) s=' selected';
	tsel+='<option value="'+v[0]+'"'+s+'>'+v[1]+'</option>';
    }
    tsel+='</select>';
    var tmin = '<button class="multi-select-button">-</button>';
    var tbr = '<br/>';
    var tplus = '<button class="multi-select-button">+</button>';

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
  
// initializes a multiselect with the default value selected
function ms_init (variable) {
    var values = variable['init'];
    if ((values === null)||(values.length == 0)) ms_append_select(variable);
    else for(var i=0; i<values.length; i++)
	ms_append_select(variable, values[i]);    
}