<?php

/**
 * @link https://github.com/indicalabs/yii2-geolocation
 * @copyright Copyright (c) 2016 indicalabs
 * @license http://opensource.org/licenses/GPL-3.0 GPL
 */

namespace indicalabs\geolocation;


use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;

class Geolocation extends Component{
 
    /**
     *
     * @author indicalabs
     * @package indicalabs\yii2-geolocation
     */
    
    public $config = ['provider'=>NULL,'return_formats'=>NULL, 'api_key'=>NULL];
        
    private static $plugins         = array();      
    private static $provider        = NULL;       
    private static $return_formats   = NULL;
    private static $api_key         = NULL;


    public function __construct($config = array()) {
                
        self::$plugins = array_diff(scandir((__DIR__).'/plugins/'), array('..', '.'));
        
        $provider = ArrayHelper::getValue($config, 'config.provider');
        if (isset($provider)) {
            if (ArrayHelper::isIn($provider . '.php', self::$plugins)) {
                require (__DIR__) . '/plugins/' . $provider . '.php';
                $format = ArrayHelper::getValue($config, 'config.return_formats');
                if (isset($format)) {
                	if(ArrayHelper::isIn($format, ArrayHelper::getValue($plugin, 'accepted_formats'))){
                        self::$return_formats = $format;
                    } else {
                        self::$return_formats = ArrayHelper::getValue($plugin, 'default_accepted_format');
                    }
                }

                self::$provider = $plugin;
                self::$api_key = ArrayHelper::getValue($config, 'config.api_key', NULL);

            } else {
                throw new HttpException(404, 'The requested Item could not be found.');
            }
        } else {
            require (__DIR__) . '/plugins/geoplugin.php';
            self::$provider = $plugin;
            self::$return_formats = $plugin['default_accepted_format'];
        }

        return parent::__construct($config);
    }
   
    /**
     * Creates the plugin URL
     * 
     * @param strint $ip
     * @return string
     */
    private static function createUrl($ip){
        $urlTmp = preg_replace('!\{\{(accepted_formats)\}\}!', self::$return_formats, self::$provider['plugin_url']);
        $urlTmp = preg_replace('!\{\{(ip)\}\}!', $ip, $urlTmp);
        
        if(isset(self::$api_key))
            $urlTmp = preg_replace('!\{\{(api_key)\}\}!', self::$api_key, $urlTmp);
        
        return $urlTmp;
    }
    
