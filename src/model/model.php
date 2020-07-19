<?php

namespace data\model;

use data\resource\resource;
use data\model\modelInterface;
use data\model\utils;

class model extends utils implements modelInterface
{   
    public $table;
    public $key;
    public $isNew = true;
    public $dicionary = null;
    protected $rows;
    protected $error;

    public function __construct(string $table, string $key)
    {
        $this->setTable($table);
        $this->setKey($key);
        $this->rows();
    }

    /**
     * Informações das colunas visíveis
     *
     * @return void
     */
    public function visibleColumns()
    {
        return array(
            'table'  => 'users',
            'key'    => 'user_id',
            'columns' => array(
                'user_id' => array(
                    'label' => 'Id',
                    'pk'    => true,
                    'type'  => 'integer',
                ),
            ),
        );
    }

    /**
     * Exporta objeto do tipo dicionary
     * 
     * @param string $dicionarySQL
     * 
     * @return object
     */
    public function dicionary()
    {
        if(empty($this->getDicionary())){
            return null;
        }

        $resource = new resource();
        $dicionary = $resource::dicionary($this->getDicionary());
        if(!$dicionary){
            $this->setError($resource::getError());
            return null;
        }
        return $dicionary;
    }

    /**
     * Cardinalidade Um para Muitos
     *
     * @param object $model
     * @param string $fieldDestine
     * @param string $fieldOrigen
     * @return void
     */
    public function oneForMany(object $model, string $fieldDestine, string $fieldOrigen = null)
    {
        if(!isset($model) && empty($model)){
            return null;
        }

        if(!isset($fieldDestine) && empty($fieldDestine)){
            return null;
        }

        if(!isset($fieldOrigem)){
            $fieldOrigem = $fieldDestine;
        }

        $resource = new resource();

        $sql = sprintf("SELECT
                %3\$s.*
            FROM %1\$s
            JOIN %3\$s ON %3\$s.%4\$s = %1\$s.%2\$s
            WHERE
                %1\$s.%2\$s = %5\$s
            ORDER BY
                %1\$s.%2\$s;",
            $this->getTable(),
            $fieldOrigem,
            $model->getTable(),
            $fieldDestine,
            $this->getField($fieldOrigem)
        );

        if(!$resource::query($sql)){
            return null;
        }

        return $resource;
    }
    
    /**
     * Cardinalidade Muitos para Muitos
     *
     * @param object $model
     * @param string $fieldDestine
     * @param string $fieldOrigen
     * @return void
     */
    public function manyForMany(object $model, string $fieldDestine, string $fieldOrigen = null)
    {
        if(!isset($model) && empty($model)){
            return null;
        }

        if(!isset($fieldDestine) && empty($fieldDestine)){
            return null;
        }

        if(!isset($fieldOrigem)){
            $fieldOrigem = $fieldDestine;
        }

        $resource = new resource();

        $sql = sprintf("SELECT
                %3\$s.*
            FROM %1\$s
            JOIN %3\$s ON %3\$s.%4\$s = %1\$s.%2\$s
            ORDER BY
                %1\$s.%2\$s;",
            $this->getTable(),
            $fieldOrigem,
            $model->getTable(),
            $fieldDestine,
        );

        if(!$resource::query($sql)){
            return null;
        }

        return $resource;
    }

    /**
     * Salva os dados do modelo
     *
     * @return bool
     */
    public function save()
    {
        if(empty($this->getRows())){
            return false;
        }

        $resource = new resource();

        $sql = $this->queryForSave($this->visibleColumns(), $this->getData());
        if(empty($sql)){
            $this->setError('Erro na geração da query de salvamento.');
            return false;
        }

        if(!$resource::query($sql)){
            $this->setError($resource::getError());
            return false;
        }

        return true;
    }

    /**
     * Salva os dados do modelo
     *
     * @return bool
     */
    public function delete()
    {
        if(empty($this->getRows())){
            return false;
        }

        $resource = new resource();

        $sql = $this->queryForDelete($this->visibleColumns(), $this->getData());
        if(empty($sql)){
            $this->setError('Erro na geração da query de deleção.');
            return false;
        }

        if(!$resource::query($sql)){
            $this->setError($resource::getError());
            return false;
        }

        return true;
    }

    /**
     * Carrega a propriedade rows com um resource
     *
     * @return void
     */
    public function rows()
    {
        $this->rows = new resource();
        $this->rows::query("SELECT * FROM ".$this->getTable().";");
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
        if(isset($table) && !empty($table)){
            $this->table = $table;
        }
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
        if(isset($key) && !empty($key)){
            $this->key = $key;
        }
    }

    /**
     * Get the value of isNew
     */ 
    public function getIsNew()
    {
        return $this->isNew;
    }

    /**
     * Set the value of isNew
     *
     * @return  self
     */ 
    protected function setIsNew($isNew)
    {
        if(isset($isNew) && !empty($isNew)){
            $this->isNew = $isNew;
        }
    }

    /**
     * Get the value of rows
     */ 
    public function getRows()
    {
        return $this->rows;
    }

        /**
     * Expõe o valor do campo
     *
     * @param string $field
     * @return void
     */ 
    public function getData()
    {
        if(empty($this->getRows())){
            return null;
        }

        return $this->getRows()::getData();
    }

    /**
     * Expõe o valor do campo
     *
     * @param string $field
     * @return void
     */ 
    public function getField(string $field)
    {
        if(empty($this->getRows())){
            return null;
        }

        if(!isset($field) || empty($field)){
            return null;
        }

        return $this->getRows()::getField($field);
    }

    /**
     * Atualiza o valor para o campo
     *
     * @param string $field
     * @param mixed $value
     * @return bool
     */ 
    public function setField(string $field, $value)
    {
        if(empty($this->getRows())){
            return null;
        }

        if(!isset($field) || empty($field)){
            return null;
        }

        return $this->getRows()::setField($field, $value);
    }

    /**
     * Get the value of dicionary
     */ 
    public function getDicionary()
    {
        return $this->dicionary;
    }

    /**
     * Set the value of dicionary
     *
     * @return  self
     */ 
    protected function setDicionary($dicionary)
    {
        if(isset($dicionary) && !empty($dicionary)){
            $this->dicionary = $dicionary;
        }
        
        return $this;
    }

    /**
     * Get the value of error
     */ 
    public function getError()
    {
        return $this->error;
    }

    /**
     * Set the value of error
     *
     * @return  self
     */ 
    public function setError($error)
    {
        if(isset($error) && !empty($error)){
            $this->error = $error;
        }
        return $this;
    }
}
