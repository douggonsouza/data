<?php

namespace data\orm;

/**
 * deprecated class
 *
 */

abstract class orm
{
    private $origem  = null;
    private $entity  = null;
    static  $conn    = null;
    static  $model   = null;
    
    public function __construct($model = null)
    {
        if(isset($model))
            self::$model = $model;
    }

    /**
     * Define a variável model
     *
     * @param string $local
     * @return void
     */
    static public function setModel( $local){
        if(isset($local))
            self::$model = $local;
    }

    /**
     * Inicia transação
     * @return boolean
     * @throws EngineException
     */
    final public function beginTransaction()
    {
        // desabilita autocommit
        self::$conn->setAttribute(\PDO::ATTR_AUTOCOMMIT, 0 );
        // inicia transação
        self::$conn->beginTransaction();
        return true;
    }

    /**
     * Faz commit na transação iniciada
     * @return boolean
     * @throws EngineException
     */
    final public function commitTransaction()
    {
        // confirma transação
        self::$conn->commit();
        // =habilita autocommit
        self::$conn->setAttribute(\PDO::ATTR_AUTOCOMMIT, 1 );
        return true;
    }
    
    /**
     * Faz rollback na transação iniciada
     * @return boolean
     * @throws EngineException
     */
    final public function rollbackTransaction()
    {
        // desfaz transação
        self::$conn->rollBack();
        // habilita autocommit
        self::$conn->setAttribute(\PDO::ATTR_AUTOCOMMIT, 1 );
        return true;
    }
    
    /**
     * Expoe classe model
     * @return type
     */
    static public function model($name)
    {
        if(isset($name) && strlen($name) > 0){
            $model = str_replace(
                array('/','//','\\','\\\\'),
                '/',
                self::$model.DS.$name.'.php');
            if(file_exists($model)){
                $local = str_replace(
                    array('/','//','\\','\\\\'),
                    '\\',
                    self::$model.DS.$name);
                return new $local();
            }
            throw new \Exception('Not found model '.$local.'.');
        }
        return null;
    }
	
}
