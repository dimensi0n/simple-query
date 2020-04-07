<?php
use PHPUnit\Framework\TestCase;
use SimpleQuery\QueryBuilder;

class QueryBuilderTest extends TestCase
{

    public function testDropTable()
    {
        $queryBuilder = new QueryBuilder('sqlite:testdb.sqlite');
        try {
            $queryBuilder->dropTable('users');
        } catch (\PDOException $exception) {
            throw $exception;
        }
        $this->assertFileExists('testdb.sqlite');
        $statement = $queryBuilder->select('sqlite_master', ['name'], ['type' => 'table']);
        $statement->execute();
        $tables = $statement->fetchAll();
        $this->assertEquals([], $tables);
    }

    public function testCreateTable()
    {
        $queryBuilder = new QueryBuilder('sqlite:testdb.sqlite');
        try {
            $queryBuilder->create('users', [
                'username' => ['type' => 'varchar (15)', 'notNullable' => true, 'unique' => true],
                'password' => ['type' => 'varchar (200)'],
                'age' => ['type' => 'integer', 'default' => 20]
            ]);
            $statement = $queryBuilder->select('sqlite_master', ['name'], ['type' => 'table']);
            $statement->execute();
            $tables = $statement->fetchAll();
            $this->assertContains('users', $tables[0]);
        } catch (\PDOException $exception) {
            throw $exception;
        } 
    }

    public function testInsert()
    {
        $queryBuilder = new QueryBuilder('sqlite:testdb.sqlite');
        $data = ['username' => 'erwan', 'age' => 16, 'password' => 'this_is_a_secure_password'];
        $queryBuilder->insert('users', $data);
        $statement = $queryBuilder->select('users', ['id', 'username', 'age', 'password']);
        $this->assertCount(1, $statement);
    }

    public function testUpdate()
    {
        $queryBuilder = new QueryBuilder('sqlite:testdb.sqlite');
        $queryBuilder->update('users', ['age' => 17], ['username' => 'erwan']);
        $statement = $queryBuilder->select('users', ['id', 'username', 'age', 'password'], ['age' => 17]);
        $this->assertCount(1, $statement);
    }

    public function testDelete()
    {
        $queryBuilder = new QueryBuilder('sqlite:testdb.sqlite');
        $data = ['username' => 'pierre', 'age' => 17, 'password' => 'this_is_another_a_secure_password'];
        $queryBuilder->insert('users', $data);
        $queryBuilder->delete('users', ['age' => 17]);
        $statement = $queryBuilder->select('users');
        $this->assertCount(0, $statement);
    }
}