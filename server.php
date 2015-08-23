<?php
/*
*    Online Service Center for Chongqing Jiaotong University 
*    Copyright (C) 2015 freyhsiao@gmail.com
*
*    This program is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License, or
*    (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License along
*    with this program; if not, write to the Free Software Foundation, Inc.,
*    51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA. 	
*/

// Check PHP version.
if(version_compare(PHP_VERSION,'5.5.0','<'))  die('require PHP > 5.5.0 !');

// NOTE: Make sure this file is not accessible when deployed to production
if (!in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    die('You are not allowed to access this file.');
}

// Check install status.
if(!file_exists('./Application/install.lock')){
	header('location: ./Website/install.php');
	exit;	
}

// Define debug mode, applicaton path.
define('APP_DEBUG',true);define('APP_PATH','./Application/');define('BUILD_LITE_FILE',true);

// Execute the application.
require './ThinkPHP/ThinkPHP.php';
