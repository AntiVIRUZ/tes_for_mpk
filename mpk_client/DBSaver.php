<?php

/**
 * Класс для сохранения информации в базу данных
 * 
 * Работает с любой СУБД, поддерживаемой технологией PDO, при уловии,
 * что в PHP установлен соответствующий драйвер
 */

include_once 'iSaver.php';
include_once 'DBSettings.php';

class DBSaver implements iSaver {

    /**
     * Список участников соревнования
     * 
     * Хранится в формате, соответствующем формату БД
     * @access private
     * @var array
     */
    private $participants;
    /**
     * Последняя ошибка, возникшая при отправке запроса БД<br>
     * [0] - Код ошибки SQLSTATE (пятисимвольный код состоящий из букв и цифр, определенный в стандарте ANSI SQL).<br>
     * [1] - Код ошибки, возвращаемый драйвером.<br>
     * [2] - Сообщение об ошибке, выданное драйвером.<br>
     * @access private
     * @var array 
     */
    private $lastError;
    /**
     * Экземпляр соединения с базой данных
     * @access private
     * @var PDO
     */
    private $pdo;
    /**
     * Статус состояния подключения к БД
     * TRUE - подключено, FALSE - отключено
     * @access private
     * @var boolean
     */
    private $connectionStatus;
    /**
     * Экземпляр класса настроек подключения по умолчанию
     * @access private
     * @var DBSettings
     */
    private $dbSettings;
    /**
     * Последний результирующий набор запроса к БД 
     * @access private
     * @var PDOStatement
     */
    private $lastStatement;
    /**
     * Список таблиц в порядке занесения значений (для сохранения целостности ключей)
     * @access private
     * @var array
     */
    private $tables = array( "sports_kinds", "members", "teams", "members_teams");

    /**
     * Метод-конструктор. Загружает настройки соединения по умолчанию
     */
    function __construct() {
        $this->connectionStatus = false;
        try {
            $this->dbSettings = new DBSettings();
        } catch (Exception $e) {
            //@TODO Добавить логирование
            die($e->getMessage());
        }
    }

    /**
     * Получить статус подключения к БД
     * @return boolean TRUE - подключено, FALSE - отключено
     */
    public function GetConnectionStatus() {
        return $this->connectionStatus;
    }

    /**
     * Получить последнюю ошибку запроса к БД
     * 
     * Последняя ошибка, возникшая при отправке запроса БД
     * @access private
     * @return array [0] - Код ошибки SQLSTATE (пятисимвольный код состоящий из букв и цифр, определенный в стандарте ANSI SQL).<br>
     * [1] - Код ошибки, возвращаемый драйвером.<br>
     * [2] - Сообщение об ошибке, выданное драйвером.
     */
    public function GetLastError() {
        return $this->lastError;
    }

    /**
     * Устанавливает список участников для дальнейшего сохранения
     * @param array $participants Массив команд (экземпляров класса Team)
     */
    public function SetParticipants($participants) {
        $this->participants = $this->ParseFromTeams($participants);
    }

    /**
     * Сохраняет список участников в базу данных
     * @return boolean TRUE если сохранение прошло удачно, FALSE в ином случае
     * @todo Написать обработкуошибок сохранения и откат после них.
     */
    public function Save() {
        foreach ($this->tables as $table) {
            if (!$this->SaveTable($table, $this->participants[$table])) {
                return FALSE;
            }
        }
        return TRUE;
    }

    /**
     * Устанавливает соединение к базе данных с настройками по умолчанию
     * @return boolean TRUE при успешном соединенит, FALSE в ином случае
     */
    public function ConnectToDB() {
        return $this->ConnectToSpecificBD($this->dbSettings->getDbType(), $this->dbSettings->getServername(), $this->dbSettings->getUsername(), $this->dbSettings->getPassword(), $this->dbSettings->getDatabase());
    }
    
