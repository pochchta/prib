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
function send_form( form_name , field_name ){		// отправка формы, еще устанавливается name = id для поля подтверждения
	var arr = [];									// field_name - строка или массив строк
	arr = arr.concat(field_name);
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
function delete_tags( str ){		// удалить теги div,br из строки
	str = str.replace(/<div>/gi," ");
	str = str.replace(/<\/div>/gi," ");
	str = str.replace(/<\/?br>/gi," ");
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
	popup_yes.setAttribute( 'onclick', 'send_form("'+form_name+'",["do_change_data","ignore_double"])' );
	popup_no.setAttribute( 'onclick', 'hide_popup()' );
	popup.setAttribute('style', 'display:block');
}