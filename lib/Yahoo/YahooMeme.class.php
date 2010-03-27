<?php

/**
 *  Meme PHP 
 *  ---------
 *  A simple PHP class to interface with Yahoo! Meme API. 
 *  @author bigo 
 **/

require_once( "YahooYQLQuery.class.php" );

/* debug galponero! */
function wtf( $smth ) {
    echo "<pre>"; var_dump( $smth ); echo "</pre>";
}

class MemeCore {

    private $_result = null;

    public function execute( $query ) {
        $yql = new YahooYQLQuery( );
        $this->_result =  $yql->execute( $query );
        if ( !$this->_result ) {
            throw new Exception( "No records found" ); 
        }
        if ( $this->_result->query->results->meme ) {
            return $this->memeResults( );
        }
        else if ( $this->_result->query->results->post ) {
            return $this->postResults( );
        }

    }

    private function memeResults( ) {
        $result = $this->_result->query->results; 
        if (count( $result->meme ) == 1 ) {
            return new Meme( $result->meme );
        }
        else if ( count( $result->meme ) > 1 ) {
            $ret = array( );
            foreach( $result->meme as $row ) {
               $ret[] = new Meme( $row ); 
            }
            return $ret;
        }
    }

    private function postResults( ) {
        $result = $this->_result->query->results; 
        if ($result && count( $result->post ) == 1 ) {
            return new Post( $result->post );
        }
        else if ( count( $result->post ) > 1 ) {
            $ret = array( );
            foreach( $result->post as $row ) {
               $ret[] = new Post( $row ); 
            }
            return $ret;
        }
    }
}

class MemeRepository {
    
    private $core = null;

    public function __construct(  ) {
        $this->core = new MemeCore( );
    }
    
    /* this function should be private but for testing purposes it has been ;
     * changed to public. PLEASE DO NOT CALL IT DIRECTLY! */
    public function _yql_query( $query ) {
        return $this->core->execute( $query );
    }

    public function get( $name ) {
        return $this->_yql_query( "SELECT * FROM meme.info WHERE name ='".$name."'" );
    }

    public function following( $name, $offset=0, $limit=10 ) {
        $guid = $this->get( $name )->guid;
        return $this->_yql_query( "SELECT * FROM meme.following( $offset, $limit ) WHERE owner_guid = '$guid'" );
    }

    public function followers ( $name, $offset=0, $limit=10 ) {
        return $this->_yql_query( "SELECT * FROM meme.followers( $offset, $limit ) WHERE owner_guid IN ( SELECT guid FROM meme.info WHERE name = '".$name."' )" );    
    }

    public function search( $query ) {
        return $this->_yql_query( "SELECT * FROM meme.people WHERE query = '$query'" );
    }
}

class Meme extends MemeRepository {
   
    public  $name;
    public  $guid;
    public  $title;
    public  $description;
    public  $url;
    public  $avatar_url;
    public  $language;
    public  $follower_count;
    
    public function __construct( $data = array() ) {
        parent::__construct( ); 
        $this->name = $data->name;
        $this->guid = $data->guid;
        $this->title = $data->title;
        $this->description = $data->description;
        $this->url = $data->url;
        $this->avatar_url = $data->avatar_url;
        $this->language = $data->language;
        $this->follower_count = $data->follower_count;
    }

    /* most weird method overloading EVER! */
    public function __call( $method, $args ) {
        if ( $method == 'following' ) {
            if ( count( $args ) == 3 ) {
                return parent::following( $args[0], $args[1], $args[2] );
            } else if ( count( $args ) == 2 ) {
                return $this->_following( $args[0], $args[1] );
            } else {
                return $this->_following(  );
            }
        }
    }

    public function getPosts( $offset=0, $limit=10 ) {
        if ( !$this->guid ) {
            throw new Exception( 'You are trying get posts from a unknown meme... guid is empty' );
            return;
        }
        return $this->_yql_query( "SELECT * FROM meme.posts( $offset, $limit ) WHERE owner_guid ='$this->guid'" );
    }

    /** this function overloards MemeRepository->following( ). the __call(  ) 
     * function will decide to call either MemeRepository->following or 
     * Meme->following according to the number of arguments.  */
    private function _following( $start = 0,  $limit = 10 ) {
        if ( $this->guid ) {
            return parent::_yql_query( "SELECT * FROM meme.following( $start, $limit ) WHERE owner_guid = '$this->guid'" );
        }
        return parent::following( $this->name, $start, $limit );   
    }

    /** this function overloards MemeRepository->followers( ). the __call(  ) 
     * function will decide to call either MemeRepository->followers or 
     * Meme->followers according to the number of arguments.  */
    private function _followers ( $start = 0, $limit = 10  ) {
        if ( $this->guid ) {
            return parent::_yql_query( "SELECT * FROM meme.followers( $start, $limit ) WHERE owner_guid = '$this->guid'" );
        }
        return parent::followers( $this->name, $start, $limit );   
    }
    
    public function toString( $fullInfo = false  ) {
        $ret = "( ";
        $ret .= "Name=$this->name,";
        $ret .= "Guid=$this->guid";
        if( $fullInfo !== false ) {
            $ret .= ",Title=$this->title,";
            $ret .= "Description=$this->description,";
            $ret .= "Url=$this->url,";
            $ret .= "Avatar_url=$this->avatar_url,";
            $ret .= "Language=$this->language,";
            $ret .= "Follower_count=$this->follower_count";
        }
        $ret .= " )";
        return $ret;
    }
}

class PostRepository {
    
    public function __construct(  ) {
        $this->core = new MemeCore( );
    }
    
    private function _yql_query( $query ) {
        return $this->core->execute( $query );
    }

    public function popular( $offset=0, $limit=10, $locale='' ) {
        return $this->_yql_query( "SELECT * FROM meme.popular( $offset, $limit ) WHERE locale='$locale'" );
    }

    public function search( $query, $offset, $limit ) {
        return $this->_yql_query( "SELECT * FROM meme.search( $offset, $limit ) WHERE query = '$query'" );
    }
}

class Post extends PostRepository {
    public $guid;
    public $pubid;
    public $type;
    public $caption;
    public $content;
    public $comment = null;
    public $url;
    public $timestamp;
    public $repost_count;
    public $origin_guid = null;
    public $origin_pubid = null;
    public $via_guid = null;

    public function __construct( $data = array(  ) ) {
        parent::__construct( ); 
        $this->guid = $data->guid;
        $this->pubid = $data->pubid;
        $this->type = $data->type;
        $this->caption = $data->caption;
        $this->content = $data->content;
        $this->comment = $data->comment;
        $this->url = $data->url;
        $this->timestamp = $data->timestamp;
        $this->respost_count = $data->repost_count;
        $this->origin_guid = $data->origin_guid;
        $this->origin_pubid = $data->origin_pubid;
        $this->via_guid = $data->via_guid;
    }

    public function toString( $fullInfo = false ) {
        $ret = "( ";
        $ret .= "Guid=$this->guid,";
        $ret .= "Pubid=$this->pubid,";
        $ret .= "Type=$this->type";
        if ( $fullInfo !== false ) {
            $ret .= ",Caption=$this->caption,";
            $ret .= "Content=$this->content,";
            $ret .= "Url=$this->url,";
            $ret .= "Timestamp=$this->timestamp,";
            $ret .= "Repost_count=$this->repost_count,";
            $ret .= "Origin_guid=$this->origin_guid,";
            $ret .= "Origin_pubid=$this->origin_pubid,";
            $ret .= "Via_guid=$this->via_guid";
        }
        $ret .= " )";
        return $ret;
    }
}
?>
