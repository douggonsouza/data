<?php

namespace data\model;

class utils
{
    /**
     * Cria query de Save
     *
     * @param array $infoColumns
     * @param array $data
     * @return string
     */
    public function queryForSave(array $infoColumns, array $data)
    {
        if(!isset($infoColumns) || empty($infoColumns)){
            $this->setError('Não é permitido parâmetro infoColumns nulo.');
            return false;
        }

        if(!isset($data) || empty($data)){
            $this->setError('Não é permitido parâmetro data nulo.');
            return false;
        }

        // existe id
        $where = null;
        if(isset($data[$infoColumns['key']])){
            $where = $infoColumns['key'].' = '.$this->prepareValueByVisibleColumns(
                $infoColumns['columns'][$infoColumns['key']]['type'],
                $data[$infoColumns['key']]
            );
        }
        
        // array do conteúdo
        $content = array();
        foreach($infoColumns['columns'] as $index => $item){
            if($item['pk']){
                continue;
            }
            if(isset($where)){
                $content[$index] = $index.' = '.$this->prepareValueByVisibleColumns($item['type'], $data[$index]).'';
                continue;
            }
            $content[$index] = $this->prepareValueByVisibleColumns($item['type'], $data[$index]);
        }

        // update
        if(isset($where)){
            $sql = sprintf(
                "UPDATE %1\$s SET %2\$s WHERE %3\$s;",
                $infoColumns['table'],
                implode(', ',$content),
                $where
            );
            return $sql;
        }
        // save
        $sql = sprintf(
            "INSERT INTO %1\$s (%2\$s) VALUES (%3\$s);",
            $infoColumns['table'],
            implode(', ', array_keys($content)),
            implode(', ',$content),
        );
        return $sql;
    }

    /**
     * Cria query de Save
     *
     * @param array $infoColumns
     * @param array $data
     * @return string
     */
    public function queryForDelete(array $infoColumns, array $data)
    {
        if(!isset($infoColumns) || empty($infoColumns)){
            $this->setError('Não é permitido parâmetro infoColumns nulo.');
            return false;
        }

        if(!isset($data) || empty($data)){
            $this->setError('Nâo é permitido parâmetro data nulo.');
            return false;
        }

        // existe id
        $where = null;
        if(isset($data[$infoColumns['key']])){
            $where = $infoColumns['key'].' = '.$this->preprepareValueByVisibleColumns(
                $infoColumns['columns'][$infoColumns['key']]['type'],
                $data[$infoColumns['key']]
            );
        }
        if(!isset($where)){
            $this->setError('Não é possível deletar um novo resource.');
            return false;
        }

        // update
        $sql = sprintf(
            "DELETE FROM %1\$s WHERE %2\$s;",
            $infoColumns['table'],
            $where
        );
        return $sql;
    }

    /**
     * Exporta o valor conforme o seu tipo SQL
     *
     * @param string $type
     * @param mixed $value
     * @return void
     */
    public function prepareValueByVisibleColumns(string $type, $value = null)
    {
        if(!isset($type) || empty($type)){
            return $value;
        }

        if(!isset($value)){
            return 'NULL';
        }

        switch(strtolower($type)){
            case 'integer':
            case 'int':
            case 'float':
            case 'double';
            case 'decimal':
                return $value;
            break;
            case 'varchar':
            case 'char':
            case 'date':
            case 'datetime':
                return "'".$value."'";
            break;
            default:
                return $value;
        }
    }

    public function arrayByVisibleColumns(array $infoColumns, array $data)
    {
        $content = array();

        if(!isset($infoColumns) || empty($infoColumns) || !isset($data) || empty($data)){
            return $content;
        }

        // array do conteúdo
        foreach($infoColumns['columns'] as $index => $item){
            if(isset($data[$index])){
                $content[$index] = trim(
                    str_pad(
                        $data[$index],
                        isset($item['limit'])? $item['limit']: 255
                    )
                );
            }
        }

        return $content;
    }

    public function filterByVisibleColumns(array $infoColumns, array $data)
    {
        $content = array();

        if(!isset($infoColumns) || empty($infoColumns) || !isset($data) || empty($data)){
            return $content;
        }

        // array do conteúdo
        foreach($infoColumns['columns'] as $index => $item){
            if(isset($data[$index])){
                $content[$index] = trim(
                    str_pad(
                        isset($item['type'])? $this->prepareValueByVisibleColumns($item['type'], $data[$index]): $data[$index],
                        isset($item['limit'])? $item['limit']: 255
                    )
                );
            }
        }

        return $content;
    }
}