    /**
     * Устанавливает соединение к базе данных с пользовательскими настройками
     * @param string $dbType тип СУБД
     * @param string $servername адресс сервера
     * @param string $username логин
     * @param string $password пароль
     * @param string $database название базы данных
     * @return boolean TRUE если соединение установлено, FALSE в ином случае
     */
    public function ConnectToSpecificBD($dbType, $servername, $username, $password, $database) {
        if (!$this->checkSupportingDBDriver($this->dbSettings->getDbType())) {
            trigger_error("Драйвер базы данных не поддерживается или указан неверно", E_USER_ERROR);
        }

        if ($this->connectionStatus) {
            $this->DisconnectFromDB();
        }

        $connectionString = $this->dbSettings->getDbType() . ":";
        $connectionString .= "host=" . $servername;

        try {
            $this->pdo = new PDO($connectionString, $username, $password);
        } catch (Exception $e) {
            //@TODO Добавить логирование
            $this->lastError = $e->getMessage();
            return FAlSE;
        }

        if ($this->CreateDatabaseIfNotExists($database)) {
            $this->SelectDatabase($database);
            $this->DeleteTables();
            $this->CreateTables();
        } else {
            return false;
        }
        $this->connectionStatus = true;
        return true;
    }

    /**
     * Выбирает базу данных
     * @param string $name имя базы данных
     * @return boolean true если база данных успешно выбрана, false в ином случае
     * @todo изменить запрос к БД на изменение переменной $database. Написать другую приватную функцию с этим запросом
     */
    public function SelectDatabase($name) {
        return $this->SentQuery("use `" . $name . "`;");
    }

    /**
     * Парсит список участников.
     * 
     * Парсит из массива команд (Team) в представление, соответствующее представлению базы данных
     * @param array $array Массив команд (Team)
     * @return array массив соответствующий по структуре БД 
     */
    private function ParseFromTeams($array) {
        $sportsKindsNumber = 0;
        $membersNumber = 0;
        $teamsNumber = 0;
        $membersToTeamsNumber = 0;
        $sportsKindsId;
        $memberList;

        $result = array();
        foreach ($this->tables as $value) {
            $result[$value] = array();
        }

        foreach ($array as $value) {
            $sportsKindsId = $this->SearchByName($result["sports_kinds"], $value->sportsKind);
            if ($sportsKindsId === FALSE) {
                $sportsKindsNumber++;
                $sportsKindsId = $sportsKindsNumber;
                $result["sports_kinds"][$sportsKindsNumber] = array("id" => $sportsKindsNumber, "name" => $value->sportsKind);
            }

            $teamsNumber++;
            $result["teams"][$teamsNumber] = array("id" => $teamsNumber, "name" => $value->name, "sports_kind_id" => $sportsKindsId, "motto" => $value->motto);

            $memberList = $value->GetMembersList();
            foreach ($memberList as $memberName) {
                $memberId = $this->SearchByName($result["members"], $memberName);
                if ($memberId === FALSE) {
                    $member = $value->GetMemberBy($memberName, "name");
                    $membersNumber++;
                    $memberId = $membersNumber;
                    $result["members"][$membersNumber] = array("id" => $membersNumber, "name" => $member->name, "passport" => $member->passport);
                }

                $membersToTeamsNumber++;
                $result["members_teams"][$membersToTeamsNumber] = array("id" => $membersToTeamsNumber, "member_id" => $memberId, "team_id" => $teamsNumber);
            }
        }
        return $result;
    }

    /**
     * Ищет среди массива элемента со значением $name в поле "name"
     * 
     * Используйте строгое (===) сравнения для проверки найден элемент, или нет, так как возвращаемый ключ может быть равен нулю
     * @param array $array Массив для поиска
     * @param string $name Значение поля
     * @return mixed ключ элемента, если найден, FALSE, если не найден
     */
    private function SearchByName($array, $name) {
        foreach ($array as $key => $value) {
            if ($value["name"] == $name)
                return $key;
        }
        return FALSE;
    }

    /**
     * Массив в таблице базы данных
     * 
     * Элементы массива должены быть массивами, составлеными в виде $key => $value,
     * где $key - столбец таблицы, а $value - значение
     * @param string $name Имя таблицы
     * @param array $values Массив значений
     * @return boolean TRUE, если сохранение прошло удачно, FALSE в противном случае
     */
    private function SaveTable($name, $values) {
        $params = array();
        $sql = "INSERT INTO `" . $name . "` (";
        foreach ($values[1] as $key => $field) {
            $sql .= $key . ",";
        }
        $sql[strlen($sql) - 1] = ") ";
        $sql .= "VALUES";

        foreach ($values as $field) {
            $sql .= "(";
            foreach ($field as $value) {
                $sql .= "?,";
                array_push($params, $value);
            }
            $sql[strlen($sql) - 1] = ")";
            $sql .= ",";
        }
        $sql[strlen($sql) - 1] = ";";
        return $this->SentQuery($sql, $params);
    }

