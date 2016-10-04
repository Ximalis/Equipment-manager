$(document).ready(function()
	{
		$(".tablesorter").tablesorter();
		$(".date-time-picker").AnyTime_picker({
			format: "%Z-%m-%d %H:%i",
			labelTitle: "Выберете дату и время",
			labelHour: "Часы",
			labelMinute: "Минуты",
			labelYear: "Год",
			labelMonth: "Месяц",
			monthAbbreviations: ['янв', 'фев', 'мар', 'апр', 'май', 'июн', 'июл', 'авг', 'сен', 'окт', 'ноя', 'дек'],
			labelDayOfMonth: "День месяца",
			dayAbbreviations: [ 'вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб'],
			firstDOW: 1
	});
	}
);
document.getElementById("admin_edit_users").onclick = function(){
	window.location = '?action=edit_users';
};
/*document.getElementById("admin_export_db").onclick = function(){
	if( confirm('Экспортировать БД?') ){
		window.location = './exportDB.php';
	}
};
document.getElementById("admin_import_table_from_excel").onclick = function(){
	if( confirm('Импортировать оборудование?') ){
		window.location = './importExcel.html';
	}
};*/
document.getElementById("search_btn").onclick = function(){
	var v = document.getElementById("search_query").value;
                window.location = '?action=search_results&name='+v;
};
document.getElementById("admin_test_export").onclick = function(){
	window.location = '?action=test_export';
};
document.getElementById("admin_test_importexcel").onclick = function(){
	window.location = '?action=test_excel';
};
/*
document.getElementById("admin_create_user").onclick = function(){
	window.location = '?action=create_user';
};
document.getElementById("admin_delete_user").onclick = function(){
                window.location = '?action=delete_user';
};
document.getElementById("admin_editpeople").onclick = function(){
	if( confirm('Редактировать пользователей?') ){
		window.location = './editpeople.php';
	}
};



document.getElementById("admin_export_admin").onclick = function(){
	if( confirm('Экспортировать Админа?') ){
		window.location = '?action=export_admin';
	}
}
document.getElementById("admin_export_users").onclick = function(){
	if( confirm('Экспортировать пользователей?') ){
		window.location = '?action=export_users';
	}
}
document.getElementById("import_tbl").onclick = function(){
	if( confirm('Импортировать таблицу?') ){
		window.location = '?action=import_tbl';
	}
}
document.getElementById("admin_editpeople").onclick = function(){
	if( confirm('Редактировать пользователей?') ){
		window.location = '?action=editpeople';
	}
}
document.getElementById("admin_delete_db").onclick = function(){
	if( confirm('Удалить БД полностью?') ){
		window.location = '?action=delete_db';
	}
}
document.getElementById("admin_delete_users").onclick = function(){
	if( confirm('Удалить всех пользователей?') ){
		window.location = '?action=delete_users';
	}
}
document.getElementById("admin_delete_admin").onclick = function(){
	if( confirm('Удалить Админа (если удалится последний Админ, то удалятся и все пользователи)?') ){
		window.location = '?action=delete_admin';
	}
}
 document.getElementById("admin_logout").onclick = function(){
	if( confirm('Завершить сеанс?') ){
		window.location = '?action=logout';
	}
}
*******************************************************************************/
