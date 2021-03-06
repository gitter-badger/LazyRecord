<?php
use SQLBuilder\Raw;
use AuthorBooks\Model\Book ;
use LazyRecord\Testing\ModelTestCase;
use LazyRecord\RESULT;
/**
 * Testing models:
 *   1. Author
 *   2. Book
 *   3. Address
 */
class BasicCRUDTest extends ModelTestCase
{
    public $driver = 'sqlite';

    public function getModels()
    {
        return array( 
            'AuthorBooks\Model\AuthorSchema',
            'AuthorBooks\Model\BookSchema',
            'AuthorBooks\Model\AuthorBookSchema',
            'AuthorBooks\Model\AddressSchema',
        );
    }

    public function setUp() {
        if( ! extension_loaded('pdo_' . $this->driver) ) {
            $this->markTestSkipped('pdo_' . $this->driver . ' extension is required for model testing');
            return;
        }
        parent::setUp();
    }

    /**
     * @rebuild false
     * @expectedException PDOException
     */
    public function testTitleIsRequired()
    {
        $b = new Book;
        $ret = $b->load(array( 'name' => 'LoadOrCreateTest' ));
        $this->assertResultFail($ret);
        $this->assertNull($b->id);
    }


    public function testRecordRawCreateBook()
    {
        $b = new Book ;
        $ret = $b->rawCreate(array( 'title' => 'Go Programming' ));
        $this->assertResultSuccess($ret);
        $this->assertNotNull($b->id);
        $this->assertEquals(RESULT::TYPE_CREATE, $ret->type);        
        $this->successfulDelete($b);
    }

    public function testRecordRawUpdateBook()
    {
        $b = new \AuthorBooks\Model\Book;
        $ret = $b->rawCreate(array( 'title' => 'Go Programming without software validation' ));
        $this->assertResultSuccess($ret);
        $this->assertNotNull($b->id);
        $ret = $b->rawUpdate(array( 'title' => 'Perl Programming without filtering' ));
        $this->assertResultSuccess($ret);
        $this->assertNotNull($b->id);
        $this->assertEquals(RESULT::TYPE_UPDATE, $ret->type);
        $this->successfulDelete($b);
    }


    public function testFind()
    {
        $results = array();
        $book1 = new Book;
        $ret = $book1->create(array( 'title' => 'Book1' ));
        $this->assertResultSuccess($ret);

        $book2 = new Book;
        $ret = $book2->create(array( 'title' => 'Book2' ));
        $this->assertResultSuccess($ret);

        $findBook = new Book;
        $ret = $findBook->find($book1->id);
        $this->assertResultSuccess($ret);
        $this->assertEquals($book1->id, $findBook->id);


        $ret = $findBook->find($book2->id);
        $this->assertResultSuccess($ret);
        $this->assertEquals($book2->id, $findBook->id);
    }


    public function testLoadOrCreateModel() 
    {
        $results = array();
        $b = new \AuthorBooks\Model\Book ;

        $ret = $b->create(array( 'title' => 'Should Create, not load this' ));
        $this->assertResultSuccess($ret);
        $results[] = $ret;

        $ret = $b->create(array( 'title' => 'LoadOrCreateTest' ));
        $this->assertResultSuccess($ret);
        $results[] = $ret;

        $id = $b->id;
        $this->assertNotNull($id);

        $ret = $b->loadOrCreate( array( 'title' => 'LoadOrCreateTest'  ) , 'title' );
        $this->assertResultSuccess($ret);
        $this->assertEquals($id, $b->id, 'is the same ID');
        $this->assertEquals(RESULT::TYPE_LOAD, $ret->type);
        $results[] = $ret;


        $b2 = new Book;
        $ret = $b2->loadOrCreate( array( 'title' => 'LoadOrCreateTest'  ) , 'title' );
        $this->assertResultSuccess($ret);
        $this->assertEquals($id,$b2->id);
        $results[] = $ret;

        $ret = $b2->loadOrCreate( array( 'title' => 'LoadOrCreateTest2'  ) , 'title' );
        $this->assertResultSuccess($ret);
        $this->assertEquals(RESULT::TYPE_CREATE, $ret->type);
        $this->assertNotEquals($id, $b2->id , 'we should create anther one'); 
        $results[] = $ret;

        $b3 = new Book;
        $ret = $b3->loadOrCreate( array( 'title' => 'LoadOrCreateTest3'  ) , 'title' );
        $this->assertResultSuccess($ret);
        $this->assertNotEquals($id, $b3->id , 'we should create anther one'); 
        $results[] = $ret;

        $this->successfulDelete($b3);

        foreach($results as $r ) {
            $book = new Book();
            $book->find(intval($r->id));
            if ($book->id) {
                $book->delete();
            }
        }
    }

