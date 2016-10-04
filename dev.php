<?php
function consoleTextArrayToUTF8String($in){
	$out = '';
	foreach( $in as $v ){
		$out .= iconv('cp866', 'utf-8', $v) . "\n";
	}
	return $out;
}
function dump($text){
	if( !empty($text) && $text !== true ){
		echo '<pre style="background:#fef">';
		print_r($text);
	}else{
		echo '<pre style="background:#eff">';
		ob_start();
		var_dump($text);
		echo ob_get_clean();
	}
	echo '</pre>';
}
function dumpx($text){
	dump($text);
	exit;
}