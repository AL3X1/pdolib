<?php
namespace App;

use App\Config\DB;

class QueryBuilder
{
    private $sql;

    private $pdo;

    private $host;

    private $db_name;

    private $user_name;

    private $password;

    /**
     * QueryBuilder constructor.
     * @param string $host
     * @param string $db_name
     * @param string $user_name
     * @param string $password
     */
    public function __construct($host = "", $db_name = "", $user_name = "", $password = "")
    {
        if (isset($host, $db_name, $user_name, $password)) {
            $this->host = $host;
            $this->db_name = base64_encode($db_name);
            $this->user_name = base64_encode($user_name);
            $this->password = base64_encode($password);
        } else {
            $this->host = DB::$host;
            $this->db_name = base64_encode(DB::$db_name);
            $this->user_name =base64_encode( DB::$user_name);
            $this->password = base64_encode(DB::$password);
        }

        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . base64_decode($this->db_name) . ";charset=" . DB::DEFAULT_CHARSET;
            $this->pdo = new \PDO($dsn, base64_decode($this->user_name), base64_decode($this->password), DB::DEFAULT_SETTINGS);
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Выборка
     * @param $select - оператор выбора
     * @param $from
     *
     * прим. $query->select("*", "table1");
     * Пример аналогичен SQL коду: SELECT * FROM `table1`
     * @return $this
     */
    public function select($select, $from)
    {
        $this->sql = "SELECT $select FROM `$from`";
        return $this;
    }

    /**
     * Оператор Where
     * @param array $data - ассоциативный массив
     * Прим. $query->select("*", "table1")->where(["name" => "Alex"])
     * @return $this
     */
    public function where(array $data)
    {
        foreach ($data as $key => $value) {
            $this->sql .= " WHERE `$key` = '$value'";
        }

        return $this;
    }

    /**
     * Оператор сортировки (ORDER BY)
     * @param $by
     * @param string $type - типы сортировки ASC, DESC
     *
     * Прим. $query->select("*", "table1")->where(["name" => "Alex"])->orderBy("id", "ASC");
     * @return $this
     */
    public function orderBy($by, $type = "")
    {
        if ($type) {
            $this->sql .= " ORDER BY $by $type";
        } else {
            $this->sql .= " ORDER BY $by";
        }

        return $this;
    }

    /**
     * Обновление данных
     * @param $table
     * @param array $data
     *
     * Прим. $query->update("table1", ["name" => "Alex1"]);
     * @return $this
     */
    public function update($table, array $data)
    {
        foreach ($data as $key => $value) {
            $this->sql = "UPDATE `$table` SET `$key` = '$value'";
        }

        return $this;
    }

    /**
     * Вставка данных
     * @param $into
     * @param array $data
     *
     * Прим. $query->insert("table1", ["name" => "Alex"]);
     * @return $this
     */
    public function insert($into, array $data)
    {
        $params = "";
        foreach ($data as $key => $value) {
            $params .= "`$key` = '$value',";
        }

        $this->sql = "INSERT INTO `$into` SET " . mb_substr($params, 0, -1);

        return $this;
    }

    /**
     * Лимит
     * @param $number
     *
     * Прим. $query->select("*", "table1")->limit(10);
     * @return $this
     */
    public function limit($number)
    {
        $this->sql .= " LIMIT $number";
        return $this;
    }


    /**
     * Удаление записи
     * @param $from
     * @param array $where
     *
     * прим. $sun->delete("users", ["username" => "Alex"])->execute();
     * @return $this
     */
    public function delete($from, array $where)
    {
        $params = "";
        foreach ($where as $row => $value) {
            $params .= "`$row`='$value'";
        }

        $this->sql = "DELETE FROM `$from` WHERE $params";
        return $this;
    }

    /**
     * Выполнение запроса
     *
     * Прим. $query->select("*", "table1")->execute();
     * @return \PDOStatement
     */
    public function execute()
    {
        $exec = $this->pdo->prepare($this->sql);
        $exec->execute();

        return $exec;
    }

    /**
     * Запрос вручную
     *
     * @param $sql
     *
     * Прим. $select = $query->query("INSERT INTO `users` (username, email) VALUES ('Alex', 'test@gmail.com')")->execute();
     * @return \PDOStatement
     */
    public function query($sql)
    {
        if ($this->pdo->query($sql))
            return true;
    }

    public function debug($data, $var_dump = false)
    {
        echo "<pre>";
        if ($var_dump) {
            var_dump($data);
        } else {
            print_r($data);
        }
        echo "</pre>";
    }
}
