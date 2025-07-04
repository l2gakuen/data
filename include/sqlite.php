<?php

/**
	---------------------------------------------------------------------------------------------------------------------------
	SQLite procedural emultation layer v1.00

 * @author Javier Gutiérrez Chamorro (Guti) - https://www.javiergutierrezchamorro.com
 * @link https://www.javiergutierrezchamorro.com
 * @copyright © Copyright 2020
 * @package sqlite.inc.php
 * @license LGPL
 * @version 1.00
	---------------------------------------------------------------------------------------------------------------------------
 */


// ---------------------------------------------------------------------------------------------------------------------------
function sqlite_open($psFilename, $piMode = SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE)
{
	global $isManager;
	$piHandle = new SQLite3($psFilename, $piMode);
	return ($piHandle);
}


// ---------------------------------------------------------------------------------------------------------------------------
function sqlite_close($piHandle)
{
	$bResult = $piHandle->close();
	unset($piHandle);
	return ($bResult);
}


// ---------------------------------------------------------------------------------------------------------------------------
function sqlite_exec($piHandle, $psQuery)
{
	$oResult = $piHandle->exec($psQuery);
	return ($oResult);
}


// ---------------------------------------------------------------------------------------------------------------------------
function sqlite_query($piHandle, $psQuery)
{
	$oResult = $piHandle->query($psQuery);
	return ($oResult);
}

// ---------------------------------------------------------------------------------------------------------------------------
function sqlite_changes($piHandle)
{
	$iResult = $piHandle->changes();
	return ($iResult);
}


// ---------------------------------------------------------------------------------------------------------------------------
function sqlite_last_insert_row_id($piHandle)
{
	$iResult = $piHandle->lastInsertRowID();
	return ($iResult);
}


// ---------------------------------------------------------------------------------------------------------------------------
function sqlite_free_result($poResultset)
{
	$bResult = $poResultset->finalize();
	unset($poResultset);
	return ($bResult);
}


// ---------------------------------------------------------------------------------------------------------------------------
function sqlite_escape_string($psString)
{
	$sResult = SQLite3::escapeString($psString);
	return ($sResult);
}


// ---------------------------------------------------------------------------------------------------------------------------
function sqlite_errno($piHandle)
{
	$iResult = $piHandle->lastErrorCode();
	return ($iResult);
}


// ---------------------------------------------------------------------------------------------------------------------------
function sqlite_error($piHandle)
{
	$sResult = $piHandle->lastErrorMsg();
	return ($sResult);
}


// ---------------------------------------------------------------------------------------------------------------------------
function sqlite_fetch_assoc($poResultset)
{
	$aResult = $poResultset->fetchArray(SQLITE3_ASSOC);
	return ($aResult);
}

// ---------------------------------------------------------------------------------------------------------------------------
function sqlite_fetch_row($poResultset)
{
	$aResult = $poResultset->fetchArray(SQLITE3_NUM);
	return ($aResult);
}


// ---------------------------------------------------------------------------------------------------------------------------
function sqlite_fetch_array($poResultset, $piMode = SQLITE3_BOTH)
{
	$aResult = $poResultset->fetchArray($piMode);
	return ($aResult);
}

function sqlite_create_function($piHandle, $function, $functionname = null)
{
	$sResult = $piHandle->createFunction($function, $functionname == null ? $function : $functionname);
	return ($sResult);
}


function DATEDIFF($date1, $date2)
{
	$date1 = strtotime($date1); // or your date as well
	$date2 = strtotime($date2);
	$datediff = $date1 - $date2;
	return round($datediff / (60 * 60 * 24));
}
function NOW()
{
	return date("Y-m-d H:i:s");
}

function DAYOFWEEK($date)
{
	$date = strtotime($date);
	$day = date('w', $date);
	return $day;
}

function WEEKDAY($date)
{
	$date = strtotime($date);
	$day = date('w', $date);
	return $day - 1;
}
function NUM_OF_WEEKS($date)
{
	$date = strtotime($date);
	$day = date('w', $date);
	$week = date('W', $date);
	return $week;
}
function SUBDATE($date, $days)
{
	$date = strtotime($date);
	$day = date('w', $date);
	$date = strtotime("-$days day", $date);
	return date('Y-m-d', $date);
}

function DATEFORMAT($date, $format)
{
	$date = strtotime($date);
	return date($format, $date);
}

function my_udf_md5($string)
{
	return md5($string);
}
function MY_RAND()
{
	return 'RANDOM() ';
}

// $db = new SQLite3('include/activcrea.sqlite');
// $db->createFunction('DAYOFWEEK', 'DAYOFWEEK');

// sqlite_create_function($connection, 'DATEDIFF');


// var_dump($db->querySingle('SELECT DAYOFWEEK("2022-03-31 08:00:00")'));


	// sqlite_create_function($connection, 'DATEDIFF');
	// sqlite_create_function($connection, 'NOW');

	// $tesql = SELECT('formations', 'DATEDIFF(NOW(), "2022-01-10")', 'WHERE 1 LIMIT 1', false);
	// while ($test = fetch_array($tesql)) {
	//     print_r($test);
	// }
