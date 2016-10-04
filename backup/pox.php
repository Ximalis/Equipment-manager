<?php

class Pox {
	
	var $db;
	var $mysqlDir;
	var $siteDir;
	var $sqlUser = 'b7_18949845';
	var $sqlPas = 'asdASDasd';
	var $sqlHost = 'sql106.byethost7.com';
	var $sqlDb = 'b7_18949845_pox';
	var $userId;
	var $userName;
	var $userType;
	var $action;

    const NAME_MAX_LENGTH = 15;

    private static $ENTITY_TABLE_MAP = array(
        'equip' => 'equipments',
        'contact' => 'contacts',
        'interview' => 'interviews'
    );

    function __construct() {
        $this->db = mysqli_connect($this->sqlHost, $this->sqlUser, $this->sqlPas) or die(mysqli_error($this->db));
        $this->siteDir = $_SERVER['DOCUMENT_ROOT'] . '/';
        $this->action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : 'index';
        if (!$this->isDbExists() && $this->action != 'create_db') {
            $this->action = 'index';
        } else {
            session_start();
            if (!empty($_SESSION['user_id']) && !empty($_SESSION['user_name']) && !empty($_SESSION['user_type']) && $this->isCurrentUserLive()) {
                $this->userType = $_SESSION['user_type'];
                $this->userName = $_SESSION['user_name'];
                $this->userId = $_SESSION['user_id'];
            } else {
                $this->userType = false;
                $this->userName = false;
                $this->userId = false;
            }
        }
    }

    function isDbExists() {
        return mysqli_select_db($this->db, $this->sqlDb);
    }

    function createDb() {
        if (mysqli_query($this->db, 'CREATE DATABASE IF NOT EXISTS `' . $this->sqlDb . '` CHARACTER SET utf8 COLLATE utf8_general_ci;') === true) {
            exec($this->mysqlDir . "mysql -h$this->sqlHost -u$this->sqlUser -p$this->sqlPas --default-character-set=utf8 -D$this->sqlDb < init.sql", $null, $isError);
            return true;
        }
        return false;
    }

    function importDb() {

        if ($_FILES['import_file']['name'] != '') {
            $filePath = str_replace('\\', '/', $_FILES['import_file']['tmp_name']);

            exec($this->mysqlDir . "mysql.exe -h$this->sqlHost -u$this->sqlUser -p$this->sqlPas --default-character-set=utf8 -D$this->sqlDb<$filePath", $null, $isError);
            if ($isError === 0) {
                return array(true, 'БД успешно импортирована');
            } else {
                if (mysqli_query($this->db, 'LOAD DATA INFILE "' . $filePath . '" REPLACE INTO TABLE `people`')) {
                    return array(true, 'Пользователи успешно импортированы');
                }
            }
        } else {
            return array(false, 'Выберите файл!');
        }
        return array(false, 'Неверный формат файла!');
    }

    /* mode:
     * 		0 - админов + обычных пользователей
     * 		1 - текущего админа
     * 		2 - обычных пользователей
     */



