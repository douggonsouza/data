<?php

namespace data\model;

use data\resource\resource;
use data\model\modelInterface;
use data\model\utils;

class model extends utils implements modelInterface
{   
    public    $table;
    public    $key;
    public    $dicionary = null;
    protected $records;
    protected $error;

    public function __construct(string $table, string $key)
    {
        $this->setTable($table);
        $this->setKey($key);
    }

    /**
     * Informações das colunas visíveis
     *
     * array(
     *      'table'  => 'users',
     *      'key'    => 'user_id',
     *      'columns' => array(
     *              'user_id' => array(
     *              'label' => 'Id',
     *              'pk'    => true,
     *              'type'  => 'integer',
     *              'limit' => 11
     *          ),
     *      ),
     * );
     * @return void
     */
    public function visibleColumns()
    {
        return array();
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
        $dicionary = $resource->execute($this->getDicionary());
        if(!isset($dicionary)){
            $this->setError($resource->getError());
            return null;
        }
        return $dicionary;
    }

    /**
     * Move o ponteiro para o prÃ³ximo
     * 
     */
    public function next()
    {
        if(empty($this->getRecords())){
            return false;
        }

        return $this->getRecords()->next();
    }

     /**
     * Move o ponteiro para o anterior
     * 
     */
    public function previous()
    {
        if(empty($this->getRecords())){
            return false;
        }

        return $this->getRecords()->previus();
    }

    /**
     * Move o ponteiro para o primeiro
     * 
     */
    public function first()
    {
        if(empty($this->getRecords())){
            return false;
        }

        return $this->getRecords()->first();
    }

    /**
     * Move o ponteiro para o Ãºltimo
     * 
     */
    public function last()
    {
        if(empty($this->getRecords())){
            return false;
        }

        return $this->getRecords()->last();
    }

    /**
     * Get the value of data
     */ 
    public function getData()
    {
        if(empty($this->getRecords())){
            return false;
        }

        return $this->getRecords()->getData();
    }

    /**
     * Get the value of data
     */ 
    public function getField(string $field)
    {
        if(empty($this->getRecords())){
            return false;
        }

        return $this->getRecords()->getField($field);
    }

    /**
     * Preenche um campo com valor
     *
     * @param string $field
     * @param mixed $value
     * @return bool
     */
    public function setField(string $field, $value)
    {
        if(empty($this->getRecords())){
            $this->setRecords(new resource());
        }

        return $this->getRecords()->setField($field, $value);
    }

    /**
     * Get the value of isEof
     */ 
    public function isEof()
    {
        if(empty($this->getRecords())){
            return true;
        }

        return $this->getRecords()->getIsEof();
    }

    /**
     * Cardinalidade Muitos para um
     *
     * @param object $model
     * @param string $fieldDestine
     * @param string $fieldOrigen
     * @return void
     */
    public function manyForOne(object $model, string $fieldDestine, string $fieldOrigen = null)
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

        $sql = sprintf("SELECT DISTINCT
                %3\$s.*
            FROM %3\$s
            JOIN %1\$s ON %1\$s.%2\$s = %3\$s.%4\$s AND %1\$s.active = 1
            WHERE
                %1\$s.%2\$s = %5\$s
                AND %3\$s.active = 1
            -- GROUP BY
            --     %3\$s.%4\$s
            ORDER BY
                %3\$s.%4\$s;",
            $this->getTable(),
            $fieldOrigem,
            $model->getTable(),
            $fieldDestine,
            $this->prepareValueByVisibleColumns(
                $this->visibleColumns()['columns'][$fieldOrigem]['type'],
                $this->getField($fieldOrigem)
            )
        );

        if(!$resource->query($sql)){
            $this->setError($resource->getError());
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

        if(!$resource->query($sql)){
            return null;
        }

        return $resource;
    }

    /**
     * Popula o objeto data pelo array
     *
     * @param array $data
     * @return bool
     */
    public function populate(array $data)
    {
        if(empty($this->visibleColumns())){
            $this->setError('Não existe configuração para colunas visíveis.');
            return false;
        }

        if(empty($this->getRecords())){
            $this->setRecords(new resource());
        }

        // array do conteúdo
        $content = $this->arrayByVisibleColumns($this->visibleColumns(), $data);
        if(!$this->getRecords()->populate($content)){
            $this->setError('Erro na população do objeto Data.');
            return false;
        }

        return true;
    }

