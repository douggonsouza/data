<?php

namespace data;

abstract class typing extends stdClass
{

    abstract public function __construct(array $fields);

    /**
     * Preenche propriedade do objeto
     *
     * @param object $obj
     * @param array $fields
     * @return void
     */
    protected static function fields(object &$obj, array $fields)
    {
        foreach($fields as $index => $value){
            if(property_exists($obj, $index)){
                $obj->$index = $value;
            }
        }
    }
}

?>