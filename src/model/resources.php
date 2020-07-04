<?php

namespace Nuclear\system\model;

use Nuclear\system\model\executes;
use Nuclear\system\model\resourceInterface;
use Nuclear\system\model\field;

class resources extends executes implements resourceInterface
{
    public    $resource;
    protected $table;
    protected $key;
    protected $rows;
    protected $row = 0;
    protected $data;
    protected $old = [];
    protected $isUpdate = false;
    protected $eof;

    public function __construct(string $sql = null, &$new = true)
    {
        if(isset($sql) && is_string($sql))
            $this->initResource($sql, $new);
    }

    final public function matchInfoFields(string $table)
    {
        if(isset($table)){
            $this->table = $table;
            $resource = $this->select('show columns from '.$table.';');
            for($count = 0; $count < $resource->num_rows; $count++){
                $column  = mysqli_fetch_assoc($resource);
                $type    = $column['Type'];
                if(strpos($column['Type'],'(') !== false)
                    $type    = substr($column['Type'],0,strpos($column['Type'],'('));
                $this->data[$column['Field']] = $this->fields($type, $column);
            }
            return true;
        }
        return false;
    }

    /**
     * Fabrica o campo conforme seu tipo
     *
     * @param string $type
     * @param mixed  $property
     * @param mixed  $value
     *
     * @return object
     */
    private function fields(string $type, $property, $value = null)
    {
        if(!isset($type) || !isset($property))
            return null;

        switch($type){
            case 'int': return new \Nuclear\system\model\fields\integer($property, $value);
            case 'varchar': return new \Nuclear\system\model\fields\varchar($property, $value);
            case 'tinyint': return new \Nuclear\system\model\fields\tinyint($property, $value);
            case 'datetime': return new \Nuclear\system\model\fields\datetime($property, $value);
            case 'text': return new \Nuclear\system\model\fields\text($property, $value);
            default: return new \Nuclear\system\model\fields\varchar($property, $value);
        }

        return null;
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
        if(!isset($position) || empty($position))
            return false;

        return mysqli_data_seek($this->getResource(), $position);
    }

    /**
     * Move o ponteiro para o próximo
     * 
     */
    final public function next()
    {
        if($this->getResource() === null)
            return null;
        $fields = mysqli_fetch_assoc($this->getResource());
        if(!isset($fields) || empty($fields))
            return null;

        foreach($fields as $index => $value){
            $this->setProperty($index, 'Value', $value);
        }

        $this->setRow($this->getRow() + 1);

        if($this->getRow() == $this->getRows())
            $this->eof = true;

        return $this;
    }

    /**
     * Move o ponteiro para o anterior
     * 
     */
    final public function previous()
    {
        if($this->getResource() === null)
            return null;

        $this->setRow(($this->getRow() - 2));
        $this->reposition((int) $this->getRow());

        $this->next();

        return $this;
    }

    /**
     * Move o ponteiro para o primeiro
     * 
     */
    final public function first()
    {
        if($this->getResource() === null)
            return null;

        $this->setRow(0);
        $this->reposition((int) $this->getRow());

        $this->next();

        return $this;
    }

    /**
     * Move o ponteiro para o último
     * 
     */
    final public function last()
    {
        if($this->getResource() === null)
            return null;

        $this->setRow($this->getRows() - 1);
        $this->reposition((int) $this->getRow());

        $this->next();

        return $this;
    }

    /**
     * Captura toda requisição à função
     * 
     */
    public function __call($function, $arguments)
    {
        return false;
    }

    /**
     * Set the value of resource
     *
     * @return  self
     */ 
    public function initResource($sql, &$new = true)
    {
        // é query modelo
        $this->setTable($this->isQueryModel($sql));

        if(!isset($sql) || !isset($this->table))
            return null;

        $this->setResource($this->select($sql));

        $this->matchInfoFields($this->getTable());

        if($this->next() !== null)
            $new = false;

        return $this;
    }

    /**
     * Se ocorreu um update em algum dos dados
     *
     * @return boolean
     */
    public function isUpdate()
    {
        return $this->isUpdate;
    }

    public function isQueryModel(string $sql)
    {
        if(stripos($sql, 'SELECT') === false)
            return null;

        $ofFrom = substr($sql, stripos($sql, 'FROM') + 5, strlen($sql));
        $table = substr($ofFrom,0,strpos($ofFrom, ' '));

        if(empty($table))
            return null;

        return $table;
    }

    /**
     * Get the value of data
     */ 
    public function getData()
    {
        return $this->data;
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
        if(isset($resource)){
            $this->resource = $resource;
            $this->setRows($this->getResource()->num_rows);
        }

        return $this;
    }

    /**
     * Get the value of row
     */ 
    public function getRow()
    {
        return $this->row;
    }

    /**
     * Set the value of row
     *
     * @return  self
     */ 
    public function setRow($row)
    {
        if($this->getRow() < 0){
            $this->row = 0;
            return $this;
        }

        $this->row = $row;

        return $this;
    }

    /**
     * Resgata valor no tipo field
     * 
     * @param mixed $resource
     * 
     * @return void
     */
    public function getProperty(string $field, string $property)
    {
        if(!isset($field) || !isset($property))
            return null;

        if(array_key_exists($field, $this->data)){
            return $this->data[$field]->get($property);
        }
        return null;
    }

    /**
     * Salva o valor da propriedade no tipo field
     *
     * @param string $field
     * @param string $property
     * @param [type] $value
     * 
     * @return void
     */
    public function setProperty(string $field, string $property, $value)
    {
        if(!isset($field) || !isset($property) || !isset($value))
            return false;

        if(array_key_exists($field, $this->data)){
            $this->data[$field]->set($property, $value);
            return $this;
        }

        return $this;
    }

    /**
     * Resgata valor existente em data
     *
     * @param  string $field
     * @return void
     */
    final public function getValue($field)
    {
        if(isset($field))
            return $this->getProperty($field, 'Value');

        return null;
    }

    /**
     * Aplica valor em data
     *
     * @param  string $field
     * @return void
     */
    final public function setValue(string $field, $value)
    {
        if(isset($field) && isset($value)){
            if(isset($this->data[$field]) && $this->data[$field]->Value != $value)
            $this->old[$field] = $this->data[$field];
            $this->isUpdate = true;

        }
        $this->setProperty($field, 'Value', $value);

        return true;
    }

    /**
     * Get the value of rows
     */ 
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * Set the value of rows
     *
     * @return  self
     */ 
    private function setRows($rows)
    {
        $this->rows = $rows;

        return $this;
    }

    /**
     * Get the value of table
     */ 
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Set the value of table
     *
     * @return  self
     */ 
    public function setTable($table)
    {
        if(isset($table))
            $this->table = $table;

        return $this;
    }

    /**
     *
     * Devolve os valores do recurso em forma de array
     *
     * @param bool $all
     *
     * @return array
     */
    public function _as_array(bool $all = false)
    {
        if(empty($this->getData()))
            return [];

        if($all){
            return mysqli_fetch_all($this->getResource(), MYSQLI_ASSOC);
        }

        $data = [];
        foreach($this->getData() as $index => $value){
            $data[$index] = $value['Value'];
        }

        return $data;
    }
}
