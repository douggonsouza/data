<?php

namespace data\resource;

use data\connection\conn;

class resource
{
    protected static $conn;
    protected $data;
    protected $resource;

    public function resource(string $sql)
    {
        $this->setConn(conn::getConnection());
        if(empty($this->getConn())){
            return false;
        }

        if(!$this->query($sql)){
            return false;
        }

        return true;
    }

    /**
     * Colhe os dados da dlinha corrente
     *
     * @return void
     */
    protected function data()
    {
        if(empty($this->getResource())){
            return false;
        }

        $this->setData($this->getResource()->fetch_assoc());
        return true;
    }

    /**
     * Exp�e o total de linha afetadas pela query
     * @return int
    */
    protected function totalRows()
    {
        if(empty($this->getResource())){
            return false;
        }

        return mysqli_num_rows($this->getResource());
    }

    /**
     * Devolve array associativo de todos os registros
     * 
     * @return array|null
     */
    public function asAllArray($type = self::MYSQLI_ASSOC)
    {
        if(empty($this->getResource())){
            return null;
        }
        return $this->getResource()->fetch_all($type);
    }

    /**
     * Executa uma instrução MySQL
     * 
     */
    final public function query(string $sql)
    {
        if(!isset($sql) || empty($sql)){
            return false;
        }
        
        try{
            mysqli_query ($this->getConn(), 'SET SQL_SAFE_UPDATES = 0;');
            $this->setResource(mysqli_query ($this->getConn(), $sql));
            mysqli_query ($this->getConn(), 'SET SQL_SAFE_UPDATES = 1;');
            return true;
        }
        catch(\Exception $e){
            return false;
        }
        
    }

    /**
     * Reposiciona o ponteiro do recurso
     *
     * @param int $possition - Número da nova possição do ponteiro
     *
     * @return bool
     */
    private function reposition(int $position)
    {
        if(!isset($position) || empty($position)){
            return false;
        }
        return mysqli_data_seek($this->getResource(), $position);
    }

    /**
     * Move o ponteiro para o próximo
     * 
     */
    final public function next()
    {
        if($this->getResource() === null){
            return false;
        }
            
        $this->reposition(mysqli_field_tell($this->getResource()) + 1);
        $this->data();

        return true;
    }

    /**
     * Move o ponteiro para o anterior
     * 
     */
    final public function previous()
    {
        if($this->getResource() === null){
            return false;
        }

        $this->reposition(mysqli_field_tell($this->getResource()) - 1);
        $this->data();

        return true;
    }

    /**
     * Move o ponteiro para o primeiro
     * 
     */
    final public function first()
    {
        if($this->getResource() === null){
            return false;
        }

        $this->reposition(0);
        $this->data();

        return true;
    }

    /**
     * Move o ponteiro para o último
     * 
     */
    final public function last()
    {
        if($this->getResource() === null){
            return false;
        }

        $this->reposition(mysqli_num_rows($this->getResource()));
        $this->data();

        return true;
    }

    /**
     * Get the value of conn
     */ 
    public function getConn()
    {
        return $this->conn;
    }

    /**
     * Set the value of conn
     *
     * @return  self
     */ 
    public function setConn($conn)
    {
        if(isset($conn) && !empty($conn)){
            $this->conn = $conn;
        }
        return $this;
    }

    /**
     * Get the value of resource
     */ 
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Set the value of resource
     *
     * @return  self
     */ 
    public function setResource($resource)
    {
        if(isset($resource) && !empty($resource)){
            $this->resource = $resource;
        }
        return $this;
    }

    /**
     * Get the value of data
     */ 
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set the value of data
     *
     * @return  self
     */ 
    public function setData($data)
    {
        if(isset($data) && !empty($data)){
            $this->data = $data;
        }
        return $this;
    }
}
