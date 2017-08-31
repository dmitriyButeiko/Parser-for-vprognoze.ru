<?php 

	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	define( 'ROOT_DIR', dirname(__FILE__) );
	
	require_once ROOT_DIR . "/includes/VprognozeRobobetParser.php";

	$vprognozeRobobetParser = VprognozeRobobetParser::getInstance();
	$parsedMatches = $vprognozeRobobetParser->getMatches();

	var_dump($parsedMatches);
?>