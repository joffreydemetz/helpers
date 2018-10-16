<?php
/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Call String Helper method
 * 
 * Accepts a variable number of arguments. See every helper methods for details.
 * 
 * @param  string  $method  Method name
 * @return string
 */
function Str($method)
{
  $args   = func_get_args();
  $method = array_shift($args);
  
  $methodCall = [ '\\JDZ\\Helpers\\StringHelper', $method ];
  
  if( !is_callable($methodCall) ){
    throw new \RuntimeException(implode('::', $methodCall).' is not callable');
  }
  
  $str = call_user_func_array($methodCall, $args);
  
  if ( function_exists('i18n') && in_array($method, ['yesno', 'boolean', 'string', 'array2string']) && in_array($str, ['YES','NO','ON','OFF','NONE']) ){
    $str = i18n($str);
  }
  
  return $str;
}
