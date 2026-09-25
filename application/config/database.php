<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the 'Database Connection'
| page of the User Guide.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['hostname'] The hostname of your database server.
|	['username'] The username used to connect to the database
|	['password'] The password used to connect to the database
|	['database'] The name of the database you want to connect to
|	['dbdriver'] The database type. ie: mysql.  Currently supported:
				 mysql, mysqli, postgre, odbc, mssql, sqlite, oci8
|	['dbprefix'] You can add an optional prefix, which will be added
|				 to the table name when using the  Active Record class
|	['pconnect'] TRUE/FALSE - Whether to use a persistent connection
|	['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
|	['cache_on'] TRUE/FALSE - Enables/disables query caching
|	['cachedir'] The path to the folder where cache files should be stored
|	['char_set'] The character set used in communicating with the database
|	['dbcollat'] The character collation used in communicating with the database
|				 NOTE: For MySQL and MySQLi databases, this setting is only used
| 				 as a backup if your server is running PHP < 5.2.3 or MySQL < 5.0.7.
| 				 There is an incompatibility in PHP with mysql_real_escape_string() which
| 				 can make your site vulnerable to SQL injection if you are using a
| 				 multi-byte character set and are running versions lower than these.
| 				 Sites using Latin-1 or UTF-8 database character set and collation are unaffected.
|	['swap_pre'] A default table prefix that should be swapped with the dbprefix
|	['autoinit'] Whether or not to automatically initialize the database.
|	['stricton'] TRUE/FALSE - forces 'Strict Mode' connections
|							- good for ensuring strict SQL while developing
|
| The $active_group variable lets you choose which connection group to
| make active.  By default there is only one group (the 'default' group).
|
| The $active_record variables lets you determine whether or not to load
| the active record class
*/
$active_group = DB1;
$active_record = FALSE;

$db[DB1]['hostname'] = 'mysql:host=127.0.0.1';
$db[DB1]['username'] = 'coreadm';
$db[DB1]['password'] = 'coreadm';
$db[DB1]['database'] = 'coreapp_fusion';
$db[DB1]['dbdriver'] = 'pdo';
$db[DB1]['dbprefix'] = '';
$db[DB1]['pconnect'] = TRUE;
$db[DB1]['db_debug'] = TRUE;
$db[DB1]['cache_on'] = FALSE;
$db[DB1]['cachedir'] = 'application/cache';
$db[DB1]['char_set'] = 'utf8';
$db[DB1]['dbcollat'] = 'utf8_general_ci';
$db[DB1]['swap_pre'] = '';
$db[DB1]['autoinit'] = TRUE;
$db[DB1]['stricton'] = FALSE;

$db[DB2]['hostname'] = 'mysql:host=127.0.0.1';
$db[DB2]['username'] = 'coreadm';
$db[DB2]['password'] = 'coreadm';
$db[DB2]['database'] = 'coresys_fusion';
$db[DB2]['dbdriver'] = 'pdo';
$db[DB2]['dbprefix'] = '';
$db[DB2]['pconnect'] = TRUE;
$db[DB2]['db_debug'] = TRUE;
$db[DB2]['cache_on'] = FALSE;
$db[DB2]['cachedir'] = 'application/cache';
$db[DB2]['char_set'] = 'utf8';
$db[DB2]['dbcollat'] = 'utf8_general_ci';
$db[DB2]['swap_pre'] = '';
$db[DB2]['autoinit'] = TRUE;
$db[DB2]['stricton'] = FALSE;

/* End of file database.php */
/* Location: ./application/config/database.php */
