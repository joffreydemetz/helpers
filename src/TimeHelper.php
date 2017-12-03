<?php
/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace JDZ\Helpers;

use DateTime;

/**
 * Time Helper
 * 
 * @author Joffrey Demetz <joffrey.demetz@gmail.com>
 */
abstract class TimeHelper 
{
  /**
   * Get the current time.
   * 
   * @return   float The current time
   */
  public static function getmicrotime()
  {
    // list ($usec, $sec) = explode(' ', microtime());
    // return ((float) $usec + (float) $sec);
    return microtime(true);
  }
  
  /**
   * Convert second to readable format
   * @param   int   $seconds  Number of seconds
   * @return   string The readable time
   */
  public static function secondsToTime($seconds)
  {
    $dtF = new DateTime('@0');
    $dtT = new DateTime("@$seconds");
    $res = $dtF->diff($dtT);
    
    $days    = $res->format('%a');
    $hours   = $res->format('%h');
    $minutes = $res->format('%i');
    $seconds = $res->format('%s');
    
    $str = [];
    if ( intval($days) > 0 ){
      $str[] = $days.' '.($days===1?'day':'days');
    }
    if ( intval($hours) > 0 ){
      $str[] = $hours.' '.($hours===1?'hour':'hours');
    }
    if ( intval($minutes) > 0 ){
      $str[] = $minutes.' '.($minutes===1?'minute':'minutes');
    }
    if ( intval($seconds) > 0 ){
      $str[] = $seconds.' '.($seconds===1?'second':'seconds');
    }
    
    return implode(' ', $str);
  }
}
