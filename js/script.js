function add_find_block(){
	var form = document.getElementById("find_form");
	var button = document.getElementById("find_button");
	var div = document.getElementById("find_block");
	var new_div = div.cloneNode(true);
	new_div.setAttribute('id', 'find_block'+n);
	var select = new_div.getElementsByTagName('select');
	var input = new_div.getElementsByTagName('input');
	select[0].setAttribute('name', 'find'+n);
	select[0].selectedIndex = 0;
	input[0].setAttribute('name', 'text'+n);
	input[0].value = '';
	input[0].setAttribute('class', '');
	var link_rem = new_div.getElementsByClassName("link_rem");
	link_rem[0].setAttribute( 'onclick', 'remove_find_block(' + n + ')' );
	form.insertBefore(new_div, button);
	// setTimeout(function func() {
	// 	window.scrollTo(button.offsetLeft, button.offsetTop);
	// }, 100);
	n++;
}
function remove_find_block(n_rem){
	if (n_rem == 0){
		var div = document.getElementById("find_block");
		var input = div.getElementsByTagName('input');
		input[0].value = '';			
	} else {
		var div = document.getElementById("find_block"+n_rem);
		var form = document.getElementById("find_form");
		form.removeChild(div);
	}
}
function show_popup_get( text, login, get_str) {
	var popup = document.getElementById("popup");
	var popup_text = document.getElementById("popup_text");
	var popup_login = document.getElementById("popup_login");
	var popup_yes = document.getElementById("popup_yes");		
	popup_text.innerHTML = text;
	popup_login.innerHTML = login + "?";
	popup_yes.setAttribute('href', get_str);
	popup_no.setAttribute( 'onclick', 'hide_popup()' );
	popup.setAttribute('style', 'display:block');
}	
function hide_popup(){
	document.getElementById('popup').setAttribute('style', 'display:none');
}	
function set_page( n_page ) {
	if (n_page != null){
		var hidden = document.getElementById("n_page_input");
		hidden.value = n_page;
		document.forms['n_page'].submit();
	}
}
function user_id( id ) {
	if (id != null){
		var hidden = document.getElementById("user_id");
		hidden.value = id;
		document.forms['hidden_form'].submit();
	}
}
function show_popup_post( text, login, form_name ) {
	var popup = document.getElementById("popup");
	var popup_text = document.getElementById("popup_text");
	var popup_login = document.getElementById("popup_login");
	var popup_yes = document.getElementById("popup_yes");
	popup_text.innerHTML = text;
	popup_login.innerHTML = login + "?";
	popup_yes.setAttribute( 'onclick', 'send_form("'+form_name+'","do_change_data")' );
	popup_no.setAttribute( 'onclick', 'hide_popup()' );
	popup.setAttribute('style', 'display:block');
}
function send_form( form_name , field_name ){		// отправка формы ( id , id )
	var arr = [];									// еще устанавливается name = id для поля подтверждения
	arr = arr.concat(field_name);					// field_name - строка или массив строк
	arr.forEach(function(item, i, arr) {
		var hidden = document.getElementById(item);
		hidden.setAttribute( 'name', item );
	});
	document.forms[form_name].submit();
}
function save_form_dev(){
	document.getElementsByName("name")[0].value = delete_tags(document.getElementById("name").innerHTML);
	document.getElementsByName("type")[0].value = delete_tags(document.getElementById("type").innerHTML);
	document.getElementsByName("number")[0].value = delete_tags(document.getElementById("number").innerHTML);
	send_form("form_dev", "do_change_data");
}
function clear_form_dev(){
	document.getElementsByName("name")[0].value = "";
	document.getElementsByName("type")[0].value = "";
	document.getElementsByName("number")[0].value = "";
	document.getElementsByName("date_release")[0].value = "";
	document.getElementsByName("state")[0].selectedIndex = 0;
	document.getElementById("name").innerHTML = "";
	document.getElementById("type").innerHTML = "";
	document.getElementById("number").innerHTML = "";
}
function delete_tags( str ){		// удаление тегов div,br и замена (&nbsp;) и (<>&)
	str = str.replace(/<div>/gi," ");
	str = str.replace(/<\/div>/gi," ");
	str = str.replace(/<\/?br>/gi," ");
	str = str.replace(/&nbsp;/g," ");
	str = str.replace(/&amp;/g,"&");
	str = str.replace(/&lt;/g,"<");
	str = str.replace(/&gt;/g,">");
	str = str.replace(/ +/g," ");
	return str.trim();
}
function ack_double_number( text, login, form_name ){
	document.getElementsByName("name")[0].value = delete_tags(document.getElementById("name").innerHTML);
	document.getElementsByName("type")[0].value = delete_tags(document.getElementById("type").innerHTML);
	document.getElementsByName("number")[0].value = delete_tags(document.getElementById("number").innerHTML);	
	var popup = document.getElementById("popup");
	var popup_text = document.getElementById("popup_text");
	var popup_login = document.getElementById("popup_login");
	var popup_yes = document.getElementById("popup_yes");
	popup_text.innerHTML = text;
	popup_login.innerHTML = login + "?";
	popup_yes.setAttribute( 'onclick', 'send_form("'+form_name+'",["do_change_data","do_ignore_double"])' );
	popup_no.setAttribute( 'onclick', 'hide_popup()' );
	popup.setAttribute('style', 'display:block');
}
function elem_copy_clear( elem_id ){				// поиск последнего elem_id[0-9]*, копирование и вставка после него
	let regexp = /([a-zA-Z_]+)([0-9]*)/;			// независимый инкремент: parent id++;	child id++, name++
	let elem = document.getElementById( elem_id );	// очистка (child, child.child) у кого есть id; сброс value, select
	let parent = elem.parentElement;
	let copy;
	let last_elem;
	for (let i = 0; i < parent.childNodes.length; i++) {     // поиск последнего id + "n"
		if ( parent.childNodes[i].nodeName=="DIV" && parent.childNodes[i].hasAttribute('id') ){
			if ( parent.childNodes[i].id.match(elem_id).length ){
				elem = parent.childNodes[i];
				if ( i < parent.childNodes.length - 1 ) last_elem = parent.childNodes[i+1];
				else last_elem = 0;
			}
		}
	}
	copy = elem.cloneNode(true);
	if ( copy.hasAttribute('id') ){						    // инкремент id (элемента copy)
		let matches = copy.id.match( regexp );
		copy.id = matches[1] + (matches[2]?++matches[2]:'1');
	}
	for (let i = 0; i < copy.childNodes.length; i++) {		// инкремент id, name (childs copy)
		if ( copy.childNodes[i].nodeName!="#text" && copy.childNodes[i].nodeName!="#comment" ){
			let i_child = copy.childNodes[i];
			if  ( i_child.hasAttribute('id') ){
				let matches = i_child.id.match( regexp );
				i_child.setAttribute( 'id' , matches[1] + (matches[2]?++matches[2]:'1') );
			}
			if  ( i_child.hasAttribute('name') ){
				let matches = i_child.getAttribute( 'name').match( regexp );			
				i_child.setAttribute( 'name' , matches[1] + (matches[2]?++matches[2]:'1') );
			}
			if ( i_child.nodeName == "INPUT" ) i_child.value = '';
			else if ( i_child.nodeName == "SELECT" ) i_child.selectedIndex = 0;
			else if ( i_child.id ) i_child.innerHTML = '';
			if ( i_child.nodeName == "DIV" ){
				for (let j = 0; j < i_child.childNodes.length; j++) {
					if ( i_child.childNodes[j].nodeName!="#text" && i_child.childNodes[j].nodeName!="#comment" ){
						if  ( i_child.childNodes[j].hasAttribute('id') ){
							let matches = i_child.childNodes[j].id.match( regexp );
							i_child.childNodes[j].setAttribute( 'id' , matches[1] + (matches[2]?++matches[2]:'1') );
						}
						if  ( i_child.childNodes[j].hasAttribute('name') ){
							let matches = i_child.childNodes[j].getAttribute( 'name').match( regexp );			
							i_child.childNodes[j].setAttribute( 'name' , matches[1] + (matches[2]?++matches[2]:'1') );
						}		
						if ( i_child.childNodes[j].nodeName == "INPUT" ) i_child.childNodes[j].value = '';
						else if ( i_child.childNodes[j].nodeName == "SELECT" ) i_child.childNodes[j].selectedIndex = 0;
						else if ( i_child.childNodes[j].id ) i_child.childNodes[j].innerHTML = '';
					}
				}
			}
		} 
	}
	if ( last_elem ) parent.insertBefore(copy, last_elem);
	else parent.appendChild(copy);
}
function create_inputs_send( parent_id ){	// создание input для childs (id=send_...) для соседних parent_id[0-9]*
	const ARR_PAR_MAX_LENGTH = 999;		// лимит, т.к. узлы добавляются во время прохода по ним же
	const regexp = /^send_(\w+)/;
	let parent = document.getElementById( parent_id );
	let grandpa = parent.parentElement;
	for (let p = 0; p < grandpa.childNodes.length; p++) {
		if ( grandpa.childNodes[p].nodeName == "DIV" && grandpa.childNodes[p].id.match(parent_id).length ){
			let par_nodes = grandpa.childNodes[p].childNodes;
			for (let i = 0; i < par_nodes.length && i < ARR_PAR_MAX_LENGTH; i++) {
				let item = par_nodes[i];
				if ( item.nodeName == "DIV" && item.id.match(regexp).length ){
					if ( i > 0 ){
						if ( par_nodes[i-1].nodeName != "INPUT" || par_nodes[i-1].getAttribute("name") != item.id.match(regexp)[1] ){
							let input = document.createElement('input');	
							input.setAttribute("name", item.id.match(regexp)[1]);			
							input.setAttribute("type", "hidden");			
							grandpa.childNodes[p].insertBefore(input, item);
						}
						par_nodes[i-1].value = delete_tags(item.innerHTML);
					}
				}
			}
		}
	}
}