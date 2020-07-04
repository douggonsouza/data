<?php

namespace Nuclear\system\model;

use Nuclear\system\model\executes;
use Nuclear\system\model\resources;
use Nuclear\system\model\resourceInterface;
use Nuclear\system\model\modelsInterface;
use Nuclear\system\model\dicionary;

class models extends executes implements resourceInterface, modelsInterface
{  
    public    $table;
    public    $key;
    public    $new = true;
    protected $resource;
    protected $columns = [];

    protected $dicionarySQL = "SELECT %s as value, %s as label FROM %s;";

    public function __construct(string $key = null)
    {
        if(isset($key) && isset($this->key))
            $this->getBy($key, $this->key);
    }

    public function getBy($value, string $index)
    {
        if(!isset($value) || isset($this->table) || isset($this->key)){
            return false;
        }

        $this->setResource(new resources(
            sprintf("SELECT * FROM %s WHERE %s = %s AND active = 1;",
                $this->table,
                $index,
                $value), 
            $this->new
        ));

        return $this;
    }

    public function search(array $fields)
    {
        $where = [];

        if(!isset($fields) || empty($fields)){
            return false;
        }
        foreach($fields as $index => $value){
            $where[] = $index.' = '.trim($value);
        }

        $this->setResource(new resources(
            sprintf(
                "SELECT * FROM %s WHERE %s AND active = 1;",
                $this->table,
                implode(' AND ',$where)
                ),
            $this->new
        ));

        return $this;
    }

    /**
     * Salva o modelo
     *
     * @return void
     */
    final public function save()
    {
        if(!isset($this->resource))
            return null;

        // colher os dados (data)
        $data = $this->resource->getData();

        if(!$this->is_new())
            return $this->updateResource($data, $this->table);

        return $this->insertResource($data, $this->table);
    }

    /**
     * Deleta logicamente o modelo
     * 
     * @return void
     */
    final public function delete()
    {
        // colher os dados (data)
        $data = $this->resource->getData();
        return $this->deleteResource($data, $this->table);
    }

    /**
     * Set the value of relationships
     *
     * @return  self
     */ 
    public function relationships(string $destinyTable, string $fieldLink)
    {
        if(!isset($destinyTable) || !isset($fieldLink))
            return null;

        if($this->getTable() === null || $this->getValue($fieldLink) === null)
            return null;

        return new resources(sprintf(
            resourceInterface::RELATIONSHIPS_MANY_TO_ONE,
            $this->getTable(),
            $destinyTable,
            $fieldLink,
            $this->getValue($fieldLink)
            ));
    }

    /**
     * Exporta objeto do tipo dicionary
     * 
     * @param string $dicionarySQL
     * 
     * @return object
     */
    public function dicionary(string $dicionarySQL)
    {
        return new dicionary($this->select($dicionarySQL));
    }

    /**
     * Valida se é uma model nova e vazia
     * 
     */
    public function is_new(){
        return $this->new;
    }

    /**
     * Resgata valor existente em data
     *
     * @param  string $field
     * @return mixed
     */
    final public function getValue($field)
    {
        if(!isset($field) || !isset($this->resource))
            return null;

        return $this->getResource()->getValue($field);
    }

    /**
     * Resgata valor existente em data
     *
     * @param  string $field
     * @return mixed
     */
    final public function setValue(string $field, $value)
    {
        if(!isset($field) || !isset($value))
            return null;

        $this->getResource()->setValue($field, $value);

        return $this;
    }

    /**
     * Get the value of resource
     */ 
    public function getResource()
    {
        if(!isset($this->resource) && isset($this->table)){
            $this->resource = new resources();
            $this->resource->matchInfoFields( $this->table);
        }

        return $this->resource;
    }

    /**
     * Set the value of resource
     *
     * @return  self
     */ 
    public function setResource($resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Colhe o valor para table
     */ 
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Define o valor para table
     *
     * @param string $table
     *
     * @return  self
     */ 
    public function setTable(string $table)
    {
        if(isset($table) && !empty($table))
            $this->table = $table;

        return $this;
    }

    /**
     * Colhe o valor para key
     */ 
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Define o valor para key
     *
     * @param string $key
     *
     * @return  self
     */ 
    public function setKey(string $key)
    {
        if(isset($key) && !empty($key))
            $this->key = $key;

        return $this;
    }
}
