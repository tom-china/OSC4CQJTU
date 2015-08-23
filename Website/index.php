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

// Check install status.
if(!file_exists('../Application/install.lock')){
	header('location: ./install.php');
	exit;	
}

// Check the runtime lite file status.
if(!file_exists('../Application/Runtime/lite.php')){
	
// Define debug mode, applicaton path and others.
define('APP_DEBUG',false);define('APP_PATH','../Application/');define('BUILD_LITE_FILE',true);
define('DIR_SECURE_FILENAME', 'index.html,index.htm');define('DIR_SECURE_CONTENT', 'deny Access!');

// Execute the application.
require '../ThinkPHP/ThinkPHP.php';

}else{
	
// Execute the runtime lite file.
require '../Application/Runtime/lite.php';
	
}

