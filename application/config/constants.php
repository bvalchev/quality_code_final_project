<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE')  OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS')        OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code


/* -------------------------------------------------------------------------------------------------------
| Custom defined constants for the application
*/

defined('SETTINGS_TABLE_NAME') OR define ('SETTINGS_TABLE_NAME', 'settings');
defined('BLOG_TABLE_NAME') OR define ('BLOG_TABLE_NAME', 'blog');
defined('USER_TABLE_NAME') OR define ('USER_TABLE_NAME', 'user_login');
defined('ALLOWED_FILE_TYPES') OR define ('ALLOWED_FILE_TYPES', 'gif|jpg|png|jpeg');
defined('PICTURES_UPLOAD_LOCATION') OR define ('PICTURES_UPLOAD_LOCATION', '../uploads/blogPics/');
defined('HOTEL_INFO_ENDPOINT') OR define ('HOTEL_INFO_ENDPOINT', 'http://json.peakview.bg/b2b_programa_hotel.php?');
defined('AUTHENTICATION_KEY') OR define ('AUTHENTICATION_KEY', '&us=e35232bd48c2e3e80eee63ebb0aee9a7o40Qjze9Ri&ps=JBPmBtdkFZxPW72e35232bd48c2e3e80eee63ebb0aee9a7');
defined('DEFAULT_UPLOAD_FILE_NAME') OR define ('DEFAULT_UPLOAD_FILE_NAME', 'file');

defined('USE_SESSION') OR define ('USE_SESSION', false);
defined('BAD_REQUEST_ERROR_CODE') OR define ('BAD_REQUEST_ERROR_CODE', 403);
defined('UNSUCCESSFUL_REQUEST_ERROR_CODE') OR define ('UNSUCCESSFUL_REQUEST_ERROR_CODE', 404);
defined('SUCCESSFUL_REQUEST_ERROR_CODE') OR define ('SUCCESSFUL_REQUEST_ERROR_CODE', 200);
defined('DEFAULT_GET_REQUEST_OFFSET') OR define ('DEFAULT_GET_REQUEST_OFFSET', 0);
defined('DEFAULT_GET_REQUEST_LIMIT') OR define ('DEFAULT_GET_REQUEST_LIMIT', 50);
defined('DEFAULT_PID') OR define ('DEFAULT_PID', -1);


defined('ERROR_OCCURRED_MESSAGE') OR define ('ERROR_OCCURRED_MESSAGE', 'Error ocurred');
defined('METHOD_CHECK_SUFFIX_MESSAGE') OR define ('METHOD_CHECK_SUFFIX_MESSAGE', ' method should be used');
defined('NOT_JSON_ERROR') OR define ('NOT_JSON_ERROR', 'Data must be in json format');
defined('PROPERTY_NOT_SET_SUFFIX_MESSAGE') OR define ('PROPERTY_NOT_SET_SUFFIX_MESSAGE', ' is not provided or is incorrect');
defined('INSERT_FAILED_MESSAGE') OR define ('INSERT_FAILED_MESSAGE', 'Insert operation failed');
defined('INSERT_SUCCESSFUL_MESSAGE') OR define ('INSERT_SUCCESSFUL_MESSAGE', 'Insert operation successful');
defined('UPDATE_SUCCESSFUL_MESSAGE') OR define ('UPDATE_SUCCESSFUL_MESSAGE', 'Update operation successful');
defined('DELETE_SUCCESSFUL_MESSAGE') OR define ('DELETE_SUCCESSFUL_MESSAGE', 'Delete operation successful');
defined('UPDATE_FAILED_MESSAGE') OR define ('UPDATE_FAILED_MESSAGE', 'Update operation failed');
defined('DELETE_FAILED_MESSAGE') OR define ('DELETE_FAILED_MESSAGE', 'Delete operation failed');
defined('EMAIL_NOT_VALID_MESSAGE') OR define ('EMAIL_NOT_VALID_MESSAGE', 'Email is not valid!');
defined('FILE_UPLOAD_ERROR_MESSAGE') OR define ('FILE_UPLOAD_ERROR_MESSAGE', 'File upload failed');
defined('SESSION_NOT_SET_MESSAGE') OR define ('SESSION_NOT_SET_MESSAGE', 'Session not set');
defined('PID_NOT_PROVIDED_MESSAGE') OR define ('PID_NOT_PROVIDED_MESSAGE', 'Pid not provided');
defined('HOTELID_NOT_PROVIDED_MESSAGE') OR define ('HOTELID_NOT_PROVIDED_MESSAGE', 'Hotel ID is not provided');
defined('USER_NOT_FOUND_MESSAGE') OR define ('USER_NOT_FOUND_MESSAGE', 'User not found');
defined('INVALID_PASSWORD_MESSAGE') OR define ('INVALID_PASSWORD_MESSAGE', 'Invalid password');
defined('VALIDATION_ERROR_MESSAGE') OR define ('VALIDATION_ERROR_MESSAGE', 'Validation of input failed');
defined('USERNAME_TAKEN_MESSAGE') OR define ('USERNAME_TAKEN_MESSAGE', 'Username taken');
defined('USER_NOT_FOUND') OR define ('USER_NOT_FOUND', 'User not found');
defined('SESSION_EXPIRED_MESSAGE') OR define ('SESSION_EXPIRED_MESSAGE', 'Session expired');



defined('XML_SAVE_PATH') OR define ('XML_SAVE_PATH', '/file/path/name.xml');



