<?php

namespace data\resource;

use data\connection\conn;

abstract class resource
{
    protected static $conn;
    protected static $data;
    protected static $resource;
    protected static $error;
    protected static $index = -1;
    protected static $isEof = false;

    /**
     * Conecta o banco de dados
     *
     * @return object
     */
    protected static function conn()
    {
        if(!isset($_ENV["DBHOST"]) || !isset($_ENV["DBLOGIN"]) || !isset($_ENV["DBPASSWORD"]) || !isset($_ENV["DBSCHEMA"])){
            return false;
        }

        if(empty(self::getConn())){
            self::setConn(conn::connection(
                $_ENV["DBHOST"],
                $_ENV["DBLOGIN"],
                $_ENV["DBPASSWORD"],
                $_ENV["DBSCHEMA"],
            ));
            if(empty(self::getConn())){
                self::setError(self::getConn()->error);
                return false;
            }
        }

        return true;
    }

    /**
     * Exp�e o total de linha afetadas pela query
     * @return int
    */
    protected static function totalRows()
    {
        if(empty(self::getResource())){
            return false;
        }

        return mysqli_num_rows(self::getResource());
    }

    /**
     * Devolve array associativo de todos os registros
     * 
     * @return array|null
     */
    public static function asAllArray($type = self::MYSQLI_ASSOC)
    {
        if(empty(self::getResource())){
            return null;
        }
        return self::getResource()->fetch_all($type);
    }

    /**
     * Executa uma instrução MySQL
     * 
     */
    public static function query(string $sql)
    {
        if(!isset($sql) || empty($sql)){
            return false;
        }

        self::conn();
        if(!self::getConn()){
            return false;
        }
        
        try{
            self::getConn()->query('SET SQL_SAFE_UPDATES = 0;');
            self::setResource(self::getConn()->query((string) $sql));
            if(empty(self::getResource())){
                self::setError(self::getConn()->error);
                return false;
            }
            self::getConn()->query((string) 'SET SQL_SAFE_UPDATES = 1;');
            
            if(!self::next()){
                self::setError(self::getConn()->error);
                return false;
            }
            return true;
        }
        catch(\Exception $e){
            return false;
        }
    }

    /**
     * Move o ponteiro para o próximo
     * 
     */
    public static function next()
    {
        if(empty(self::getResource())){
            return false;
        }

        self::setIndex(self::getIndex() + 1);
        self::setData(self::getResource()->fetch_object());
        return true;
    }

    /**
     * Move o ponteiro para o anterior
     * 
     */
    public static function previous()
    {
        if(empty(self::getResource())){
            return false;
        }


        self::setIndex(self::getIndex() - 1);
        self::setData(self::getResource()->fetch_object());

        return true;
    }

    /**
     * Move o ponteiro para o primeiro
     * 
     */
    public static function first()
    {
        if(empty(self::getResource())){
            return false;
        }

        self::setIndex(0);
        self::setData(self::getResource()->fetch_object());

        return true;
    }

    /**
     * Move o ponteiro para o último
     * 
     */
    public static function last()
    {
        if(empty(self::getResource())){
            return false;
        }

        self::setIndex(self::totalRows() - 1);
        self::setData(self::getResource()->fetch_object());

        return true;
    }

    /**
     * Get the value of conn
     */ 
    public static function getConn()
    {
        return self::$conn;
    }

    /**
     * Set the value of conn
     *
     * @return  self
     */ 
    public static function setConn($conn)
    {
        if(isset($conn) && !empty($conn)){
            self::$conn = $conn;
        }
    }

    /**
     * Get the value of data
     */ 
    public static function getData()
    {
        return self::$data;
    }

    /**
     * Set the value of data
     *
     * @return  self
     */ 
    public static function setData($data)
    {
        if(!isset($data) && self::getIndex() >= self::totalRows()){
            self::setIsEof(true);
            return;
        }

        self::$data = $data;
        self::setIsEof(false);
    }

    /**
     * Get the value of error
     */ 
    public static function getError()
    {
        return self::$error;
    }

    /**
     * Set the value of error
     *
     * @return  self
     */ 
    public static function setError($error)
    {
        if(isset($error) && !empty($error)){
            self::$error = $error;
        }
    }

    /**
     * Get the value of resource
     */ 
    public static function getResource()
    {
        return self::$resource;
    }

    /**
     * Set the value of resource
     *
     * @return  self
     */ 
    public static function setResource($resource)
    {
        if(isset($resource) && !empty($resource) && $resource != false){
            self::$resource = $resource;
        }
    }

    /**
     * Get the value of index
     */ 
    public static function getIndex()
    {
        return self::$index;
    }

    /**
     * Set the value of index
     *
     * @return  self
     */ 
    public static function setIndex($index)
    {
        if(isset($index) && $index <= self::totalRows()){
            self::$index = $index;
            self::getResource()->data_seek(self::getIndex());
        }
    }

    /**
     * Get the value of isEof
     */ 
    public static function getIsEof()
    {
        return self::$isEof;
    }

    /**
     * Set the value of isEof
     *
     * @return  self
     */ 
    public function setIsEof($isEof)
    {
        if(isset($isEof) && !empty($isEof)){
            self::$isEof = $isEof;
        }
    }
}
