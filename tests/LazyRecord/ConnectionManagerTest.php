<?php
use LazyRecord\Connection;

class ConnectionManagerTest extends PHPUnit_Framework_TestCase
{
    public function testConnectionManager()
    {
        // $pdo = new PDO( 'sqlite3::memory:', null, null, array(PDO::ATTR_PERSISTENT => true) );
        $conn = new Connection( 'sqlite::memory:' );
        ok( $conn );

        $manager = LazyRecord\ConnectionManager::getInstance();
        ok( $manager );

        $manager->free();

        $manager->add($conn, 'default');

        /*
        $conn = $manager->getDefaultConnection();
        ok($conn);
         */

        $manager->addDataSource( 'master', array( 
            'dsn' => 'sqlite::memory:',
            'user' => null,
            'pass' => null,
            'options' => array(),
        ));

        $master = $manager->getConnection('master');
        ok( $master );


        // array access test.
        ok( $manager['master'] );

        $manager->free();
    }
}


