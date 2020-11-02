<?php

namespace data\validade;

use data\validade\validadeInterface;

class limit implements validadeInterface
{
    protected $error;
    protected $value;
    protected $limit;

    public function __construct($value, $limit)
    {
        $this->setValue($value);
        $this->setLimit($limit);
    }
    
    public function validade()
    {
        if(empty($this->getValue())){
            $this->setError('Não existe valor para ser validado seu tamanho.');
        }

        if(empty($this->getLimit())){
            $this->setError('Não existe um tamanho limite para o campo.');
        }

        if(strlen($this->getValue()) > $this->getLimit()){
            $this->setError('O valor excede o tamanho do campo.');
        }

        return $this;

    }

    /**
     * Get the value of value
     */ 
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the value of value
     *
     * @return  self
     */ 
    public function setValue($value)
    {
        if(isset($value)){
            $this->value = $value;
        }

        return $this;
    }

    /**
     * Get the value of limit
     */ 
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Set the value of limit
     *
     * @return  self
     */ 
    public function setLimit($limit)
    {
        if(isset($limit)){
            $this->limit = $limit;
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
        if(isset($error)){
            $this->error = $error;
        }

        return $this;
    }
}
