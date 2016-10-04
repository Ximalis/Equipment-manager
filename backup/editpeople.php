<meta http-equiv="Content-Language" content="ru">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="anytime.compressed.css">
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css">
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap-theme.min.css">
<link rel="stylesheet" href="/style.css">
<style type="text/css">

</style>
<?php
if (!isset($_GET["action"]))
    $_GET["action"] = "showlist";

switch ($_GET["action"]) {
    case "showlist":    // Список всех записей в таблице БД
        show_list();
        break;
    case "addform":     // Форма для добавления новой записи 
        get_add_row_form();
        break;
    case "add":         // Добавить новую запись в таблицу БД
        add_row();
        break;
    case "editform":    // Форма для редактирования записи 
        get_edit_row_form();
        break;
    case "update":      // Обновить запись в таблице БД
        update_row();
        break;
    case "delete":      // Удалить запись в таблице БД
        delete_row();
        break;
    default:
        show_list();
}

//error_reporting(0); // не отображать отчёты об ошибках

// Функция выводит список всех записей в таблице БД
    function show_list() {

        $valid_passwords = array("admin" => "admin");
        $valid_users = array_keys($valid_passwords);

        $user = $_SERVER['PHP_AUTH_USER'];
        $pass = $_SERVER['PHP_AUTH_PW'];

        $validated = (in_array($user, $valid_users)) && ($pass == $valid_passwords[$user]);

        if (!$validated) {
            header('WWW-Authenticate: Basic realm="Please enter login and password"');
            header('HTTP/1.0 401 Unauthorized');
            $diescript = '<a href='/print "<script type=\"text/javascript\">document.location.href =\"index.php\";</script>";'>';
            die("$diescript");
        }
        $link = mysqli_connect("localhost", "root", "", "b7_18949845_pox") or die("Error " . mysqli_error($link));
        $query = 'SELECT * FROM people';
        $result = mysqli_query($link, $query);
    ?>
        <a href="./index.php" class="btn btn-primary" style="position:relative; left: 5px; top:5px;">Вернуться на главную</a>
        <h2 style="text-align: center;">Редактирование учётных записей</h2>
        <table class="table table-striped table-bordered table-condensed table-hover col-sm-4">
        <tr><th>Id</th><th>Логин</th><th>Пароль</th><th>Тип*</th><th>Редактирование</th><th>Удаление</th></tr>

<?
        while ($row = mysqli_fetch_array($result)) {
            echo '<tr>';
            echo '<td>' . $row['id'] . '</td>';
            echo '<td>' . $row['name'] . '</td>';
            echo '<td>' . $row['password'] . '</td>';
            echo '<td>' . $row['type'] . '</td>';
            echo '<td><a class="btn btn-info" href="' . $_SERVER['PHP_SELF'] . '?action=editform&id=' . $row['id'] . '">Редактировать</a></td>';
            echo '<td><a class="btn btn-danger" href="' . $_SERVER['PHP_SELF'] . '?action=delete&id=' . $row['id'] . '">Удалить</a></td>';
            echo '</tr>';
        }
        echo '</table>';?>
        <p><a class="btn btn-primary" style="margin: 5px 5px 5px 5px;" href=" <?=$_SERVER['PHP_SELF'] . '?action=addform'; ?> ">Добавить</a></p>
    <?}
?>
<h5 style="margin: 15px 15px 15px 15px; text-align: left;">*Колонка "Тип" отвечает за привилегии пользователя: 1 - Администратор, 2 - Обычный пользователь.</h5>


<?
// Функция формирует форму для добавления записи в таблице БД 
function get_add_row_form() {
    ?>
    <h2 style="margin: 15px 15px 15px 15px;">Добавить</h2>
    <form name="addform" action="<?=$_SERVER['PHP_SELF'] . '?action=add'?>" method="POST">
    <table class="table table-striped table-bordered table-condensed table-hover">
    <tr>
    <td>Логин</td>
    <td><input type="text" SIZE=40 name="name" value="" /></td>
    </tr>
    <tr>
    <td>Пароль</td>
    <td><input type="text" SIZE=40 name="password" value="" /></td>
    </tr>
    <tr>
    <td>Тип*</td>
    <td><textarea name="type"></textarea></td>
    <td><input type="radio" name="type" value="1"> Администратор
              <input type="radio" autofocus name="type" value="2"> Пользователь</td>
    </tr>
    <tr>
    </tr>
    </table>
    <td><input style="margin: 15px 5px 5px 5px;" class="btn btn-primary" type="submit" value="Сохранить"></td>
    <td><button style="margin: 15px 5px 5px;" 15px; class="btn btn-primary" type="button" onClick="history.back();">Отменить</button></td>
    </form>
<?}?>
<?
// Функция добавляет новую запись в таблицу БД  
function add_row() {
    $link = mysqli_connect("localhost", "root", "", "b7_18949845_pox") or die("Error " . mysqli_error($link));
    $name = mysql_escape_string($_POST['name']);
    $password = md5(mysql_escape_string($_POST['password']));
    $type = mysql_escape_string($_POST['type']);
    if(!empty($password)){
        $query = "INSERT INTO people (name, password, type) VALUES ('" . $name . "', '" . $password . "', '" . $type . "');";
        md5($password);
        $result = mysqli_query($link, $query);
        header('Location: ' . $_SERVER['PHP_SELF']);
        die();
    } else{
        echo "<h1>Err</h1>";
    }

}

