<?php

namespace data\resource;

class dicionary extends \stdClass
{
    public $list = [];

    /**
     * Evanto construtor
     * 
     * @param object $resource
     * 
     * @return void
     */
    public function __construct($resource = null)
    {
        if(isset($resource)){
            $fields = mysqli_fetch_assoc($resource);
            while(isset($fields)){
                $this->setList($fields);
                $fields = mysqli_fetch_assoc($resource);
            }
        }
    }

    /**
     * Devolve a lista
     * 
     * @return array
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * Adiciona item a lista
     * 
     * @param array $list
     * 
     * @return $this;
     */
    public function setList(array $list)
    {
        if (isset($list) && !empty($list)) {
            if (isset($list['value']) && isset($list['label']))
                $this->list[] = $list;
        }

        return $this;
    }
}
