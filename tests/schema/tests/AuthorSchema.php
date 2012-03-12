<?php
namespace tests;
use Lazy\Schema\SchemaDeclare;
use Lazy\Schema;

class AddressSchema extends SchemaDeclare
{
    function schema()
    {
        $this->column('author_id')
                ->integer();

        $this->column('address')
                ->varchar(128);


        /**
         * $address->author 
         */
        // $this->belongsTo( '\tests\Author' , 'author_id', 'author' );
    }
}

class AuthorSchema extends SchemaDeclare
{
    function schema()
    {

        $this->column('name')
            ->isa('str')
            ->varchar(128);

        $this->column('email')
            ->isa('str')
            ->required()
            ->varchar(128);

        $this->column('identity')
            ->isa('str')
            ->unique()
            ->required()
            ->varchar(128);

        $this->column('confirmed')
            ->isa('bool')
            ->default(false)
            ->boolean();

        $this->mixin('tests\MetadataMixinSchema');


        /**
         * for append:
         *
         *     $author->address[] = array(  );
         *
         *     $record = $author->createAddress(array( ... ));  // return false on failure.
         *
         * for fetch:
         *
         *     foreach( $author->addresses as $address ) {
         *
         *     }
         *
         * for search/find:
         *
         *     $address = $author->addresses->find(k);
         *
         */
        $this->hasMany( 'addresses', '\tests\Address','author_id', 'id');
        // $this->belongsTo( '\tests\Company' , 'company' );
    }

}