    public function booleanTrueTestDataProvider()
    {
        return array(
            array( array( 'name' => 'Foo' , 'country' => 'Tokyo', 'confirmed' => 1 ) ),
            array( array( 'name' => 'Foo' , 'country' => 'Tokyo', 'confirmed' => '1' ) ),
            array( array( 'name' => 'Foo' , 'country' => 'Tokyo', 'confirmed' => true ) ),
            array( array( 'name' => 'Foo' , 'country' => 'Tokyo', 'confirmed' => 'true' ) ),
            array( array( 'name' => 'Foo' , 'country' => 'Tokyo', 'confirmed' => ' ' ) ),  // space string (true)
        );
    }

    public function booleanFalseTestDataProvider()
    {
        return array(
#              array( array( 'name' => 'Foo' , 'country' => 'Tokyo', 'confirmed' => 0 ) ),
#              array( array( 'name' => 'Foo' , 'country' => 'Tokyo', 'confirmed' => '0' ) ),
#              array( array( 'name' => 'Foo' , 'country' => 'Tokyo', 'confirmed' => false ) ),
#              array( array( 'name' => 'Foo' , 'country' => 'Tokyo', 'confirmed' => 'false' ) ),
            array( array( 'name' => 'Foo' , 'country' => 'Tokyo', 'confirmed' => '' ) ),  // empty string should be (false)
            // array( array( 'name' => 'Foo' , 'country' => 'Tokyo', 'confirmed' => 'aa' ) ),
            // array( array( 'name' => 'Foo' , 'country' => 'Tokyo', 'confirmed' => 'bb' ) ),
        );
    }

    public function testModelUpdateRaw() 
    {
        $author = new \AuthorBooks\Model\Author;
        $ret = $author->create(array( 
            'name' => 'Mary III',
            'email' => 'zz3@zz3',
            'identity' => 'zz3',
        ));
        $this->assertResultSuccess($ret);

        $ret = $author->update(array('id' => new Raw('id + 3') ));
        $this->assertResultSuccess($ret);
        $this->assertEquals(RESULT::TYPE_UPDATE, $ret->type);
    }

    public function testManyToManyRelationRecordCreate()
    {
        $author = new \AuthorBooks\Model\Author;
        $author->create(array( 'name' => 'Z' , 'email' => 'z@z' , 'identity' => 'z' ));
        $this->assertNotNull( 
            $book = $author->books->create( array( 
                'title' => 'Programming Perl I',
                ':author_books' => array( 'created_on' => '2010-01-01' ),
            ))
        );
        $this->assertNotNull($book->id);
        $this->assertEquals( 'Programming Perl I' , $book->title );

        $this->assertEquals( 1, $author->books->size() );
        $this->assertEquals( 1, $author->author_books->size() );
        $this->assertNotNull( $author->author_books[0] );
        $this->assertNotNull( $author->author_books[0]->created_on );
        $this->assertEquals( '2010-01-01', $author->author_books[0]->created_on->format('Y-m-d') );

        $author->books[] = array( 
            'title' => 'Programming Perl II',
        );
        $this->assertEquals( 2, $author->books->size() , '2 books' );

        $books = $author->books;
        $this->assertEquals( 2, $books->size() , '2 books' );

        foreach( $books as $book ) {
            $this->assertNotNull( $book->id );
            $this->assertNotNull( $book->title );
        }

        foreach( $author->books as $book ) {
            $this->assertNotNull( $book->id );
            $this->assertNotNull( $book->title );
        }

        $books = $author->books;
        $this->assertEquals( 2, $books->size() , '2 books' );
        $this->successfulDelete($author);
    }


