<?php
$valid_passwords = array ("admin" => "admin");
$valid_users = array_keys($valid_passwords);

$user = $_SERVER['PHP_AUTH_USER'];
$pass = $_SERVER['PHP_AUTH_PW'];

$validated = (in_array($user, $valid_users)) && ($pass == $valid_passwords[$user]);

if (!$validated) {
  header('WWW-Authenticate: Basic realm="My Realm"');
  header('HTTP/1.0 401 Unauthorized');
  die ("Not authorized");
}
error_reporting(0);
backup_database_tables('localhost', 'root', '', 'b7_18949845_pox', '*');

// backup the db function
function backup_database_tables($host, $user, $pass, $name, $tables) {

    $link = mysql_connect($host, $user, $pass);
    mysql_select_db($name, $link);

    //получаем все таблицы
    if ($tables == '*') {
        $tables = array();
        $result = mysql_query('SHOW TABLES');
        while ($row = mysql_fetch_row($result)) {
            $tables[] = $row[0];
        }
    } else {
        $tables = is_array($tables) ? $tables : explode(',', $tables);
    }

    //ѕроходим в цикле по всем таблицам и форматируем данные
    foreach ($tables as $table) {
        $result = mysql_query('SELECT * FROM ' . $table);
        $num_fields = mysql_num_fields($result);

        $return.= 'DROP TABLE ' . $table . ';';
        $row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE ' . $table));
        $return.= "\n\n" . $row2[1] . ";\n\n";

        for ($i = 0; $i < $num_fields; $i++) {
            while ($row = mysql_fetch_row($result)) {
                $return.= 'INSERT INTO ' . $table . ' VALUES(';
                for ($j = 0; $j < $num_fields; $j++) {
                    $row[$j] = addslashes($row[$j]);
                    $row[$j] = ereg_replace("\n", "\\n", $row[$j]);
                    if (isset($row[$j])) {
                        $return.= '"' . $row[$j] . '"';
                    } else {
                        $return.= '""';
                    }
                    if ($j < ($num_fields - 1)) {
                        $return.= ',';
                    }
                }
                $return.= ");\n";
            }
        }
        $return.="\n\n\n";
    }

    //сохран¤ем файл
    $handle = fopen('db-backup-' . time() . '-' . (md5(implode(',', $tables))) . '.sql', 'w+');
    fwrite($handle, $return);
    fclose($handle);
}
print <<<HERE
<link rel="stylesheet" href="/bootstrap.min.css">
<a href="./index.php" style="position:absolute; top:10px;" class="btn btn-primary">Вернуться сейчас</a>
<h1 style="text-align:center; margin-top:70px">Резервное копирование базы данных выполнено успешно.</h1>
<form name="redirect">
    <center>
        <font face="Arial"><b>Вы перейдете на главную страницу через<br><br>
            <form>
                <input type="text" size="3" name="redirect2">
            </form>
            секунд</b></font>
    </center>
</form>    
    <script>
        var targetURL = "./index.php"
        var countdownfrom = 10
        var currentsecond = document.redirect.redirect2.value = countdownfrom + 1

        function countredirect() {
            if (currentsecond != 1) {
                currentsecond -= 1
                document.redirect.redirect2.value = currentsecond
            }
            else {
                window.location = targetURL
                return
            }
            setTimeout("countredirect()", 1000)
        }
        countredirect()
    </script>
    <style type="text/css">
    html {
        padding: 5px;
    }
    </style>
HERE;

//echo '<a href='/print "<script type=\"text/javascript\">document.location.href =\"index.php\";</script>";'>';
?>