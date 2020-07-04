<?php

namespace Nuclear\system\model;

use Nuclear\app\orbe;
use Nuclear\system\model\resourceInterface;

class Executes implements resourceInterface
{
    const MYSQLI_ASSOC = 1;

    static    $connection  = null;
    protected $transaction = null;

    /**
     * Capitura o link de conecxão com o banco de dados
     */
    private function getConnection()
    {
        if(!isset($this->connection)){
            $this->connection = orbe::rescue('conn');
        }

        return $this->connection;
    }

    /**
     * Inicia transação
     * 
     * @return boolean
     */
    public function beginTransaction()
    {
        // desabilita autocommit
        $this->transaction = $this->query('START TRANSACTION');
        return true;
    }

    /**
     * Faz commit na transação iniciada
     * @return boolean
     * @throws EngineException
     */
    final public function commitTransaction()
    {
        $this->query('COMMIT');
        return true;
    }

    /**
     * Faz rollback na transação iniciada
     * @return boolean
     * @throws EngineException
     */
    final public function rollbackTransaction()
    {
        $this->query('ROLLBACK');
        return true;
    }

    /**
     * Executa uma instrução MySQL
     * 
     */
    final public function query(string $sql)
    {
        if(!isset($sql))
            return false;

        mysqli_query ($this->getConnection(), 'SET SQL_SAFE_UPDATES = 0;');
        $result = mysqli_query ($this->getConnection(), $sql);
        mysqli_query ($this->getConnection(), 'SET SQL_SAFE_UPDATES = 1;');
        return $result;
    }

    final public function totalRows($resource)
    {
        return mysqli_num_rows($resource);
    }

    /**
     * Executa uma instrução de seleção
     * 
     */
    public function select(string $sql)
    {
        return $this->query($sql);
    }

    /**
     * Devolve array associativo de todos os registros
     * 
     * @return array|null
     */
    public function fetchAll($resource, $type = self::MYSQLI_ASSOC)
    {
        if(!isset($resource) | empty($resource))
            return null;

        return $resource->fetch_all($type);
    }

    /**
     * Executa uma instrução sql de inserção
     * 
     * @param array $data
     * @param string $table
     */
    public function insert(string $sql)
    {
        if(!isset($sql))
            return false;

        return $this->query($sql);
    }

    /**
     * Executa uma instrução de update
     * 
     * @param array $data
     * @param string $table
     */
    public function update(string $sql)
    {
        if(!isset($sql))
            return false;

        return $this->query($sql);
    }

    /**
     * Executa uma instrução de inserção para o Resource
     * 
     * @param array $data
     * @param string $table
     */
    public function insertResource(array $data, string $table)
    {
        $fields = [];
        if(!isset($data) || !isset($table))
            return false;

        // extrai valores
        foreach($data as $index => $field){
            if($field->getValue() !== null)
                $fields[$index] = $field->getValue();
        }

        // forma sql
        $sql = sprintf(
            resourceInterface::EXECUTE_INSERT,
            $table,
            implode(', ', array_keys( $fields )),
            implode(', ', $fields)
        );

        // executa query
        return $this->query($sql);
    }

    /**
     * Executa uma instrução de update para o Resource
     * 
     * @param array $data
     * @param string $table
     */
    public function updateResource(array $data, string $table)
    {
        $fields  = [];
        $primary = [];
        if(!isset($data) || !isset($table))
            return false;

        // extrai valores
        foreach($data as $index => $field){
            if($field->getValue() !== null){
                $fields[] = $index.' = '.$field->getValue();
                if($field->get('Key') === 'PRI'){
                    $primary[] = $index.' = '.$field->getValue();
                }
            }
        }

        // forma sql
        $sql = sprintf(
            resourceInterface::EXECUTE_UPDATE,
            $table,
            implode(', ', $fields),
            implode(' AND ', $primary)
        );

        // executa query
        return $this->query($sql);
    }

    /**
     * Executa uma instrução de delete
     * 
     */
    public function delete(string $sql)
    {
        return $this->query($sql);
    }

    /**
     * Executa uma instrução de delete
     * 
     */
    public function deleteResource(array $data, string $table)
    {
        $primary = [];
        if(!isset($data) || !isset($table))
            return false;

        // extrai valores
        foreach($data as $index => $field){
            if($field->getValue() !== null){
                if($field->get('Key') === 'PRI'){
                    $primary[] = $index.' = '.$field->getValue();
                }
            }
        }

        // forma sql
        $sql = sprintf(
            "UPDATE %s SET active = 0 WHERE %s;",
            $this->table,
            implode(' AND ', $primary)
        );

        // executa query;
        return $this->query($sql);
    }
}
