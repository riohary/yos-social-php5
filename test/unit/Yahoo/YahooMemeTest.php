<?php


require_once 'PHPUnit/Framework.php';
require dirname(__FILE__).'/../../../lib/Yahoo/YahooMEME.class.php';

class YahooMemeTest extends PHPUnit_Framework_TestCase {
    public function setup( ) {
        ;
    }

    public function testMemeRepositoryGet(  ) {

        $dummyMeme = new Meme(  );
        $dummyMeme->guid = '123abc';
        $stub = $this->getMock( 'MemeRepository', array( '_yql_query' ) );
        $stub->expects( $this->once(  ) )
                ->method( '_yql_query' )
                ->will( $this->returnValue( $dummyMeme ) );

        $this->assertEquals( '123abc', $stub->get( 'abc' )->guid );

    }

    public function testMemeRepositoryFollowing(  ) {
        $memeList = array(  );
        $meme1 = new Meme( );
        $meme1->guid = '1'; 
        $meme1->name = "ronaldo";
        $meme1->language = 'pt';
        
        $meme2 = new Meme( ); 
        $meme2->guid = '12asdf4';
        $meme2->name = 'genivaldo oliveira júnior';
        $meme2->language = 'en';
        $meme2->avatar_url = 'http://www.yahoo.com/bleh.png'; 
        
        $meme3 = new Meme( );
        $meme3->guid = '3333';
        $meme3->name = 'ubirajara';
        $meme3->language = 'pt'; 
       
        $memeList[] = $meme1; $memeList[] = $meme2; $memeList[] = $meme3;
        $mock = $this->getMock( 'MemeRepository', array( 'get', '_yql_query' ) );
        $mock->expects( $this->any(  ) )
            ->method( 'get' )
            ->will( $this->returnValue( $meme1 ) );
        $mock->expects( $this->any(  ) )
            ->method( '_yql_query' )
            ->will( $this->returnValue( $memeList ) );
        $ret = $mock->following( 'anybody' );
        $this->assertEquals( 3, count( $mock->following( 'anybody' ) ) );
        $this->assertEquals( '1', $ret[0]->guid );
        $this->assertEquals( 'en', $ret[1]->language );
    }

    public function testMemeRepositoryFollowers(  ) {
        $memeList = array(  );
        $meme1 = new Meme( );
        $meme1->guid = '1'; 
        $meme1->name = "ronaldo";
        $meme1->language = 'pt';
        
        $meme2 = new Meme( ); 
        $meme2->guid = '12asdf4';
        $meme2->name = 'genivaldo oliveira júnior';
        $meme2->language = 'en';
        $meme2->avatar_url = 'http://www.yahoo.com/bleh.png'; 
        
        $meme3 = new Meme( );
        $meme3->guid = '3333';
        $meme3->name = 'ubirajara';
        $meme3->language = 'pt'; 
       
        $memeList[] = $meme1; $memeList[] = $meme2; $memeList[] = $meme3;
        $mock = $this->getMock( 'MemeRepository', array( 'get', '_yql_query' ) );
        $mock->expects( $this->any(  ) )
            ->method( 'get' )
            ->will( $this->returnValue( $meme1 ) );
        $mock->expects( $this->any(  ) )
            ->method( '_yql_query' )
            ->will( $this->returnValue( $memeList ) );
        $ret = $mock->following( 'anybody' );
        $this->assertEquals( 3, count( $mock->followers( 'anybody' ) ) );
        $this->assertEquals( '1', $ret[0]->guid );
        $this->assertEquals( 'en', $ret[1]->language );
    }

    public function testMemeRepositorySearch(  ) {
        // TODO: create a test that is different from the others... is this 
        // needed, as we are only fwding the results from _yql_query() 
        // anyway...
    }
}
?> 
