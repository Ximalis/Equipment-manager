<?php

require_once 'dev.php';
require_once 'pox.php';

$pox = new Pox();

$html['title'] = '';
$html['errors'] = '';
$html['msg'] = '';
$html['body'] = '';
if ($pox->userType && $pox->userName && $pox->userId) {
    $breadcrumbs = array();
    switch ($pox->userType) {
        case 1: //Админ
            $htmlFileDir = 'admin/';
            $html['title'] = 'Здравствуйте, ' . $pox->userName . ', вы вошли в систему с правами администратора';
            $action_handled = true;
            switch ($pox->action) {
                case 'login':
                case 'index':
                    break;
                case 'create_user':
                    $html['title'] = 'Создание нового пользователя';
                    $html['file'] = $htmlFileDir . 'create-user.html';
                    if (isset($_POST['action']) && $_POST['action'] == 'create_user') {
                        $result = $pox->createMan(2);
                        if (empty($result)) {
                            $html['msg'] = 'Пользователь успешно создан';
                        } else {
                            $html['errors'] = $result;
                        }
                    }
                    break;
                
				 case 'edit_users':
                     $html['title'] = 'Пользователи';
                    if (isset($_POST['submit'])) {
                        $name = $_POST['name'];  
                        $password = $_POST['password'];    
                        $type = $_POST['type'];    
                        if (isset($_POST['id'])) {
                            $id = $_POST['id'];
                            $pox->updateUser($id, $name, $password, $type);
                            $html['msg'] = 'Данные обновлены';
                        } else {
                            $pox->createUsers($name, $password, $type);
                            $html['msg'] = 'Пользователь создан';
                        }
                    } elseif (isset($_POST['remove-users'])) {  
                        $user_ids = $_POST['user-ids'];
                        $pox->removeUser($user_ids);
                        $html['msg'] = 'Пользователь удалён';
                    }
                    $html['file'] = $htmlFileDir . 'edit-users.html';
                    $html['users'] = $pox->findUsers();
                    break;
                
                case 'edit_user':
                    $html['title'] = 'Пользователь';
                    $user_id = $_GET['id'];
                    $html['title'] = 'Пользователь ' . $user_id;
                    if ($pox->hasModificationAccess('user', $user_id)) {
                        $html['users'] = $pox->findUser();
                        $html['user'] = $pox->findUserById($equip_id);
                        $html['file'] = $htmlFileDir . 'edit-user.html';
                    } else {
                        $html['errors'] = 'У Вас нет прав на изменение этого объекта';
                    }
                    break;
                case 'test_export':
                    $html['title'] = 'Резервное копирование БД';
                    $html['errors'] = $pox->backup_database_tables($tables);
                    $html['file'] = $htmlFileDir . 'test-export.html';
                    break;
                case 'test_excel':
                    $html['title'] = 'Импорт из файла Excel';
                    //$html['errors'] = $pox->backup_database_tables($tables);
                    $html['file'] = $htmlFileDir . 'test-importexcel.html';
                    break;
                case 'export_db':
                    $html['errors'] = $pox->exportDbToSql();
                    break;
                case 'import_tbl':
                    $html['file'] = "import2.html";
                    //$html['errors'] = $pox->importDb();
                    break;
                case 'editpeople':
                    $html['file'] = "editpeople.php";
                    break;
                case 'import_db2':
                    $html['errors'] = $pox->importDb();
                    break;
                case 'export_admin':
                    $html['errors'] = $pox->exportPeopleToCsv(1);
                    break;
                case 'export_users':
                    $html['errors'] = $pox->exportPeopleToCsv(2);
                    break;
                case 'delete_db':
                    $result = $pox->deleteDbData();
                    if ($result == true) {
                        header('Location: index.php');
                        exit;
                    } else {
                        $html['errors'] = $result;
                    }
                    break;
                case 'delete_users':
                    $result = $pox->deleteDbData(1);
                    if ($result == true) {
                        $html['msg'] = 'Все пользователи удалены';
                    } else {
                        $html['errors'] = $result;
                    }
                    break;
                case 'delete_admin':
                    $result = $pox->deleteDbData(2);
                    if ($result == true) {
                        header('Location: index.php');
                        exit;
                    } else {
                        $html['errors'] = $result;
                    }
                    break;
                case 'logout':
                    $pox->logout();
                    header('Location: index.php');
                    break;
                default:
                    $action_handled = false;
                    break;
            }
            if ($action_handled) {
                break;
            }
        //Администратор имеет те же возможности , что и обычный пользователь
        //поэтому если запрос не был обработан в предущей ветке, то пытаемся
        //обработать его в секции простого пользователя
        case 2: //Обычный пользователь
            $htmlFileDir = 'user/';
            $html['title'] = 'Здравствуйте, ' . $pox->userName;
            switch ($pox->action) {
                case 'index':
                    break;
                case 'show_equipments': 
				$count1 = $pox->countEquip();
				//$count1 = $_POST['count_equip'];
                    $html['title'] = 'Оборудование';
                    if (isset($_POST['submit'])) {
                        $title = $_POST['title'];  //Наименование
                        $year = $_POST['year'];    //Год выпуска
                        $size = $_POST['size'];    //Формат
                        $stamp = $_POST['stamp'];  //Кол-во отт-в
                        $comp = $_POST['comp'];  //Комплектация
                        $price = $_POST['price'];  //Стоимость, Евро
                        if (isset($_POST['id'])) {
                            $id = $_POST['id'];
                            $owner_id = $_POST['owner_id'];
                            $pox->updateEquip($id, $title, $year, $size, $stamp, $comp, $price, $owner_id);
                            $html['msg'] = 'Оборудование изменено';
                        } else {
                            $pox->createEquip($title, $year, $size, $stamp, $comp, $price);
                            $html['msg'] = 'Оборудование создано';
                        }
                    } elseif (isset($_POST['remove-equipments'])) {  
                        $equip_ids = $_POST['equip-ids'];
                        $pox->removeEquip($equip_ids);
                        $html['msg'] = 'Оборудование удалено';
                    }
                    $html['file'] = $htmlFileDir . 'show-equipments.html';
                    $html['equipments'] = $pox->findEquip(); 
                    break;
                case 'show_equip':
                    $equip_id = $_GET['id'];
                    if (isset($_POST['submit'])) {
                        $firstName = $_POST['first-name'];
                        $lastName = $_POST['last-name'];
                        $middleName = $_POST['middle-name'];
                        $interest = $_POST['interest'];
                        $telephone = $_POST['telephone'];
                        $email = $_POST['email'];
                        $skype = $_POST['skype'];
                        $other = $_POST['other'];
                        if (isset($_POST['id'])) {
                            $owner_id = $_POST['owner_id'];
                            $pox->updateContact($_POST['id'], $firstName, $lastName, $middleName, $interest, $telephone, $email, $skype, $other, $owner_id);
                            $html['msg'] = 'Контакт изменен';
                        } else {
                            $pox->createContact($equip_id, $firstName, $lastName, $middleName, $interest, $telephone, $email, $skype, $other);
                            $html['msg'] = 'Контакт создан';
                        }
                    } elseif (isset($_POST['remove-contacts'])) {
                        $contact_ids = $_POST['contact-ids'];
                        $pox->removeContact($contact_ids);
                        $html['msg'] = 'Контакты удалены';
                    }
                    $html['equip'] = $pox->findEquipById($equip_id);
                    $html['title'] = 'Оборудование ' . $equip_id;
                    $html['file'] = $htmlFileDir . 'show-equip.html';
                    $html['contacts'] = $pox->findContactByEquipId($equip_id);
                    break;
                case 'search_results':
                    $name = $_GET['name'];
                    $name = trim($name);
                    $name = htmlspecialchars($name);
                    $name = strip_tags($name);
                    $name = htmlentities($name);
                    $name = preg_replace("/[^a-z0-9]/i", "", $name);
                    $html['title'] = 'Поиск: ' . $name;
                    if (isset($_POST['submit'])) {
                        $title = $_POST['title'];  //Наименование
                        $year = $_POST['year'];    //Год выпуска
                        $size = $_POST['size'];    //Формат
                        $stamp = $_POST['stamp'];  //Кол-во отт-в
                        $comp = $_POST['comp'];  //Комплектация
                        $price = $_POST['price'];  //Стоимость, Евро
                        if (isset($_POST['id'])) {
                            $id = $_POST['id'];
                            $owner_id = $_POST['owner_id'];
                            $pox->updateEquip($id, $title, $year, $size, $stamp, $comp, $price, $owner_id);
                            $html['msg'] = 'Оборудование изменено';
                        } else {
                            $pox->createEquip($title, $year, $size, $stamp, $comp, $price);
                            $html['msg'] = 'Оборудование создано';
                        }
                    } elseif (isset($_POST['remove-equipments'])) { 
                        $equip_ids = $_POST['equip-ids'];
                        $pox->removeEquip($equip_ids);
                        $html['msg'] = 'Оборудование удалено';
                    }
                    if (!empty($name)) {
                        $html['file'] = $htmlFileDir . 'show-equipments.html';
                        $html['equipments'] = $pox->findEquipByName($name);
                    } else {
                        
                        $html['msg'] = 'Введите данные для поиска';
                        $html['title'] = '';
                    }
                    break;
                case 'edit_equip':
                    $equip_id = $_GET['id'];
                    $html['title'] = 'Оборудование ' . $equip_id;
                    if ($pox->hasModificationAccess('equip', $equip_id)) {
                        $html['users'] = $pox->findUser();
                        $html['equip'] = $pox->findEquipById($equip_id);
                        $html['file'] = $htmlFileDir . 'edit-equip.html';
                    } else {
                        $html['errors'] = 'У Вас нет прав на изменение этого объекта';
                    }
                    break;
                case 'create_equip':
                    $html['title'] = 'Создание оборудования';
                    $html['file'] = $htmlFileDir . 'create-equip.html';
                    break;
                case 'create_contact':
                    $equip_id = $_GET['equip_id'];
                    array_push($breadcrumbs, $equip_id);
                    $html['equip'] = $pox->findEquipById($equip_id);

                    $html['title'] = 'Создание контакта для продажи оборудования ' . $equip_id;
                    $html['file'] = $htmlFileDir . 'create-contact.html';
                    break;
                case 'edit_contact':
                    $contact_id = $_GET['id'];
                    $html['title'] = 'Контакт ' . $contact_id;
                    if ($pox->hasModificationAccess('contact', $contact_id)) {
                        $html['users'] = $pox->findUser();
                        $contact = $pox->findContactById($contact_id);
                        $html['contact'] = $contact;
                        array_push($breadcrumbs, $contact->equip_id);
                        $html['file'] = $htmlFileDir . 'edit-contact.html';
                    } else {
                        $html['errors'] = 'У Вас нет прав на изменение этого объекта';
                    }
                    break;
                case 'show_contact':
                    $contact_id = $_GET['id'];
                    $contact = $pox->findContactById($contact_id);
                    array_push($breadcrumbs, $contact["equip_id"]);
                    if (isset($_POST['submit'])) {
                        $dateTime = $_POST['date-time'];
                        $goal = $_POST['goal'];
                        $result = $_POST['result'];
                        if (isset($_POST['id'])) {
                            $owner_id = $_POST['owner_id'];
                            $pox->updateInterview($_POST['id'], $dateTime, $goal, $result, $owner_id);
                            $html['msg'] = 'Беседа изменена';
                        } else {
                            $pox->createInterview($contact_id, $dateTime, $goal, $result);
                            $html['msg'] = 'Беседа создана';
                        }
                    } elseif (isset($_POST['remove-interviews'])) {
                        $interview_ids = $_POST['interview-ids'];
                        $pox->removeInterview($interview_ids);
                        $html['msg'] = 'Беседы удалены';
                    }
                    $html['contact'] = $contact;
                    $html['title'] = 'Контактное лицо ' . $contact_id;
                    $html['file'] = $htmlFileDir . 'show-contact.html';
                    $html['interviews'] = $pox->findInterviewByContactId($contact_id);
                    break;
                case 'create_interview':
                    $contact_id = $_GET['contact_id'];
                    $contact = $pox->findContactById($contact_id);
                    array_push($breadcrumbs, $contact["equip_id"], $contact_id);
                    $html['contact'] = $contact;
                    $html['title'] = 'Создание беседы для контакта ' . $contact_id;
                    $html['file'] = $htmlFileDir . 'create-interview.html';
                    break;
                case 'edit_interview':
                    $interview_id = $_GET['id'];
                    $interview = $pox->findInterviewById($interview_id);
                    $contact_id = $interview["contact_id"];
                    $contact = $pox->findContactById($contact_id);
                    array_push($breadcrumbs, $contact["equip_id"], $contact_id);
                    $html['title'] = 'Беседа ' . $interview_id;
                    if ($pox->hasModificationAccess('interview', $interview_id)) {
                        $html['users'] = $pox->findUser();
                        $html['interview'] = $interview;
                        $html['file'] = $htmlFileDir . 'edit-interview.html';
                    } else {
                        $html['errors'] = 'У Вас нет прав на изменение этого объекта';
                    }
                    break;
                case 'show_interview':
                    $interview_id = $_GET['id'];
                    $interview = $pox->findInterviewById($interview_id);
                    $contact_id = $interview["contact_id"];
                    $contact = $pox->findContactById($contact_id);
                    array_push($breadcrumbs, $contact["equip_id"], $contact_id);
                    $html['interview'] = $interview;
                    $html['title'] = 'Беседа ' . $interview_id;
                    $html['file'] = $htmlFileDir . 'show-interview.html';
                    break;
                case 'logout':
                    $pox->logout();
                    header('Location: index.php');
                    break;
                default:
                    $wtf = true;
                    break;
            }
            break;
        default:
            $wtf = true;
            break;
    }
    $html['breadcrumbs'] = $breadcrumbs;
} else {
    switch ($pox->action) {
        case 'index':
            if (!$pox->isDbExists()) {
                $html['title'] = 'ПОКС';
                $html['errors'] = 'База данных "' . $pox->sqlDb . '" отсутствует на сервере';
                $html['file'] = 'create-db.html';
            } else {
                if ($pox->isAnyManExists(1)) {
                    $html['file'] = 'login.html';
                } else {
                    $html['title'] = 'Инициализация';
                    $html['msg'] = 'База данных "' . $pox->sqlDb . '" успешно создана';
                    $html['file'] = 'import-or-create-admin.html';
                }
            }
            break;
        case 'create_db':
            if ($pox->createDb()) {
                $html['title'] = 'Инициализация';
                $html['msg'] = 'База данных "' . $pox->sqlDb . '" успешно создана';
                $html['file'] = 'import-or-create-admin.html';
            } else {
                $html['title'] = 'Ошибка!';
                $html['errors'] = 'Возникла ошибка при создании база данных "' . $pox->sqlDb . '"';
            }
            break;
        case 'import_or_create_admin':
            switch ($_REQUEST['choise']) {
                case 'Импорт БД':
                    //$html['title'] = 'Импорт БД';
                    $html['file'] = 'import.html';
                    break;
                case 'Создание Администратора':
                    $html['title'] = 'Создание Администратора';
                    $html['file'] = 'admin/create-admin.html';
                    break;
                default:
                    $wtf = true;
                    break;
            }
            break;
        case 'import':
            list($isImportSuccess, $importMessage) = $pox->importDb();
            if ($isImportSuccess) {
                if ($pox->isAnyManExists(1)) {
                    $html['title'] = 'Войти';
                    $html['msg'] = $importMessage;
                    $html['file'] = 'login.html';
                } else {
                    $html['title'] = 'Создание Админа';
                    $html['msg'] = $importMessage;
                    $html['file'] = 'admin/create-admin.html';
                }
            } else {
                $html['title'] = 'Импорт БД';
                $html['errors'] = $importMessage;
                $html['file'] = 'import.html';
                break;
            }
            break;
        case 'create-admin':
            $result = $pox->createMan(1);
            if (empty($result)) {
                $html['title'] = 'Войти';
                $html['msg'] = 'Администратор успешно создан';
                $html['file'] = 'login.html';
            } else {
                $html['title'] = 'Создание Админа';
                $html['errors'] = $result;
                $html['file'] = 'admin/create-admin.html';
            }
            break;
        case 'login':
            $result = $pox->login();
            if (empty($result)) {
                header('Location: index.php');
                exit;
            } else {
                $html['title'] = 'Войти';
                $html['errors'] = $result;
                $html['file'] = 'login.html';
            }
            break;
        default:
            $wtf = true;
            break;
    }
}

if (is_array($html['errors'])) {
    $html['errors'] = implode('<br />', $html['errors']);
}

if (isset($wtf)) {
    $html['title'] = 'Ошибка!';
    $html['errors'] = 'Необработанное нечто';
}
require_once 'main.html';
