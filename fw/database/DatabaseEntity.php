<?php
namespace fw\database;

class DatabaseEntity
{

    public static function all(string $className): iterable
    {
        $conn = DatabaseConnection::getInstance();

        $class = new \ReflectionClass($className);

        $tableName = $class->getProperty("table")->getValue();
        $primaryKey = $class->getProperty("primaryKey")->getValue();

        $stmt = $conn->query('SELECT * FROM ' . $tableName);

        $res = $stmt->execute();

        if ($res) {
            $list = Array();
            while ($entityDB = $stmt->fetchObject($class->getName())) {
                array_push($list, $entityDB);
            }
            return $list;
        }
    }

    public static function find(string $className, $key): Entity
    {
        $entity = new $className();
        $class = new \ReflectionClass($entity);

        $propPrimaryKey = $class->getProperty("primaryKey")->getValue();

        $class->getProperty($propPrimaryKey)->setValue($entity, $key);

        return self::load($entity) ? $entity : null;
    }

    public static function load(Entity $entity): bool
    {
        $conn = DatabaseConnection::getInstance();
        $conn->setAttribute(\PDO::ATTR_FETCH_TABLE_NAMES, true);

        $class = new \ReflectionClass($entity);

        $tableName = $class->getProperty("table")->getValue();
        $primaryKey = $class->getProperty("primaryKey")->getValue();

        $relationship = $class->getProperty("relationship");
        if ($relationship) {
            $relationship = $relationship->getValue();
        }

        $relString = '';
        foreach ($relationship as $propName => $fieldName) {
            $rel = $class->getProperty($propName)->getValue($entity);
            $classRel = new \ReflectionClass($rel);

            $tableNameRel = $classRel->getProperty("table")->getValue();
            $primaryKeyRel = $classRel->getProperty("primaryKey")->getValue();

            $relString .= ' LEFT JOIN ' . $tableNameRel . ' ON ' . $tableName . '.' . $fieldName . '=' . $tableNameRel . '.' . $primaryKeyRel;
        }

        $stmt = $conn->query('SELECT * FROM `' . $tableName . '`' . $relString . ' WHERE ' . $tableName . '.' . $primaryKey . ' = ' . $class->getProperty($primaryKey)
            ->getValue($entity));

        $res = $stmt->execute();

        if ($res) {
            $entityDB = $stmt->fetch(\PDO::FETCH_ASSOC);

            $props = $class->getProperties();
            foreach ($props as $prop) {
                $name = $prop->getName();
                if ($prop->isStatic() || $name == 'class') {
                    continue;
                }

                if ($rel = $relationship[$name] ?? null) {
                    $entityRel = $entity->{$name};

                    $classRel = new \ReflectionClass($entityRel);
                    $tableNameRel = $classRel->getProperty("table")->getValue();

                    $propsRel = $classRel->getProperties();
                    foreach ($propsRel as $propRel) {
                        $nameRel = $propRel->getName();
                        if ($propRel->isStatic() || $nameRel == 'class') {
                            continue;
                        }

                        $entityRel->{$nameRel} = $entityDB[$tableNameRel . '.' . $nameRel];
                    }
                } else {
                    $entity->{$name} = $entityDB[$tableName . '.' . $name];
                }
            }

            return $res;
        }
    }

    public static function insert(Entity $entity): bool
    {
        $conn = DatabaseConnection::getInstance();

        $class = new \ReflectionClass($entity);

        $tableName = $class->getProperty("table")->getValue();
        $props = $class->getProperties();

        $relationship = $class->getProperty("relationship");
        if ($relationship) {
            $relationship = $relationship->getValue();
        }

        $values = Array();
        $fields = null;
        $params = null;
        foreach ($props as $prop) {
            $name = $prop->getName();
            if ($prop->isStatic() || $name == 'class') {
                continue;
            }

            if ($fields) {
                $fields .= ',';
                $params .= ',';
            }

            $value = $prop->getValue($entity);
            if ($value && $r = $relationship[$name] ?? null) {
                $name = $r;
                $class = new \ReflectionClass($value);
                $primaryKey = $class->getProperty("primaryKey")->getValue();
                $value = $class->getProperty($primaryKey)->getValue($value);
            }

            $fields .= '`' . $name . '`';
            $params .= '?';

            array_push($values, $value);
        }

        $stmt = $conn->prepare('INSERT INTO `' . $tableName . '`(' . $fields . ') VALUES (' . $params . ')');

        $i = 0;
        foreach ($values as $value) {
            $stmt->bindValue(++ $i, $value);
        }

        return $stmt->execute();
    }

    public static function update(Entity $entity): bool
    {
        $conn = DatabaseConnection::getInstance();

        $class = new \ReflectionClass($entity);

        $primaryKey = $class->getProperty("primaryKey")->getValue();

        $tableName = $class->getProperty("table")->getValue();

        $relationship = $class->getProperty("relationship");
        if ($relationship) {
            $relationship = $relationship->getValue();
        }

        $primaryKeyValue = $class->getProperty($primaryKey)->getValue($entity);
        $props = $class->getProperties();

        $values = Array();
        $fields = null;
        $params = null;
        foreach ($props as $prop) {
            $name = $prop->getName();
            if ($prop->isStatic() || $name == 'class' || $name == $primaryKey) {
                continue;
            }

            if ($fields) {
                $fields .= ',';
            }

            $value = $prop->getValue($entity);
            if ($value && $r = $relationship[$name] ?? null) {
                $name = $r;
                $class = new \ReflectionClass($value);
                $primaryKey = $class->getProperty("primaryKey")->getValue();
                $value = $class->getProperty($primaryKey)->getValue($value);
            }

            $fields .= '`' . $name . '`=?';

            array_push($values, $value);
        }

        $stmt = $conn->prepare('UPDATE `' . $tableName . '` SET ' . $fields . ' WHERE `' . $primaryKey . '` = ?');

        $i = 0;
        foreach ($values as $value) {
            $stmt->bindValue(++ $i, $value);
        }

        $stmt->bindValue(++ $i, $primaryKeyValue);

        return $stmt->execute();
    }

    public static function delete(Entity $entity): bool
    {
        $conn = DatabaseConnection::getInstance();

        $class = new \ReflectionClass($entity);

        $tableName = $class->getProperty("table")->getValue();
        $primaryKey = $class->getProperty("primaryKey")->getValue();

        $stmt = $conn->query('DELETE FROM `' . $tableName . '` WHERE `' . $primaryKey . '` = ' . $class->getProperty($primaryKey)
            ->getValue($entity));

        return $stmt->execute();
    }

    public static function deleteWithFilter(string $className, Array $fieldsFilter): bool
    {
        if (! $fieldsFilter || count($fieldsFilter) == 0) {
            throw new \Exception("Não foi definido os campos para filtro.");
        }

        $class = new \ReflectionClass($className);
        $tableName = $class->getProperty("table")->getValue();

        $filter = '';
        foreach ($fieldsFilter as $key => $value) {
            $filter .= ' AND ' . $key . (is_array($value) ? ' in (' . implode(',', $value) . ')' : '=' . (is_string($value) ? '\'' . $value . '\'' : $value));
        }

        $conn = DatabaseConnection::getInstance();

        return $conn->exec('DELETE FROM ' . $tableName . ' WHERE 1' . $filter) > 0;
    }
}
