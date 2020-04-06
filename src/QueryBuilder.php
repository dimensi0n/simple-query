<?php
namespace SimpleQuery;

/**
 * Provides a simple query builder
 * @author Erwan ROUSSEL
 */
class QueryBuilder extends \PDO 
{
    
    /**
     * @param mixed $dsn
     * @param null $username
     * @param null $password
     * @param array $driver_options
     * 
     * @return void
     */
    public function __construct($dsn, $username = null, $password = null, array $driver_options = null) {
         parent :: __construct($dsn, $username, $password, $driver_options);
         $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * @param string $key
     * @param mixed $value
     * 
     * @return string
     */
    private function getParamName(string $key, $value) : string
    {
        $length = strlen((string)$value);
        $param = $length  < 5 ? $key.mb_substr($value, 0,$length) : $key.mb_substr($value, 0,5);
        
        return $param;
    }

    /**
     * @param array $data
     * 
     * @return string
     */
    private function  generateKeys(array $data) : string
    {
        $string = '';
        $ctr = 0;
        foreach ($data as $key => $value) {
            $param = $this->getParamName($key, $value);
            $ctr === 0 ? $string .= $key.' = :'.$param : $sql .= ', '.$key.' = :'.$param;
            $ctr++;
        }
        return $string;
    }

    /**
     * @param array $data
     * @param \PDOStatement $statement
     * 
     * @return array
     */
    private function bindParams(array $data, \PDOStatement $statement) : array
    {
        $array = [];
        foreach ($data as $key => $value) {
            $param = $this->getParamName($key, $value);
            $dumpValue = $value; // avoid string conversion
            $statement->bindParam($param, $dumpValue);
            $array[$param] = $value;
        }
        return $array;
    }

    /**
     * @param string $name
     * @param array $fields
     * 
     * @return QueryBuilder
     */
    public function create(string $name, array $fields) : QueryBuilder
    {
        try {
            $sql = 'CREATE TABLE IF NOT EXISTS '.$name.' ( id INTEGER PRIMARY KEY';
            foreach ($fields as $key => $value) {
                $sql .= ", ".$key." ".$value['type'];
                isset($value['notNullable']) ? $sql .= ' NOT NULL ': '';
                isset($value['unique']) ? $sql .= ' UNIQUE ' : '';
                isset($value['default']) ? $sql.= ' DEFAULT '.$value['default'] : '';
            }
            $sql .= ' )';
            $this->query($sql);
            return $this;
        } catch (\PDOException $exception) {
            throw $exception;
        }

        return $this;
    }

    /**
     * @param string $name
     * @param string $opts=''
     * 
     * @return QueryBuilder
     */
    public function dropTable(string $name, string $opts='') : QueryBuilder
    {
        try {
            $sql = 'DROP TABLE IF EXISTS '.$name.' '.$opts;
            $this->query($sql);
        } catch (\PDOException $exception) {
            throw $exception;
        }

        return $this;
    }

    /**
     * @param string $into
     * @param array $data
     * 
     * @return QueryBuilder
     */
    public function insert(string $into, array $data) : QueryBuilder
    {
        try {
            $keys = array_keys($data);
            $sql = 'INSERT INTO '.$into.' ('.implode(', ',$keys).') VALUES ( :'.implode(', :', $keys).' )';
            $statement = $this->prepare($sql);
            $statement->execute($data);
        } catch (\PDOException $exception) {
            throw $exception;
        }

        return $this;
    }

    /**
     * @param string $from
     * @param ?array $fields
     * @param ?array $opts
     * 
     * @return PDOStatement
     */
    public function select(string $from, ?array $fields = null, ?array $where = null ,?array $opts = null) : \PDOStatement
    {
        try {
            if(isset($fields)) {
                $sql = 'SELECT ';
                $sql .= implode(', ', $fields);
            } else {
                $sql = 'SELECT *';
            }
            $sql .= ' FROM '.$from.' ';
            isset($where) ? $sql .= ' WHERE '.$this->generateKeys($where) : null;
            isset($opts) ? $sql .= implode(' ', $opts) : null;

            $statement = $this->prepare($sql);
            isset($where) ? $this->bindParams($where, $statement) : null;
            $statement->execute();
            return $statement;
        } catch (\PDOException $exception) {
            throw $exception;
        }
    }

    /**
     * @param string $table
     * @param array $set
     * @param array $where
     * 
     * @return QueryBuilder
     */
    public function update(string $table, array $set, array $where) : QueryBuilder
    {
        try {
            $sql = 'UPDATE '.$table.' SET ';
            $sql .= $this->generateKeys($set);
            $sql .= ' WHERE ';
            $sql .= $this->generateKeys($where);
            $statement = $this->prepare($sql);
            $set = $this->bindParams($set, $statement);
            $where = $this->bindParams($where, $statement);
            $params = $set + $where;
            $statement->execute($params);
        } catch (\PDOException $exception) {
            throw $exception;
        }   
        return $this;
    }

    /**
     * @param string $table
     * @param array $where
     * 
     * @return QueryBuilder
     */
    public function delete(string $table, array $where) : QueryBuilder
    {
        try {
            $sql = 'DELETE FROM '.$table.' WHERE '.$this->generateKeys($where);
            $statement = $this->prepare($sql);
            $params = $this->bindParams($where, $statement);
            $statement->execute($params);
        } catch (\PDOException $exception) {
            throw $exception;
        }
        return $this;
    }
}
