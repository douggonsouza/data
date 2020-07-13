<?php

namespace Nuclear\system\model;

interface modelsInterface
{
    /**
     * Colhe o valor para table
     */ 
    public function getTable();

    /**
     * Define o valor para table
     *
     * @param string $table
     *
     * @return  self
     */ 
    public function setTable(string $table);

    /**
     * Colhe o valor para key
     */ 
    public function getKey();

    /**
     * Define o valor para key
     *
     * @param string $key
     *
     * @return  self
     */ 
    public function setKey(string $key);
}

