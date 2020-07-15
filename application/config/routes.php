<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'Welcome';
$route['user/register'] = 'Authentication_Controller/insertUser';
$route['password/update'] = 'Authentication_Controller/updatePassword';
$route['user/delete'] = 'Authentication_Controller/deleteUser';
$route['login'] = 'Authentication_Controller/login';
$route['logout'] = 'Authentication_Controller/logout';
$route['session'] = 'Authentication_Controller/getSessionDetails';
$route['offers/details'] = 'Offers_Controller/getDetailsForOffer';
$route['offers/countries'] = 'Offers_Controller/getDistinctOfferCountries';
$route['offers'] = 'Offers_Controller/getOffers';
$route['picture/upload'] = 'Blog_Controller/uploadFile';
$route['picture/optimize'] = 'Blog_Controller/executeScheduledPicUpdate';
$route['posts'] = 'Blog_Controller/index';
$route['hotels'] = 'Offers_Controller/getHotelInfo';
$route['email'] = 'Email_Controller/sendEmail';
$route['offerStatic'] = 'Offers_Controller/getStaticOfferView';
$route['blogStatic'] = 'Blog_Controller/getStaticBlogView';
$route['404_override'] = 'Error_Controller';
$route['translate_uri_dashes'] = FALSE;
