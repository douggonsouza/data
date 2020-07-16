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
        if(isset($data[$infoColumns['config']['key']])){
            $where = $infoColumns['config']['key'].' = '.$this->prepareSqlValue('integer', $data[$infoColumns['config']['key']]);
        }

        // array do conteúdo
        $content = array();
        foreach($infoColumns as $index => $item){
            if($item['pk'] || $item['name'] === 'config'){
                continue;
            }
            if(isset($where)){
                $content[$item['name']] = $item['name'].' = '.$this->prepareSqlValue($item['type'], $data[$item['name']]).'';
                continue;
            }
            $content[$item['name']] = $this->prepareSqlValue($item['type'], $data[$item['name']]);
        }

        // update
        if(isset($where)){
            $sql = sprintf(
                "UPDATE %1\$s SET %2\$s WHERE %3\$s;",
                $infoColumns['config']['table'],
                implode(', ',$content),
                $where
            );
            return $sql;
        }
        // save
        $sql = sprintf(
            "INSERT INTO %1\$s (%2\$s) VALUES (%3\$s);",
            $infoColumns['config']['table'],
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
            $this->setError('Não é permitido parâmetro data nulo.');
            return false;
        }

        // existe id
        $where = null;
        if(isset($data[$infoColumns['config']['key']])){
            $where = $infoColumns['config']['key'].' = '.$this->prepareSqlValue('integer', $data[$infoColumns['config']['key']]);
        }
        if(!isset($where)){
            $this->setError('Não é possível deletar um novo resource.');
            return false;
        }

        // update
        $sql = sprintf(
            "DELETE FROM %1\$s WHERE %2\$s;",
            $infoColumns['config']['table'],
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
    public function prepareSqlValue(string $type, $value = null)
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
}