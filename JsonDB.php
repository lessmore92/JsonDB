<?php
/**
 * User: Lessmore92
 * Date: 11/19/2020
 * Time: 1:07 AM
 */

class JsonDB
{
    protected $dbDir;
    protected $tables = [];

    public function __construct($dbDir = '')
    {
        $this->dbDir = $dbDir ? $dbDir : getcwd();
        @mkdir($this->dbDir, 0777, true);
    }

    /**
     * @param string $tableName
     * @param array $data
     * @throws Exception
     */
    public function insert(string $tableName, array $data)
    {
        $this->load($tableName);
        $this->validateTableSchema($tableName, array_keys($data));
        $newData = $this->prepareDataToInsert($tableName, $data);
        $this->addDataToTable($tableName, $newData);
        $this->save($tableName);
    }

    /**
     * @param string $tableName
     * @param array $where
     * @return mixed
     * @throws Exception
     */
    public function select(string $tableName, array $where = [])
    {
        $this->load($tableName);
        if (empty($where))
        {
            return $this->tables[$tableName]['data'];
        }

        return $this->filterData($this->tables[$tableName]['data'], $where);

    }

    /**
     * @param string $tableName
     * @param array $newValues
     * @param array $where
     * @throws Exception
     */
    public function update(string $tableName, array $newValues, array $where = [])
    {
        $this->load($tableName);
        $this->validateTableSchema($tableName, array_merge(array_keys($newValues), array_keys($where)));
        $data = $this->updateData($this->tables[$tableName]['data'], $newValues, $where);
        $this->setDataToTable($tableName, $data);
        $this->save($tableName);
    }

    /**
     * @param string $tableName
     * @param array $where
     * @throws Exception
     */
    public function delete(string $tableName, array $where = [])
    {
        $this->load($tableName);
        $this->validateTableSchema($tableName, array_keys($where));
        if (count($where))
        {
            $data = $this->deleteData($this->tables[$tableName]['data'], $where);
        }
        else
        {
            $data = [];
        }
        $this->setDataToTable($tableName, $data);
        $this->save($tableName);
    }

    private function addDataToTable(string $tableName, array $data)
    {
        array_push($this->tables[$tableName]['data'], $data);
    }

    private function filterData(array $data, array $filters)
    {
        return array_filter($data, function ($row) use ($filters) {
            $match = 0;
            foreach ($filters as $key => $value)
            {
                $match = $row[$key] == $value ? ++$match : $match;
            }
            return count($filters) == $match;
        });
    }

    private function updateData(array $data, array $newValues, array $filters)
    {
        return array_map(function ($row) use ($newValues, $filters) {
            $should_update = 0;
            foreach ($filters as $key => $value)
            {
                $should_update = $row[$key] == $value ? ++$should_update : $should_update;
            }
            return count($filters) == $should_update ? array_replace($row, $newValues) : $row;
        }, $data);
    }

    private function deleteData(array $data, array $filters)
    {
        return array_filter($data, function ($row) use ($filters) {
            $should_delete = 0;
            foreach ($filters as $key => $value)
            {
                $should_delete = $row[$key] == $value ? ++$should_delete : $should_delete;
            }
            return count($filters) != $should_delete;
        });
    }

    private function setDataToTable(string $tableName, array $data)
    {
        $this->tables[$tableName]['data'] = $data;
    }

    /**
     * @param string $tableName
     * @param array $schema
     * @throws Exception
     */
    private function validateTableSchema(string $tableName, array $schema)
    {
        if ($diffs = array_diff($schema, array_keys($this->tables[$tableName]['schema'])))
        {
            throw new Exception(sprintf("Column %s not found", array_values($diffs)[0]));
        }
    }

    private function isRequired(array $properties)
    {
        return !$properties['nullable'] && !isset($properties['default']);
    }

    /**
     * @param string $tableName
     * @param array $data
     * @return array
     * @throws Exception
     */
    private function prepareDataToInsert(string $tableName, array $data)
    {
        $out    = [];
        $schema = $this->tables[$tableName]['schema'];

        foreach ($schema as $field => $properties)
        {
            $value = $this->getValueOrDefaultByKey($properties, $data, $field);
            if ($this->isRequired($schema[$field]) && empty($value))
            {
                throw new Exception(sprintf("No value provided for column %s", $field));
            }

            $out[$field] = $value;
        }

        return $out;
    }

    private function getValueOrDefaultByKey(array $properties, array $data, string $key)
    {
        return isset($data[$key]) ? $data[$key] : (isset($properties['default']) ? $properties['default'] : null);
    }

    /**
     * @param string $table
     * @throws Exception
     */
    protected function load(string $table)
    {
        if (isset($this->tables[$table]))
        {
            return;
        }

        if (!file_exists($this->resolveDBFilePath($table) . '.json'))
        {
            throw new Exception(sprintf("Table %s not found", $table));
        }

        $this->tables[$table] = json_decode(file_get_contents($this->resolveDBFilePath($table) . '.json'), true);
    }

    protected function save(string $table)
    {
        file_put_contents($this->resolveDBFilePath($table) . '.json', json_encode($this->tables[$table]));
    }

    protected function resolveDBFilePath(string $tableName)
    {
        return rtrim($this->dbDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $tableName;
    }
}
