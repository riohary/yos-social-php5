<?php

require_once( '../../lib/Yahoo/YahooMeme.class.php' );


$meme = new Meme( );
//
//print "<h2>[info about bigo]</h2> <br />";
//print $meme->get( "bigodines" )->toString( );
//
//print "<h2>[bigo followers]</h2> <br/>";
//foreach( $meme->following( "bigodines" ) as $row) print $row->toString(  ) . "<br />";
//

print "<h2>[bigo latest 5 posts]</h2>";

$x = $meme->get( "bigodines" );
wtf( $x );
foreach( $x->getPosts( 0, 5 ) as $row ) print $row->toString(  ) . "<br />\n";

print "<h2>[meme popular posts]</h2> <br />";
$post = new Post(  );
foreach( $post->popular( ) as $row) print $row->toString(  ) . "<br />";

print "<h2>[latest 5 posts]</h2> <br />";
foreach ( $post->search( 'sort:cdate', 0, 5 ) as $row ) print  $row->toString(  ) . "<br />\n";
//


?>