    function backup_database_tables($tables) {

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




//echo '<a href='/print "<script type=\"text/javascript\">document.location.href =\"index.php\";</script>";'>';




    function exportPeopleToCsv($mode = 0) {
        if ($this->userType != 1) {
            return 'Ошибка доступа! Только Админ может экспортировать пользователей';
        }
        switch ($mode) {
            case 1:
                $outFile = $this->siteDir . 'admin.csv';
                $sql = 'SELECT * INTO OUTFILE "' . $outFile . '" FROM `' . $this->sqlDb . '`.`people` WHERE `id`=' . $this->userId;
                break;
            case 2:
                $outFile = $this->siteDir . 'users.csv';
                $sql = 'SELECT * INTO OUTFILE "' . $outFile . '" FROM `' . $this->sqlDb . '`.`people` WHERE `type`=0';
                break;
            default:
                $outFile = $this->siteDir . 'people.csv';
                $sql = 'SELECT * INTO OUTFILE "' . $outFile . '" FROM `' . $this->sqlDb . '`.`people`';
                break;
        }
        //	unlink($outFile);
        if (mysqli_query($this->db, $sql) === true) {
            $this->returnFile($outFile);
        } else {
            echo mysqli_errno($this->db);
        }
    }

    /* mode:
     * 		0 - БД полностью
     * 		1 - пользователей
     * 		2 - текущего админа
     */

    function deleteDbData($mode = 0) {
        if ($this->userType != 1) {
            return 'Ошибка доступа! Только Админ может удалять что-то в таблице пользователей';
        }
        if ($mode == 2) {
            if (mysqli_query($this->db, 'DELETE FROM `' . $this->sqlDb . '`.`people` WHERE `id`=' . $this->userId) === true) {
                $o = mysqli_query($this->db, 'SELECT COUNT(*) AS `count` FROM `people` WHERE `type`=1');
                if ($o === false) {
                    return 'Админ удалён, но потом случилась ошибка запроса!';
                } else {
                    $userData = mysqli_fetch_object($o);
                    if ($userData->count > 0) {
                        return true;
                    } else {
                        $mode = 1;
                    }
                }
            } else {
                return 'Ошибка удаления записи Админа!';
            }
        }
        switch ($mode) {
            case 1:
                $sql = 'DELETE FROM `' . $this->sqlDb . '`.`people` WHERE `type`=2';
                break;
            default:
                $sql = 'DROP DATABASE `' . $this->sqlDb . '`';
                break;
        }
        if (mysqli_query($this->db, $sql) === true) {
            return true;
        } else {
            return 'Ошибка удаления!';
        }
    }

    function exportDbToSql() {
        if ($this->userType != 1) {
            return 'Ошибка доступа! Только Админ может экспортировать БД';
        }
        $outFile = $this->siteDir . 'db.sql';
        exec($this->mysqlDir . "mysqldump.exe -u$this->sqlUser -p$this->sqlPas $this->sqlDb>$outFile", $null, $isError);
        if (!$isError) {
            $this->returnFile($outFile);
        } else {
            return 'Ошибка экспорта!';
        }
    }

    function returnFile($filePath) {
        if (file_exists($filePath)) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($filePath));
            header('Content-Length: ' . filesize($filePath));
            ob_clean();
            flush();
            readfile($filePath);
            unlink($filePath);
            exit;
        } else {
            return false;
        }
    }

    /* type:
     * 		1 - админ
     * 		2 - обычный пользователь
     */

    function isAnyManExists($type) {
        $o = mysqli_query($this->db, 'SELECT * FROM `people` WHERE `type`=' . $type);
        if ($o !== false) {
            if (mysqli_fetch_object($o) !== null) {
                return true;
            }
        }
        return false;
    }

    /* type:
     * 		1 - админ
     * 		2 - обычный пользователь
     */

    function createMan($type) {
        if ($type == 2 && $this->userType != 1) {
            return 'Ошибка доступа! Только Админ может создавать новых пользователей';
        }

        $name = $_REQUEST['name'];
        $pass = $_REQUEST['password'];
        $rePass = $_REQUEST['repeat_password'];

        $errors = array();

        if (empty($name)) {
            $errors[] = 'Введите имя';
        } elseif (strlen($name) > self::NAME_MAX_LENGTH) {
            $errors[] = 'Имя должно содержать не более ' . self::NAME_MAX_LENGTH . ' символов';
        }
        if (empty($pass)) {
            $errors[] = 'Введите пароль';
        }
        if (empty($rePass)) {
            $errors[] = 'Повторите пароль';
        } elseif ($pass != $rePass) {
            $errors[] = 'Пароли не совпадают';
        }

        $o = mysqli_query($this->db, 'SELECT * FROM `people` WHERE `name`="' . $name . '"');
        if ($o === false) {
            $errors[] = mysqli_error($this->db);
        } else {
            $userData = mysqli_fetch_object($o);
            if ($userData !== null) {
                $errors[] = 'Такой аккаунт уже существует';
            }
        }
        if (empty($errors)) {
            if (!mysqli_query($this->db, 'INSERT INTO `people`(`name`, `password`, `type`) VALUES("' . $name . '", "' . md5($pass) . '", ' . $type . ')')) {
                $errors[] = mysqli_error($this->db);
            }
        }

        return $errors;
    }

    function login() {
        $name = $_REQUEST['name'];
        $pass = $_REQUEST['password'];

        $errors = array();

        if (empty($name)) {
            $errors[] = 'Введите имя';
        } elseif (strlen($name) > self::NAME_MAX_LENGTH) {
            $errors[] = 'Имя должно содержать не более ' . self::NAME_MAX_LENGTH . ' символов';
        }
        if (empty($pass)) {
            $errors[] = 'Введите пароль';
        }

        if (empty($errors)) {
            $o = mysqli_query($this->db, 'SELECT `id`, `name`, `type` FROM `people` WHERE `name`="' . $name . '" AND `password`="' . md5($pass) . '"');
            if ($o === false) {
                $errors[] = mysqli_error($this->db);
            } else {
                $userData = mysqli_fetch_object($o);
                if ($userData !== null) {
                    $_SESSION['user_id'] = $userData->id;
                    $_SESSION['user_name'] = $userData->name;
                    $_SESSION['user_type'] = $userData->type;
                } else {
                    $errors[] = 'Неверные данные для входа';
                }
            }
        }

        return $errors;
    }

    function logout() {
        $_SESSION['user_id'] = false;
        $_SESSION['user_name'] = false;
        $_SESSION['user_type'] = false;
        return true;
    }

    function isCurrentUserLive() {
        $o = mysqli_query($this->db, 'SELECT COUNT(*) AS `count` FROM `people` WHERE `id`="' . $_SESSION['user_id'] . '" AND `name`="' . $_SESSION['user_name'] . '"');
        if ($o === false) {
            return false;
        } else {
            $userData = mysqli_fetch_object($o);
            if ($userData->count == 1) {
                return true;
            }
        }
    }

    function countEquip() {
        $result = mysqli_query($this->db, 'SELECT COUNT(*) as count_equip FROM equipments');
        $counts = mysqli_fetch_assoc($result);
        $count_equip = $counts['count_equip'];
        return $count_equip;
    }

    function findEquip() {
        $result = $this->db->query('SELECT * FROM equipments');
        $rows = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
        //	return ($result === false)
        //	? false
        //	: $result->fetch_all(MYSQL_ASSOC);
    }

    function findUser() {
        $result = $this->db->query('SELECT * FROM people');
        $rows = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
        //	return ($result === false)
        //	? false
        //	: $result->fetch_all(MYSQL_ASSOC);
    }

    function findUserById($user_id) {
        $result = $this->db->query("SELECT * FROM people WHERE id = $user_id");

        return ($result === false) ? false : $result->fetch_assoc();
    }

    function findEquipById($equip_id) {
        $result = $this->db->query("SELECT * FROM equipments WHERE id = $equip_id");

        return ($result === false) ? false : $result->fetch_assoc();
    }

    function findEquipByName($name) {
        $name = trim($name);
        //$name = mysql_real_escape_string($name);
        $name = htmlspecialchars($name);
        $name = strip_tags($name);
        $name = htmlentities($name);
        $name = preg_replace("/[^a-z0-9]/i", "", $name);
        $result = $this->db->query("SELECT * FROM equipments WHERE `title` LIKE '%$name%' OR `year` LIKE '%$name%' OR `size` LIKE '%$name%' OR `stamp` LIKE '%$name%' OR `comp` LIKE '%$name%' OR `price` LIKE '%$name%'");
        $rows = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }

    function findContactByEquipId($equip_id) {
        $result = $this->db->query("SELECT * FROM contacts WHERE equip_id = '$equip_id'");
        $rows = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
        //return ($result === false)
        //	? false
        //	: $result->fetch_all(MYSQL_ASSOC);
    }

    function createEquip($title, $year, $size, $stamp, $comp, $price) {
        $stmt = $this->db->prepare(
                "INSERT INTO equipments (title, year, size, stamp, comp, price, owner_id)
			 VALUES (?, ?, ?, ?, ?, ?, $this->userId)"
        );
        $stmt->bind_param('ssssss', $title, $year, $size, $stamp, $comp, $price);
        $stmt->execute();
        $stmt->close();
    }

  /*  function updateUser($user_id, $name, $password, $type) {

        $stmt = $this->db->prepare(
            "UPDATE peple SET name = ?, password = ?, type = ?
			 WHERE id = ?"
        );
        $stmt->bind_param('ssssssii', $name, $password, $type, $user_id);
        $stmt->execute();
        $stmt->close();
    }*/

    function updateEquip($equip_id, $title, $year, $size, $stamp, $comp, $price, $owner_id) {

        $stmt = $this->db->prepare(
                "UPDATE equipments SET title = ?, year = ?, size = ?, stamp = ?, comp = ?, price = ?, owner_id = ?
			 WHERE id = ?"
        );
        $stmt->bind_param('ssssssii', $title, $year, $size, $stamp, $comp, $price, $owner_id, $equip_id);
        $stmt->execute();
        $stmt->close();
    }

    function createUsers($name, $password, $type) {
        $stmt = $this->db->prepare(
            "INSERT INTO people (name, password, type)
			 VALUES (?, ?, ?, ?, ?, ?, $this->userId)"
        );
        $stmt->bind_param('ssssss', $name, $password, $type);
        $stmt->execute();
        $stmt->close();
    }

    function removeEquip($equip_ids) {
        foreach ($equip_ids as $id) {
            $this->db->query("DELETE FROM equipments WHERE id = $id");
        }
    }
	
	function createUser($name, $password, $type) {
        $stmt = $this->db->prepare(
                "INSERT INTO people (name, password, type)
			 VALUES (?, ?, ?, ?, ?, ?, $this->userId)"
        );
        $stmt->bind_param('ssssss', $name, $password, $type);
        $stmt->execute();
        $stmt->close();
    }

    function updateUser($user_id, $name, $password, $type) {

        $stmt = $this->db->prepare(
                "UPDATE people SET name = ?, password = ?, type = ?
			 WHERE id = ?"
        );
        $stmt->bind_param('ssssssii', $name, $password, $type, $user_id);
        $stmt->execute();
        $stmt->close();
    }

    function removeUser($user_ids) {
        foreach ($user_ids as $id) {
            $this->db->query("DELETE FROM people WHERE id = $id");
        }
    }

    function createContact($equip_id, $firstName, $lastName, $middleName, $interest, $telephone, $email, $skype, $other) {
        $stmt = $this->db->prepare(
                "INSERT INTO contacts (first_name, last_name, middle_name, interest, telephone, email, skype, other, equip_id, owner_id)
			 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, $this->userId)"
        );
        $stmt->bind_param('ssssssssi', $firstName, $lastName, $middleName, $interest, $telephone, $email, $skype, $other, $equip_id);
        $stmt->execute();
        $stmt->close();
    }

    function findContactById($contact_id) {
        $result = $this->db->query("SELECT * FROM contacts WHERE id = $contact_id");

        return ($result === false) ? false : $result->fetch_assoc();
    }

    function updateContact($contact_id, $firstName, $lastName, $middleName, $interest, $telephone, $email, $skype, $other, $owner_id) {
        $stmt = $this->db->prepare(
                "UPDATE contacts SET first_name = ?, last_name = ?, middle_name = ?, interest = ?, telephone = ?, email = ?, skype = ?, other = ?, owner_id = ?
			 WHERE id = ?"
        );
        $stmt->bind_param('ssssssssii', $firstName, $lastName, $middleName, $interest, $telephone, $email, $skype, $other, $owner_id, $contact_id);
        $stmt->execute();
        $stmt->close();
    }

    function removeContact($contact_ids) {
        foreach ($contact_ids as $id) {
            $this->db->query("DELETE FROM contacts WHERE id = $id");
        }
    }

    function findInterviewByContactId($contact_id) {
        $result = $this->db->query("SELECT * FROM interviews WHERE contact_id = $contact_id");
        $rows = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
        //return ($result === false)
        //? false
        //: $result->fetch_all(MYSQL_ASSOC);
    }

    function createInterview($contact_id, $dateTime, $goal, $result) {
        if (empty($dateTime)) {
            $dateTime = null;
        }
        $stmt = $this->db->prepare(
                "INSERT INTO interviews (date_time, goal, result, contact_id, owner_id)
			 VALUES (?, ?, ?, ?, $this->userId)"
        );
        $stmt->bind_param('sssi', $dateTime, $goal, $result, $contact_id);
        $stmt->execute();
        $stmt->close();
    }

    function updateInterview($interview_id, $dateTime, $goal, $result, $owner_id) {
        $stmt = $this->db->prepare(
                "UPDATE interviews SET date_time = ?, goal = ?, result = ?, owner_id = ?
			 WHERE id = ?"
        );
        $stmt->bind_param('sssii', $dateTime, $goal, $result, $owner_id, $interview_id);
        $stmt->execute();
        $stmt->close();
    }

    function findInterviewById($interview_id) {
        $result = $this->db->query("SELECT * FROM interviews WHERE id = $interview_id");

        return ($result === false) ? false : $result->fetch_assoc();
    }

    function removeInterview($interview_ids) {
        foreach ($interview_ids as $id) {
            $this->db->query("DELETE FROM interviews WHERE id = $id");
        }
    }

    function findUsers() {
        $result = $this->db->query("SELECT * FROM people");
        $rows = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
        //return ($result === false)
        //? false
        //: $result->fetch_all(MYSQL_ASSOC);
    }



    function hasModificationAccess($entity, $id) {
        if ($this->userType == 1) {
            $hasAccess = true;
        } else {
            $table = self::$ENTITY_TABLE_MAP[$entity];

            $result = $this->db->query("SELECT owner_id FROM $table WHERE id = $id");
            $arr = $result->fetch_array();
            $hasAccess = ($arr[0] == $this->userId);
            $result->free();
        }

        return $hasAccess;
    }

}
