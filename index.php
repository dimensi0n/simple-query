<?php
require_once 'src/Query.php';

use SimpleQuery\QueryBuilder;

function selectAndEcho(QueryBuilder $queryBuilder)
{
    foreach ($queryBuilder->select('users', ['id','username', 'age'], ['ORDER BY id', 'LIMIT 10']) as  $row) {
        echo 'Id : '.$row['id']."\n";
        echo 'Username : '.$row['username']."\n";
        echo 'Age : '.$row['age']."\n";
    }
}

echo 'DROP, CREATE and INSERT'."\n";
echo '======================='."\n";

$queryBuilder = new QueryBuilder('mysql:host=localhost;dbname=dbname', 'newuser', 'mdp');
$queryBuilder->dropTable('users')->create('users', [
    'username' => ['type' => 'varchar (15)', 'notNullable' => true, 'unique' => true],
    'password' => ['type' => 'varchar (200)'],
    'age' => ['type' => 'int', 'default' => 20]
])->insert('users' , ['username' => 'erwan', 'password' => 'admin', 'age' => 16, 'id'=> 0]);

selectAndEcho($queryBuilder);

echo "\n".'UPDATE AND SELECT'."\n";
echo '======================='."\n";
$queryBuilder->update('users', ['username' => 'admin'], ['username' => 'erwan']);

selectAndEcho($queryBuilder);

echo "\n".'DELETE AND SELECT'."\n";
echo '======================='."\n";
$queryBuilder->delete('users', ['age' => 16]);

echo 'Number of rows : '.$queryBuilder->select('users', ['id','username', 'age'])->rowCount()."\n";
