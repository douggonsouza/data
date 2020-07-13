<?php

namespace Nuclear\system\model\fields;

interface fieldsInterface
{

    /**
     * Evento construtor da classe
     * 
     */
    public function __construct($property = null, $value = null);

    /**
     * Resgata valor existente em data
     *
     * @param  string $field
     * @return void
     */
    public function getValue();

    /**
     * Aplica valor em data
     *
     * @param  string $field
     * @return void
     */
    public function setValue($value);

    /**
     * Resgata valor de property
     *
     * @param  string $field
     * @return void
     */
    public function get(string $property);

    /**
     * Aplica valor em data
     *
     * @param  string $field
     * @return void
     */
    public function set(string $property, $value);
}
