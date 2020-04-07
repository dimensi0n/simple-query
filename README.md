# simple-query

![PHP Composer](https://github.com/dimensi0n/simple-query/workflows/PHP%20Composer/badge.svg)

Provides a simple and easy to use query builder

Install it :

```bash
composer require dimensi0n/simple-query
```

Initialize it like a PDO instance :

```php
use SimpleQuery\QueryBuilder;
$queryBuilder = new QueryBuilder('mysql:host=localhost;dbname=test', $user, $pass);
```

Create a table :

```php
$queryBuilder->create('users', [
    'username' => ['type' => 'varchar (15)', 'notNullable' => true, 'unique' => true],        'password' => ['type' => 'varchar (200)'],
    'age' => ['type' => 'int', 'default' => 20]
]); 
/* CREATE TABLE IF NOT EXISTS ( 
    id INTEGER PRIMARY KEY, 
    username VARCHAR (15) NOT NULL UNIQUE,
    password VARCHAR (200),
    age INTEGER DEFAULT 20
)
*/
```

Drop a table :

```php
$queryBuilder->dropTable('users'); // DROP TABLE IF EXISTS users
```

Select :

```php
$statement = $queryBuilder->select('users', ['id', 'username', 'age', 'password']); // SELECT id, username, age, password FROM users
$statement = $queryBuilder->select('users', ['id', 'username', 'age', 'password'], ['age' => 16]); // SELECT id, username, age, password FROM users WHERE age = 17;
$statement->fetchAll(); // select returns a PDOStatement
```

Insert :

```php
$queryBuilder->insert('users', ['username' => 'erwan', 'age' => 16, 'password' => 'this_is_a_secure_password']); // INSERT INTO users (username, age, password) VALUES ('erwan', 16, 'this_is_a_secure_password')
```

Update :

```php
$queryBuilder->update('users', ['age' => 17], ['username' => 'erwan']); // UPDATE users SET age = 17 WHERE username = erwan
```

Delete :

```php
$queryBuilder->delete('users', ['age' => 17]); // DELETE users WHERE age = 17
```



For further information check the API docs : dimensi0n.github.io/simple-query/api