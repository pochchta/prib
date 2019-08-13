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
	popup_yes.setAttribute( 'onclick', 'send_form("'+form_name+'")' );
	popup_no.setAttribute( 'onclick', 'hide_popup()' );
	popup.setAttribute('style', 'display:block');
}
function send_form( form_name ){		// отправка формы, еще устанавливается name = "do_change_data" для скрытого поля
	var hidden = document.getElementById("do_change_data");
	hidden.setAttribute( 'name', 'do_change_data' );
	document.forms[form_name].submit();
}
function save_form_dev(){
	document.getElementsByName("name")[0].value = document.getElementById("name").innerHTML;
	document.getElementsByName("type")[0].value = document.getElementById("type").innerHTML;
	document.getElementsByName("number")[0].value = document.getElementById("number").innerHTML;
	document.forms["form_dev"].submit();
}