    /**
     * @rebuild false
     */
    public function testPrimaryKeyIdIsInteger()
    {
        $author = new \AuthorBooks\Model\Author;
        $ret = $author->create(array( 'name' => 'Z' , 'email' => 'z@z' , 'identity' => 'z' ));
        $this->assertResultSuccess($ret);

        // XXX: in different database engine, it's different.
        // sometimes it's string, sometimes it's integer
        // ok( is_string( $author->getValue('id') ) );
        $this->assertTrue(is_integer($author->get('id')));
        $this->successfulDelete($author);
    }


    public function testManyToManyRelationFetchRecord()
    {
        $author = new \AuthorBooks\Model\Author;
        $author->create(array( 'name' => 'Z' , 'email' => 'z@z' , 'identity' => 'z' ));

        $book = $author->books->create(array( 'title' => 'Book Test' ));
        $this->assertNotNull( $book );
        $this->assertNotNull( $book->id , 'book is created' );

        $ret = $book->delete();
        $this->assertTrue($ret->success);
        $this->assertEquals(RESULT::TYPE_DELETE, $ret->type);

        $ab = new \AuthorBooks\Model\AuthorBook;
        $book = new \AuthorBooks\Model\Book ;

        // should not include this
        $this->assertTrue( $book->create(array( 'title' => 'Book I Ex' ))->success );
        $this->assertTrue( $book->create(array( 'title' => 'Book I' ))->success );

        result_ok( $ab->create(array( 
            'author_id' => $author->id,
            'book_id' => $book->id,
        )) );

        $this->assertTrue( $book->create(array( 'title' => 'Book II' ))->success );
        $ab->create(array( 
            'author_id' => $author->id,
            'book_id' => $book->id,
        ));

        $this->assertTrue( $book->create(array( 'title' => 'Book III' ))->success );
        $ab->create(array( 
            'author_id' => $author->id,
            'book_id' => $book->id,
        ));

        // retrieve books from relationshipt
        $author->flushCache();
        $books = $author->books;
        $this->assertEquals( 3, $books->size() , 'We have 3 books' );


        $bookTitles = array();
        foreach( $books->items() as $item ) {
            $bookTitles[ $item->title ] = true;
            $item->delete();
        }

        $this->assertCount( 3, array_keys($bookTitles) );
        ok( $bookTitles[ 'Book I' ] );
        ok( $bookTitles[ 'Book II' ] );
        ok( $bookTitles[ 'Book III' ] );
        ok( ! isset($bookTitles[ 'Book I Ex' ] ) );

        $author->delete();
    }

    public function testHasManyRelationCreate2()
    {
        $author = new \AuthorBooks\Model\Author;
        $author->create(array( 'name' => 'Z' , 'email' => 'z@z' , 'identity' => 'z' ));
        ok( $author->id );

        // append items
        $author->addresses[] = array( 'address' => 'Harvard' );
        $author->addresses[] = array( 'address' => 'Harvard II' );

        $this->assertEquals(2, $author->addresses->size() , 'just two item' );

        $addresses = $author->addresses->items();
        $this->assertNotEmpty($addresses);
        $this->assertEquals( 'Harvard' , $addresses[0]->address );

        $a = $addresses[0];
        ok( $retAuthor = $a->author );
        ok( $retAuthor->id );
        ok( $retAuthor->name );
        $this->assertEquals( 'Z', $retAuthor->name );

        $author->delete();
    }