    /**
     * Returns client info
     * 
     * @param string $ip You can supply an IP address or none to use the current client IP address
     * @return mixed
     */
    public static function getInfo($ip=NULL){
    	if(!isset($ip)){
    		$ip = Yii::$app->getRequest()->getUserIp();
    	}
    	//remove the below line in production
		//$ip = '80.2.236.225';
		//the below line is to give public ip - but this link may hack the software.
		//file_get_contents('https://api.ipify.org')
		
        $url = self::createUrl($ip);
        Yii::trace('$url--===>'. \yii\helpers\VarDumper::dumpAsString($url), __METHOD__);
        //print_r($url); exit;
        
        if(self::$return_formats == 'php')
            return unserialize(file_get_contents($url));
        else
            return file_get_contents($url);
    }    
    
    
    /**
     * 
     * Changes the used plugin
     * 
     * @param string $provider The provider plugin name
     * @param string $format The data return format
     */
    public static function getPlugin($provider=NULL, $format=NULL, $api_key=NULL){
        
        self::$plugins = array_diff(scandir((__DIR__).'/plugins/'), array('..', '.'));
        
        if(isset($api_key)){
            self::$api_key = $api_key;
        }
        if (in_array($provider . ".php", self::$plugins)) {
            require (__DIR__) . '/plugins/' . $provider . '.php';
            if(in_array($format, $plugin['accepted_formats'])){
                self::$return_formats = $format;
            } else {
                self::$return_formats = $plugin['default_accepted_format'];
            }

            self::$provider = $plugin;
        }

    }
    
//     private static function getIP(){
//         $ip = getenv('HTTP_CLIENT_IP')?:
//         getenv('HTTP_X_FORWARDED_FOR')?:
//         getenv('HTTP_X_FORWARDED')?:
//         getenv('HTTP_FORWARDED_FOR')?:
//         getenv('HTTP_FORWARDED')?:
//         getenv('REMOTE_ADDR');        
//         return $ip;
//     }

//Yii::trace('$ipppp===>'. \yii\helpers\VarDumper::dumpAsString(Yii::$app->geolocation->getInfo()), __METHOD__);

//213.205.194.147
//80.2.236.225
//order of preference
//freegeoip
//geoplugin
//ipapi
  
/**
 Columns
 -----------------
 plugin
 ip
 city
 region
 country_code
 country_name
 zip_code
 latitude
 longitude
 time_zone
 -----------------
 if(plugin =='geoplugin'){
 plugin =  'geoplugin'
 ip = {geoplugin_request}
 city = {geoplugin_city}
 region = {geoplugin_regionName}
 country_code = {geoplugin_countryCode}
 country_name = {geoplugin_countryName}
 zip_code = {}
 latitude = {geoplugin_latitude}
 longitude = {geoplugin_longitude}
 time_zone = {}
 } else if (plugin = 'freegeoip'){
	 plugin = 'freegeoip'
	 ip = {ip}
	 city = {city}
	 region = {region_name}
	 country_code = {country_code}
	 country_name = {country_name}
	 zip_code = {zip_code}
	 latitude = {latitude}
	 longitude = {longitude}
	 time_zone = {time_zone}
 }else if (plugin = 'ipapi'){
	 plugin = 'ipapi'
	 ip = {query}
	 city = {city}
	 region = {regionName}
	 country_code = {countryCode}
	 country_name = {country}
	 zip_code = {zip}
	 latitude = {lat}
	 longitude = {lon}
	 time_zone = {timezone}
 }
 */

   
//geoplugin
//	[
// 	    'geoplugin_request' => '80.2.236.225'
// 	    'geoplugin_status' => 200
// 	    'geoplugin_credit' => 'Some of the returned data includes GeoLite data created by MaxMind, available from <a href=\\\'http://www.maxmind.com\\\'>http://www.maxmind.com</a>.'
// 	    'geoplugin_city' => 'Lancing'
// 	    'geoplugin_region' => 'West Sussex'
// 	    'geoplugin_areaCode' => '0'
// 	    'geoplugin_dmaCode' => '0'
// 	    'geoplugin_countryCode' => 'GB'
// 	    'geoplugin_countryName' => 'United Kingdom'
// 	    'geoplugin_continentCode' => 'EU'
// 		'geoplugin_latitude' => '50.8303'
// 		'geoplugin_longitude' => '-0.3257'
// 		'geoplugin_regionCode' => 'P6'
// 		'geoplugin_regionName' => 'West Sussex'
// 		'geoplugin_currencyCode' => 'GBP'
// 	    'geoplugin_currencySymbol' => '&#163;'
// 	    'geoplugin_currencySymbol_UTF8' => '£'
// 		'geoplugin_currencyConverter' => '0.7577'
// 	]

//freegeoip    
    //     	{\"ip\":\"80.2.236.225\",
    //     	\"country_code\":\"GB\",
    //     	\"country_name\":\"United Kingdom\",
    //     	\"region_code\":\"ENG\",
    //     	\"region_name\":\"England\",
    //     	\"city\":\"Worthing\",
    //     	\"zip_code\":\"BN11\",
    //     	\"time_zone\":\"Europe/London\",
    //     	\"latitude\":50.8,
    //     	\"longitude\":-0.3668,
    //     	\"metro_code\":0}
//ipapi
//	'{\"as\":\"AS12576 ORANGE-PCS\",
// 		\"city\":\"London\",
// 		\"country\":\"United Kingdom\",
// 		\"countryCode\":\"GB\",
// 		\"isp\":\"EE Mobile\",
// 		\"lat\":51.5142,
// 		\"lon\":-0.0931,
// 		\"org\":\"EE Mobile\",
// 		\"query\":\"213.205.194.147\",
// 		\"region\":\"ENG\",
// 		\"regionName\":\"England\",
// 		\"status\":\"success\",
// 		\"timezone\":\"Europe/London\",
// 		\"zip\":\"EC4N\"}'
 
}

