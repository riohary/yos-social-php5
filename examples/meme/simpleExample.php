<?php

require_once( '../../lib/Yahoo/YahooMEME.class.php' );


$meme = new Meme( );

print "<h2>[info about bigo]</h2> <br />";
print $meme->get( "bigodines" )->toString( );

print "<h2>[bigo followers]</h2> <br/>";
foreach( $meme->following( "bigodines" ) as $row) print $row->toString(  ) . "<br />";

print "<h2>[popular posts]</h2> <br />";

$post = new Post(  );
foreach( $post->popular( ) as $row) print $row->toString(  ) . "<br />";

?>