    public function testHasManyRelationCreate()
    {
        $author = new \AuthorBooks\Model\Author;
        $author->create(array( 'name' => 'Z' , 'email' => 'z@z' , 'identity' => 'z' ));
        ok( $author->id );

        $address = $author->addresses->create(array( 
            'address' => 'farfaraway'
        ));

        ok( $address->id );
        ok( $address->author_id );
        $this->assertEquals( $author->id, $address->author_id );

        $this->assertEquals( 'farfaraway' , $address->address );

        $address->delete();
        $author->delete();

    }

    public function testHasManyRelationFetch()
    {
        $author = new \AuthorBooks\Model\Author;
        ok( $author );

        $author->create(array( 'name' => 'Z' , 'email' => 'z@z' , 'identity' => 'z' ));
        ok( $author->id );

        $address = new \AuthorBooks\Model\Address;
        ok( $address );

        $address->create(array( 
            'author_id' => $author->id,
            'address' => 'Taiwan Taipei',
        ));
        ok( $address->author );
        ok( $address->author->id );
        $this->assertEquals( $author->id, $address->author->id );

        $address->create(array( 
            'author_id' => $author->id,
            'address' => 'Taiwan Taipei II',
        ));

        // xxx: provide getAddresses() method generator
        $addresses = $author->addresses;
        ok( $addresses );

        $items = $addresses->items();
        ok( $items );

        ok( $addresses[0] );
        ok( $addresses[1] );
        ok( ! isset($addresses[2]) );
        ok( ! @$addresses[2] );

        ok( $addresses[0]->id );
        ok( $addresses[1]->id );

        ok( $size = $addresses->size() );
        $this->assertEquals( 2 , $size );

        foreach( $author->addresses as $ad ) {
            ok( $ad->delete()->success );
        }

        $author->delete();
    }


    /**
     * @basedata false
     */
    public function testRecordUpdateWithRawSQL()
    {
        $n = new \AuthorBooks\Model\Book ;
        $n->create(array(
            'title' => 'book title',
            'view' => 0,
        ));
        $this->assertEquals( 0 , $n->view );
        $ret = $n->update(array( 
            'view' => new Raw('view + 1')
        ), array('reload' => true));

        $this->assertTrue($ret->success, $ret->message);
        $this->assertEquals(1, $n->view);

        $n->update(array( 
            'view' => new Raw('view + 3'),
        ), array('reload' => true));
        $ret = $n->reload();
        $this->assertTrue( $ret->success );
        $this->assertEquals( 4, $n->view );
        result_ok($n->delete());
    }



    /**
     * @rebuild false
     */
    public function testZeroInflator()
    {
        $b = new \AuthorBooks\Model\Book ;
        $ret = $b->create(array( 'title' => 'Zero number inflator' , 'view' => 0 ));
        $this->assertResultSuccess($ret);
        $this->assertNotNull($b->id);
        $this->assertEquals(0 , $b->view);

        $ret = $b->find($ret->id);
        $this->assertResultSuccess($ret);
        $this->assertNotNull($b->id);
        $this->assertEquals(0 , $b->view);
        $this->successfulDelete($b);
    }


    /**
     * @rebuild false
     */
    public function testUpdateWithReloadOption()
    {
        $b = new \AuthorBooks\Model\Book ;
        $ret = $b->create(array( 'title' => 'Create for reload test' , 'view' => 0 ));
        $this->assertResultSuccess($ret);

        // test incremental with Raw statement
        $ret = $b->update(array( 'view'  => new Raw('view + 1') ), array('reload' => true));
        $this->assertResultSuccess($ret);
        $this->assertEquals(1,  $b->view);

        $ret = $b->update(array('view' => new Raw('view + 1') ), array('reload' => true));
        $this->assertResultSuccess($ret);
        $this->assertEquals( 2,  $b->view );

        $ret = $b->delete();
        $this->assertResultSuccess($ret);
    }
}


