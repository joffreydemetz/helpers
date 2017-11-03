<?php
/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace JDZ\Helpers;

/**
 * Attributes helper
 * 
 * @author Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class AttributesHelper
{
	/**
	 * Parse html tag attributes
	 *
	 * @param 	string   $string  The tag attributes
	 * @return 	array    Key/Value pairs
	 */
	public static function parse($string)
	{
		$attr = [];
		$list = [];

    preg_match_all('/([\w:-]+)[\s]?=[\s]?"([^"]*)"/i', $string, $attr);

		if ( is_array($attr) ){
			$numPairs = count($attr[1]);
			for ($i = 0; $i < $numPairs; $i++){
				$list[$attr[1][$i]] = $attr[2][$i];
			}
		}
    
		return $list;
	}
  
	/**
	 * Merge html tag attributes to string
	 *
	 * @param 	array    $attrs  Key/Value pairs
	 * @return 	string   The tag attributes as a string
	 */
  public static function merge(array $attrs=[])
  {
    $attrs = (array)$attrs;
    
    $attributes=[];
    foreach($attrs as $key => $value){
      if ( $key === 'class' && is_array($value) ){
        $value = array_unique($value);
        $value = implode(' ', $value);
        if ( empty($value) ){
          continue;
        }
      }
      
      $attributes[] = $key.'="'.str_replace('"', '\"', trim($value)).'"';
    }
    
    $attrs = implode(' ', $attributes);
    if ( $attrs !== '' ){
      return ' '.$attrs;
    }
    return '';
  }
}