    /**
     * Увеличивает поле "id" всех элементов массива $values на значние $num
     * @param array $values массив элементов
     * @param int $num значение инкремента
     */
    private function IncrementIds(&$values, $num) {
        foreach ($values as $key => $value) {
            $values[$key]["id"] += $num;
        }
        echo "num = " . $num;
    }

    /**
     * Получает максимальное значение столбца "id" таблицы $name
     * @param string $name название таблицы
     * @return int максимальное значение столбца "id"
     */
    private function GetMaxIdFromTable($name) {
        $result = $this->SentQuery("SELECT MAX(id) FROM " . $name);
        if ($result) {
            $info = $this->lastStatement->fetch();
            return $info["MAX(id)"];
        }
    }

    /**
     * Проверяет, поддерживает ли PHP драйвер $driverName
     * @param string $driverName Имя драйвера СУБД
     * @return boolean TRUE, если PHP поддерживает драйвер, FALSE в противном случае
     */
    private function checkSupportingDBDriver($driverName) {
        $avaiableDrivers = PDO::getAvailableDrivers();
        if (in_array($driverName, $avaiableDrivers)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Разрывает соединение с БД
     */
    private function DisconnectFromDB() {
        $this->pdo = NULL;
    }

    /**
     * Создает базу данных с названием $database, если такая не существует
     * @param string $database название базы данных
     * @return boolean TRUE, если база данных успешно создана, FALSE в противном случае
     */
    private function CreateDatabaseIfNotExists($database) {
        return $this->SentQuery("CREATE DATABASE IF NOT EXISTS `" . $database . "` CHARACTER SET utf8 COLLATE utf8_general_ci;");
    }

    /**
     * Создает таблицы связанные с соревнованием для последующего сохранения в них информации
     */
    private function CreateTables() {

        $sql = "CREATE TABLE IF NOT EXISTS `sports_kinds` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `name` varchar(50) NOT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $this->SentQuery($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `members` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `name` varchar(50) NOT NULL,
                    `passport` varchar(50) NOT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $this->SentQuery($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `teams` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `name` varchar(50) NOT NULL,
                    `sports_kind_id` int(11) NOT NULL,
                    `motto` varchar(200),
                    PRIMARY KEY (`id`),
                    KEY `t2sk` (`sports_kind_id`),
                    CONSTRAINT `t2sk` FOREIGN KEY (`sports_kind_id`) REFERENCES `sports_kinds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $this->SentQuery($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `members_teams` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `member_id` int(11) NOT NULL,
                    `team_id` int(11) NOT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `member_id_team_id` (`member_id`,`team_id`),
                    KEY `mt2t` (`team_id`),
                    CONSTRAINT `mt2t` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                    CONSTRAINT `mt2m` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $this->SentQuery($sql);
    }

    /**
     * Удаляет таблицы, связанные с соревнованием
     */
    private function DeleteTables() {
        foreach (array_reverse($this->tables) as $table) {
            $sql = "DROP TABLE IF EXISTS `" . $table . "`;";
            if (!$this->SentQuery($sql)) {
                trigger_error("Ошибка сохранения базы данных", E_USER_ERROR);
            }
        }
    }

    /**
     * Посылает SQL запрос в БД
     * 
     * Используется метод PDO::prepare 
     * @link http://php.net/manual/ru/pdostatement.execute.php Смотри подробнее
     * @param string $sql SQL запрос
     * @param array $input_params массив входных параметров SQL запроса
     * @return boolean TRUE если запрос выполнен успешно, FALSE в случае возникновения ошибок
     */
    private function SentQuery($sql, $input_params = NULL) {
        $sth = $this->pdo->prepare($sql);
        if ($input_params) {
            $result = $sth->execute($input_params);
        } else {
            $result = $sth->execute();
        }
        if (!$result) {
            print_r($sth->errorInfo());
            $this->lastError = $sth->errorInfo();
        } else {
            $this->lastStatement = $sth;
        }
        return $result;
    }

    /**
     * Метод-деструктор. Разрывает соединение с БД
     */
    function __destruct() {
        $this->DisconnectFromDB();
    }

}