// Функция формирует форму для редактирования записи в таблице БД
function get_edit_row_form()
{
    $link = mysqli_connect("localhost", "root", "", "b7_18949845_pox") or die("Error " . mysqli_error($link));
    $query = 'SELECT name, password, type FROM people WHERE id=' . $_GET['id'];
    $result = mysqli_query($link, $query);
    $row = mysqli_fetch_array($result);

?>
    <form name="editform" action="<?= $_SERVER['PHP_SELF'] . '?action=update&id=' . $_GET['id'] ?>" method="POST">
    <table class="table table-striped table-bordered table-condensed table-hover">
    <tr>
    <td>Логин</td>
    <td><input type="text" SIZE=38 name="name" value="<?=$row['name']?>"></td>
    </tr>
    <tr>
    <td>Пароль</td>
    <td><input type="text" SIZE=38 name="password" value="<?=$row['password']?>"></td>
    </tr>
    <tr>
    <td>Тип*</td>
    <td><textarea rows="1" cols="2" name="type"><?=$row['type']?></textarea></td>
    </tr>
    </table>
    <h5 style="margin: 15px 15px 15px 15px; text-align: left;">*Строка "Тип" отвечает за привилегии пользователя: 1 - Администратор, 2 - Обычный пользователь.</h5>
    <input class="btn btn-primary" style="margin: 5px 5px 5px 5px" type="submit" value="Сохранить">
    <button class="btn btn-primary" style="margin: 5px 5px 5px 5px" type="button" onClick="history.back();">Отменить</button>
    </form>
		<div style="margin-top: 100px; margin-left:50px;">
		<h2>Внимание! Перед изменением пароля, необходимо его зашифровать. Введите новый пароль в поле и нажмите кнопку "Вычислить MD5". После зашифровки скопируйте полученый пароль в поле "Пароль" для изменения</h2>
	<script type="text/javascript" src="jshash-2.2/md5.js"></script>
	<script type="text/javascript">
	function generateMD5(){
		var str = document.generateMD5Form.str.value;
		var md5 = hex_md5(str);
		document.generateMD5Form.md5.value = md5;
	}
	</script>
	<form name="generateMD5Form">
		<p>Строка: <input type="text" name="str" value="" placeholder="Введите новый пароль в это поле" size="100" /></p>
		<p>
			<input type="button" class="btn btn-primary" onClick="generateMD5()" value="Вычислить MD5" />
			MD5-хэш: <input type="text" name="md5" value="" size="50" />
		</p>
	</form>
	</div>
<?}?>
<?
// Функция обновляет запись в таблице БД  
function update_row() {
    $link = mysqli_connect("localhost", "root", "", "b7_18949845_pox") or die("Error " . mysqli_error($link));
    $name = mysql_escape_string($_POST['name']);
    $password = mysql_escape_string($_POST['password']);
    $getpas = ($_GET['id']);
    $query1 = mysql_query("SELECT password FROM people WHERE id= $getpas");
    echo mysqli_data_seek($query1, 0);
    echo $password;
    //$md5password = md5(mysql_escape_string($_POST['password']));
    //if ($password != $md5password){
    $type = mysql_escape_string($_POST['type']);
    $query = "UPDATE people SET name='" . $name . "', password='" . $password . "', type='" . $type . "' 
            WHERE id=" . $_GET['id'];
    $result = mysqli_query($link, $query);
    header('Location: ' . $_SERVER['PHP_SELF']);
    die();
    //} else {
    //	$type = mysql_escape_string($_POST['type']);
    //	$query = "UPDATE people SET name='" . $name . "', type='" . $type . "' 
    //			WHERE id=" . $_GET['id'];
    //	$result = mysqli_query($link, $query);
    //	header('Location: ' . $_SERVER['PHP_SELF']);
    //	die();
//	}
}

// Функция удаляет запись в таблице БД 
function delete_row() {
    $link = mysqli_connect("localhost", "root", "", "b7_18949845_pox") or die("Error " . mysqli_error($link));
    $query = "DELETE FROM people WHERE id=" . $_GET['id'];
    $result = mysqli_query($link, $query);
    header('Location: ' . $_SERVER['PHP_SELF']);
    die();
}

?>