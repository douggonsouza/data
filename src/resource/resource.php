<?php

namespace data\resource;

use data\connection\conn;
use data\resource\resourceInterface;

class resource implements resourceInterface
{
    protected static $conn;
    protected static $data;
    protected static $resource;
    protected static $error;
    protected static $index = -1;
    protected static $isEof = false;
    protected static $new   = true;

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
     * Expõe o total de linha afetadas pela query
     * @return int
    */
    protected static function total()
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
    public static function asArray()
    {
        if(empty(self::getResource())){
            return null;
        }
        return mysqli_fetch_all(self::getResource(), MYSQLI_ASSOC);
    }

    /**
     * Executa uma instruÃ§Ã£o MySQL
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
     * Busca entre os registros
     *
     * @param string $table
     * @param array  $search
     * @return bool
     */
    public static function search(string $table, array $search)
    {
        if(!isset($table) || empty($table)){
            self::setError('Não é permitido table nulo.');
            return false;
        }

        if(!isset($search) || empty($search)){
            self::setError('Não é permitido search nulo.');
            return false;
        }

        if(!self::query(sprintf(
            "SELECT * FROM %1\$s WHERE %2\$s;",
            $table,
            implode(' AND ', $search)
        ))){
            self::setError(self::getConn()->error);
            return false;
        }

        return true;
    }

    /**
     * Busca entre os registros da tabela ou retorna todos
     *
     * @param string $table
     * @param array  $search
     * @return bool
     */
    public static function seek(string $table, array $search = null)
    {
        if(!isset($table) || empty($table)){
            self::setError('Não é permitido table nulo.');
            return false;
        }

        $where = null;
        if(isset($search) && !empty($search)){
            $where = ' WHERE '.implode(' AND ', $search);
        }

        if(!self::query(sprintf(
            "SELECT * FROM %1\$s%2\$s;",
            $table,
            $where
        ))){
            self::setError(self::getConn()->error);
            return false;
        }

        return true;
    }

    /**
     * Executa uma instrução MySQL
     * 
     */
    public static function dicionary(string $sql)
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
            self::getConn()->query('SET SQL_SAFE_UPDATES = 1;');
            
            return self::asArray();
        }
        catch(\Exception $e){
            return false;
        }
    }

    /**
     * Atualiza a propriedade data
     *
     * @return bool
     */
    public static function data()
    {
        if(empty(self::getResource())){
            return false;
        }
        self::setData(self::getResource()->fetch_assoc());
        return true;
    }

    /**
     * Move o ponteiro para o prÃ³ximo
     * 
     */
    public static function next()
    {
        if(empty(self::getResource())){
            return false;
        }

        self::setIndex(self::getIndex() + 1);
        self::setData(self::getResource()->fetch_assoc());
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
        self::data();

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
        self::data();

        return true;
    }

    /**
     * Move o ponteiro para o Ãºltimo
     * 
     */
    public static function last()
    {
        if(empty(self::getResource())){
            return false;
        }

        self::setIndex(self::total() - 1);
        self::data();

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
        if(!isset(self::$data)){
            self::$data = array();
        }
        return self::$data;
    }

    /**
     * Set the value of data
     *
     * @return  self
     */ 
    protected static function setData($data)
    {
        if(!isset($data) && self::getIndex() >= self::total()){
            self::setIsEof(true);
            return;
        }

        self::$data = $data;
        self::setIsEof(false);
    }

    /**
     * Get the value of data
     */ 
    public static function getField(string $field)
    {
        if(empty(self::getData()) || !isset($field) || empty($field)){
            return '';
        }
        return self::$data[$field];
    }

    /**
     * Preenche um campo com valor
     *
     * @param string $field
     * @param mixed $value
     * @return bool
     */
    public static function setField(string $field, $value)
    {
        if(!isset($field) || empty($field) || !isset($value) || empty($value)){
            self::setError('Não é permitido nulo para os parâmentros Field ou Value.');
            return false;
        }
        if(empty(self::getData())){
            self::setError('Não encontrado o objeto Data');
            return false;
        }
        self::$data[$field] = $value;
        return true;
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
        self::$resource = null;
        if(isset($resource) && !empty($resource) && $resource != false){
            self::$resource = $resource;
            self::setNew(true);
            if($resource->num_rows > 0){
                self::setNew(false);
            }
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
        if(isset($index) && $index <= self::total()){
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

    /**
     * Get the value of new
     */ 
    public static function getNew()
    {
        return self::$new;
    }

    /**
     * Set the value of new
     *
     * @return  self
     */ 
    public static function setNew($new)
    {
        self::$new = $new;
    }
}
