<?php

namespace Nuclear\system\model\fields;

use Nuclear\system\model\fields\fieldsInterface;

class int extends \stdClass implements fieldsInterface
{
    public $Field;
    public $Type;
    public $Null;
    public $Key;
    public $Default;
    public $Extra;
    public $Value;

    /**
     * Evento construtor da classe
     * 
     */
    public function __construct($property = null, $value = null)
    {
        if(is_array($property)){
            foreach($property as $index => $value){
                $this->set($index, $value);
            }
            return;
        }

        if(isset($property) && isset($value))
            $this->set($property, $value);
    }

    /**
     * Resgata valor existente em data
     *
     * @param  string $field
     * @return void
     */
    public function getValue()
    {
        if(isset($this->value) && isset($this->Type)){
            return $this->Value;
        }

        return null;
    }

    /**
     * Aplica conteúdo a propriedade value
     *
     * @param  string $field
     * @return void
     */
    public function setValue($value)
    {
        if(isset($value))
            $this->Value = (int) $value;

        return $this;
    }

    /**
     * Resgata valor de property
     *
     * @param  string $field
     * @return void
     */
    public function get(string $property)
    {
        if(isset($property) && isset($this->{$property})){
            return $this->{$property};
        }

        return null;
    }

    /**
     * Aplica valor em data
     *
     * @param  string $field
     * @return void
     */
    public function set(string $property, $value)
    {
        if(isset($property) && isset($value))
            $this->{$property} = $value;

        return $this;
    }

}

