<?php

namespace data\model;

interface modelInterface
{
    /**
     * Evento construtor da classe
     */
    public function __construct();
    
    /**
     * Get the value of table
     */ 
    public function getTable();

    /**
     * Get the value of key
     */ 
    public function getKey();
}