    /**
     * Salva os dados do modelo
     *
     * @return bool
     */
    public function save()
    {
        if(empty($this->getRecords())){
            return false;
        }

        $resource = new resource();

        $sql = $this->queryForSave($this->visibleColumns(), $this->getData());
        if(empty($sql)){
            $this->setError('Erro na geração da query de salvamento.');
            return false;
        }

        if(!$resource->query($sql)){
            $this->setError($resource->getError());
            return false;
        }

        return true;
    }

    /**
     * Expõe o total de linha afetadas pela query
     * @return int
    */
    public function total()
    {
        if(empty($this->getRecords())){
            return null;
        }

        return $this->getRecords()->total();
    }

    /**
     * Devolve array associativo de todos os registros
     * 
     * @return array|null
     */
    public function asArray()
    {
        if(empty($this->getRecords())){
            return null;
        }
        return $this->getRecords()->asArray();
    }

    /**
     * Executa uma instruÃ§Ã£o MySQL
     * 
     */
    public function query(string $sql)
    {
        if(empty($this->getRecords())){
            $this->setRecords(new resource());
        }

        return $this->getRecords()->query($sql);
    }

    /**
     * Salva os dados do modelo
     *
     * @return bool
     */
    public function delete()
    {
        if(empty($this->getRecords())){
            return false;
        }

        $resource = new resource();

        $sql = $this->queryForDelete($this->visibleColumns(), $this->getData());
        if(empty($sql)){
            $this->setError('Erro na geração da query de deleção.');
            return false;
        }

        if(!$resource->query($sql)){
            $this->setError($resource->getError());
            return false;
        }

        return true;
    }

    /**
     * Executa uma instrução MySQL
     * 
     */
    public function execute(string $sql)
    {
        if(empty($this->getRecords())){
            $this->setRecords(new resource());
        }

        return $this->getRecords()->execute($sql);
    }

    /**
     * Carrega a propriedade records com um resource
     *
     * @return void
     */
    public function records(string $sql = null)
    {
        $this->records = new resource();
        if(isset($sql)){
            $this->records->query($sql);
            return true;
        }
        $this->records->query("SELECT * FROM ".$this->getTable().";");
        return true;
    }

    /**
     * Busca entre os registros da tabela
     *
     * @param array $search
     * @return void
     */
    public function seek(array $search = null)
    {
        $this->setRecords(new resource());
        if(!$this->getRecords()->seek($this->sqlSeek($search))){
            $this->setError($this->getRecords()->getError());
            return null;
        }

        return $this;
    }

    /**
     * Busca entre os registros
     *
     * @param string $table
     * @return bool
     */
    public function search(array $search)
    {
        if(empty($this->getTable())){
            return null;
        }
        if(!isset($search) || empty($search)){
            return null;
        }

        $content = $this->filterByVisibleColumns($this->visibleColumns(), $search);
        array_walk ($content, function(&$item, $key){
            $item = $key.' = '.$item;
        });

        $this->setRecords(new resource());
        if(!$this->getRecords()->search(
            $this->getTable(),
            $content
        )){
            $this->setError($this->getRecords()->getError());
            return null;
        }

        return $this;
    }

    public function isNew()
    {
        if(empty($this->getRecords())){
            return null;
        }
        return $this->getRecords()->getNew();
    }

    /**
     * Devolve sql para a realização da busca
     *
     * @param array $where
     * @return string
     */
    public function sqlSeek(array $where = null)
    {
        if(empty($this->visibleColumns()['table'])){
            return null;
        }

        if(!isset($where)){
            $where = array( $this->visibleColumns()['table'].'.active = 1');
        }

        return sprintf(
            'SELECT * FROM %1$s WHERE %2$s;',
            $this->visibleColumns()['table'],
            implode(' AND ', $where)
        );
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
     * Get the value of records
     */ 
    public function getRecords()
    {
        return $this->records;
    }

    protected function setRecords($records)
    {
        if(isset($records) && !empty($records)){
            $this->records = $records;
        }
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
