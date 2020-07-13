<?php

namespace data\connection;

abstract class conn
{

	static private $connection  = null;
	static public $transaction;

	private function __construct(){	}
	
	/**
	 * Conecta com o banco de dados
	 *
	 * @param string $host
	 * @param string $login
	 * @param string $password
	 * @param string $schema
	 * @return void
	 */
	static public function connection(string $host, string $login, string $password, string $schema)
	{
        if(!isset(self::$connection)){
			self::$connection = mysqli_connect(
				$host,
				$login,
				$password,
				$schema
			) or die('Error connection database.');
		}
		return self::$connection;
    }
    	
	/**
	 * Evento destruidor da classe
	 */
	function __destruct()
	{
		self::$connection = null;	
	}

	/**
	 * Get the value of conn
	 */ 
	static public function getConnection()
	{
		return self::connection();
	}

    /**
     * Inicia transação
     * 
     * @return boolean
     */
    public function beginTransaction()
    {
		// inicia sessão de transação
		mysqli_query(self::getConnection(), 'SET SQL_SAFE_UPDATES = 0;');
        self::setTransaction(mysqli_query (self::getConnection(), 'START TRANSACTION'));
		mysqli_query(self::getConnection(), 'SET SQL_SAFE_UPDATES = 1;');
		
        return true;
	}
	
    /**
     * Faz commit na transação iniciada
     * @return boolean
     */
    final public function commitTransaction()
    {
		// confirma sessão de transação
		mysqli_query(self::getConnection(), 'SET SQL_SAFE_UPDATES = 0;');
        self::setTransaction(mysqli_query (self::getConnection(), 'COMMIT'));
		mysqli_query(self::getConnection(), 'SET SQL_SAFE_UPDATES = 1;');
		
        return true;
    }

    /**
     * Faz rollback na transação iniciada
     * @return boolean
     */
    final public function rollbackTransaction()
    {
		// desfaz sessão de transação
		mysqli_query(self::getConnection(), 'SET SQL_SAFE_UPDATES = 0;');
        self::setTransaction(mysqli_query (self::getConnection(), 'ROLLBACK'));
		mysqli_query(self::getConnection(), 'SET SQL_SAFE_UPDATES = 1;');
		
        return true;
    }


	/**
	 * Get the value of transaction
	 */ 
	static public function getTransaction()
	{
		return self::$transaction;
	}

	/**
	 * Set the value of transaction
	 *
	 * @return  self
	 */ 
	static public function setTransaction($transaction)
	{
		if(isset($transaction) && !empty($transaction))
			self::$transaction = $transaction;
	}
}
