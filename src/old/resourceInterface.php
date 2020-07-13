<?php

namespace Nuclear\system\model;

interface resourceInterface
{
    const RELATIONSHIPS_MANY_TO_ONE = "SELECT %2\$s.* FROM %2\$s JOIN %1\$s ON %1\$s.%3\$s = %2\$s.%3\$s AND %1\$s.active = 1 WHERE %2\$s.%3\$s = %4\$s AND %2\$s.active = 1 GROUP BY %2\$s.%3\$s;";
    const EXECUTE_INSERT = "INSERT INTO %s (%s) VALUES (%s);";
    const EXECUTE_UPDATE = "UPDATE %s SET %s WHERE %s;";
    const EXECUTE_DELETE = "UPDATE %s SET active = 0 WHERE %s;";
}