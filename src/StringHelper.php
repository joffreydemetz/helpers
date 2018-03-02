<?php
/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace JDZ\Helpers;

use ErrorException;

/**
 * String Helper
 * 
 * @author Joffrey Demetz <joffrey.demetz@gmail.com>
 */
abstract class StringHelper
{  
  /** 
   * Clean textarea field content with no html
   * 
   * @param   string  $str  Field content
   * @return  string Cleaned string
   */
  public static function cleanText($str)
  {
    $str = str_replace('&nbsp;', ' ', $str);
    $str = strip_tags($str);
    return $str;
  }
  
  /** 
   * Clean textarea field content
   * 
   * @param   string  $str  Field content
   * @return  string Cleaned string
   */
  public static function cleanTextarea($str)
  {
    $str = str_replace('&nbsp;', ' ', $str);
    $str = preg_replace("/[\r\n]{2,}/", "\n\n", $str);
    
    if ( !preg_match("/<p>.+<\/p>/", $str) ){
      $str = '<p>'.$str.'</p>';
    }
    $str = preg_replace("/\n\n/", "</p> <p>", $str);
    $str = preg_replace("/\n/", "<br />", $str);
    $str = preg_replace("/<p><\/p>/", "", $str);
    $str = trim($str);
    // debugMe($str)->end();
    
    return $str;
  }
  
  /** 
   * Clean html field content
   * 
   * use with imperavi redactor field
   * 
   * @param   string  $str  Field content
   * @return   string Cleaned string
   */
  public static function cleanRedactor($str)
  {
    $str = str_replace('&nbsp;', ' ', $str);
    
    $str = preg_replace_callback("/<img ([^>]+)>/", function($m){
      $attrs = AttributesHelper::parse($m[1]);
      if ( isset($attrs['data-src']) ){
        $attrs['src'] = $attrs['data-src'];
        unset($attrs['data-src']);
      }
      return '<img '.AttributesHelper::merge($attrs).' />';
    }, $str);
    
    // $str = preg_replace("/<div class=\"redactor-iframe-clickable\"[^>]*><\/div>/", "", $str);
    $str = preg_replace("/<div class=\"redactor-iframe-clickable\"><\/div>/", "", $str);
    
    // JizyVideo
    $str = preg_replace("/ allowfullscreen=\"(true)?\"/", " allowfullscreen", $str);
    
    return $str;
  }
  
  /**
   * Replace accented characters by the non accented one
   *
   * @param   string  $str  String to process
   * @return   string  The clean string
   */
  public static function removeAccents($str)
  {
    // $str = Callisto()->language->transliterate($str);
    $str = htmlentities($str, ENT_NOQUOTES, 'utf-8');
    $str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
    $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
    $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères
    return $str;
  }
  
  /**
   * Generate a random string
   * 
   * backporting function for PHP >= 5.1
   * 
   * @param   int   $min  Min int
   * @param   int   $max  Max int
   * @return  int   Random number
   */
  public static function randomString()
  {
    if ( version_compare(PHP_VERSION,'7.0.0', '<') ){
      return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        // 16 bits for "time_mid"
        mt_rand(0, 0xffff),
        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand(0, 0x0fff) | 0x4000,
        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand(0, 0x3fff) | 0x8000,
        // 48 bits for "node"
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
      );
    }
    else {
      return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        random_int(0, 0xffff), random_int(0, 0xffff),
        // 16 bits for "time_mid"
        random_int(0, 0xffff),
        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        random_int(0, 0x0fff) | 0x4000,
        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        random_int(0, 0x3fff) | 0x8000,
        // 48 bits for "node"
        random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff)
      );
    }
  }  
  
  /**
   * Split a string in camel case format
   *
   * "FooBarABCDef"            becomes  array("Foo", "Bar", "ABC", "Def");
   * "abcDef"                  becomes  array("abc", "Def");
   * "abc_defGhi_Jkl"          becomes  array("abc_def", "Ghi_Jkl");
   * "ThisIsA_NASAAstronaut"   becomes  array("This", "Is", "A_NASA", "Astronaut")),
   * "JohnFitzgerald_Kennedy"  becomes  array("John", "Fitzgerald_Kennedy")),
   *
   * @param   string  $string  The source string.
   * @return   array   The splitted string.
   */
  public static function splitCamelCase($string)
  {
    return preg_split('/(?<=[^A-Z_])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][^A-Z_])/x', $string);
  }
  
  /**
   * Convert a string into a valid slug.
   *
   * @param   string  $str  The string input.
   * @return   string  The slugged string.
   */
  public static function toSlug($str)
  {
    $str = str_replace('-', ' ', $str);
    $str = str_replace("’", "'", $str);
    $str = self::removeAccents($str);
    $str = preg_replace("/\s+/", " ", $str);
    $str = trim(mb_strtolower($str));
    $str = preg_replace('/(\s|[^A-Za-z0-9\-])+/', '-', $str);
    $str = trim($str, '-');
    return $str;
  }
  
  /**
   * Convert a string into camel case.
   *
   * @param   string  $str  The string input.
   * @return   string  The camel case string.
   */
  public static function toCamelCase($str)
  {
    // Convert words to uppercase and then remove spaces.
    $str = self::toSpaceSeparated($str);
    $str = ucwords($str);
    $str = str_ireplace(' ', '', $str);

    return $str;
  }

  /**
   * Convert a string into dash separated form.
   *
   * @param   string  $str  The string input.
   * @return   string  The dash separated string.
   */
  public static function toDashSeparated($str)
  {
    // Convert spaces and underscores to dashes.
    $str = str_ireplace(array(' ', '_'), '-', $str);

    // Remove duplicate dashes.
    $str = preg_replace('#-+#', '-', $str);

    return $str;
  }

  /**
   * Convert a string into space separated form.
   *
   * @param   string  $str  The string input.
   * @return   string  The space separated string.
   */
  public static function toSpaceSeparated($str)
  {
    // Convert underscores and dashes to spaces.
    $str = str_ireplace(array('_', '-'), ' ', $str);

    // Remove duplicate spaces.
    $str = preg_replace('#\s+#', ' ', $str);

    return $str;
  }

  /**
   * Convert a string into underscore separated form.
   *
   * @param   string  $str  The string input.
   * @return   string  The underscore separated string.
   */
  public static function toUnderscoreSeparated($str)
  {
    // Convert spaces and dashes to underscores.
    $str = str_ireplace(array(' ', '-'), '_', $str);

    // Remove duplicate underscores.
    $str = preg_replace('#_+#', '_', $str);

    return $str;
  }

  /**
   * Convert a string into variable form.
   *
   * @param   string  $str  The string input.
   * @return   string  The variable string.
   */
  public static function toVariable($str)
  {
    // Remove dashes and underscores, then convert to camel case.
    $str = self::toSpaceSeparated($str);
    $str = self::toCamelCase($str);

    // Remove leading digits.
    $str = preg_replace('#^[0-9]+.*$#', '', $str);

    // Lowercase the first character.
    $first = substr($str, 0, 1);
    $first = mb_strtolower($first);

    // Replace the first character with the lowercase character.
    $str = substr_replace($str, $first, 0, 1);

    return $str;
  }

  /**
   * Convert a string into key form.
   *
   * @param   string  $str  The string input.
   * @return   string  The key string.
   */
  public static function toKey($str)
  {
    // Remove spaces and dashes, then convert to lower case.
    $str = self::toUnderscoreSeparated($str);
    $str = mb_strtolower($str);

    return $str;
  }
  
  /**
   * Convertt number to Roman
   *
   * @param   int     $num    Number
   * @return   string  Roman representation
   * @todo    find a class to manage all roman numbers
   */
  public static function toRoman($num)
  {
    $num = (int)$num;
    switch($num){
      case 1:
        $roman = 'I';
        break;
      case 2:
        $roman = 'II';
        break;
      case 3:
        $roman = 'III';
        break;
      case 4:
        $roman = 'IV';
        break;
      case 5:
        $roman = 'V';
        break;
      case 6:
        $roman = 'VI';
        break;
      case 7:
        $roman = 'VII';
        break;
      case 8:
        $roman = 'VIII';
        break;
      case 9:
        $roman = 'IX';
        break;
      case 10:
        $roman = 'X';
        break;
      default:
        $roman = (string)$num;
        break;
    }
    
    return $roman;
  }

  
  /**
   * On/Off boolean to string
   *
   * @param   mixed   $val    Value
   * @return   string 
   */
  public static function boolean($val)
  {
    if ( $val ){
      return 'ON';
    }
    
    return 'OFF';
  }
  
  /**
   * Yes/No boolean to string
   *
   * @param   mixed   $val    Value
   * @return   string 
   */
  public static function yesno($val)
  {
    if ( $val ){
      return 'YES';
    }
    
    return 'NO';
  }

  /**
   * Value to string
   * 
   * Returns NONE if the value is empty
   *
   * @param   mixed   $val    Value
   * @return   string 
   */
  public static function string($val)
  {
    if ( empty($val) ){
      return 'NONE';
    } 

    return htmlspecialchars($val, ENT_QUOTES);
  }

  /**
   * String to integer
   *
   * @param   mixed   $val    Value
   * @return   int 
   */
  public static function integer($val)
  {
    return intval($val);
  }

  /**
   * Array to string
   *
   * @param   array   $val        One dimension array
   * @param   string  $separator  Implode separator
   * @return   string   
   */
  public static function array2string(array $val, $separator=', ')
  {
    return implode($separator, $val);
  }
  
  /**
   * Replaces &amp; with & for XHTML compliance
   *
   * @param   string  $text  Text to process
   * @return   string  Processed string.
   * @todo There must be a better way???
   */
  public static function ampReplace($text)
  {
    $text = str_replace('&&', '*--*', $text);
    $text = str_replace('&#', '*-*', $text);
    $text = str_replace('&amp;', '&', $text);
    $text = preg_replace('|&(?![\w]+;)|', '&amp;', $text);
    $text = str_replace('*-*', '&#', $text);
    $text = str_replace('*--*', '&&', $text);

    return $text;
  }
  
  /* NEED TO CLEAN THIS UP */
  
  /**
   * Increment styles.
   *
   * @var    array
   */
  protected static $incrementStyles = [
    'dash' => [
      '#-(\d+)$#',
      '-%d'
    ],
    'default' => [
      ['#\((\d+)\)$#', '#\(\d+\)$#'],
      [' (%d)', '(%d)'],
    ],
  ];
  
  /**
   * Increments a trailing number in a string.
   *
   * Used to easily create distinct labels when copying objects. The method has the following styles:
   *
   * default: "Label" becomes "Label (2)"
   * dash:    "Label" becomes "Label-2"
   *
   * @param   string   $string  The source string.
   * @param   string   $style   The the style (default|dash).
   * @param   integer  $n       If supplied, this number is used for the copy, otherwise it is the 'next' number.
   * @return   string  The incremented string.
   */
  public static function increment($string, $style = 'default', $n = 0)
  {
    $styleSpec = isset(self::$incrementStyles[$style]) ? self::$incrementStyles[$style] : self::$incrementStyles['default'];

    // Regular expression search and replace patterns.
    if (is_array($styleSpec[0]))
    {
      $rxSearch = $styleSpec[0][0];
      $rxReplace = $styleSpec[0][1];
    }
    else
    {
      $rxSearch = $rxReplace = $styleSpec[0];
    }

    // New and old (existing) sprintf formats.
    if (is_array($styleSpec[1]))
    {
      $newFormat = $styleSpec[1][0];
      $oldFormat = $styleSpec[1][1];
    }
    else
    {
      $newFormat = $oldFormat = $styleSpec[1];
    }

    // Check if we are incrementing an existing pattern, or appending a new one.
    if (preg_match($rxSearch, $string, $matches))
    {
      $n = empty($n) ? ($matches[1] + 1) : $n;
      $string = preg_replace($rxReplace, sprintf($oldFormat, $n), $string);
    }
    else
    {
      $n = empty($n) ? 2 : $n;
      $string .= sprintf($newFormat, $n);
    }

    return $string;
  }
  
  
  /**
   * Transcode a string.
   *
   * @param   string  $source         The string to transcode.
   * @param   string  $from_encoding  The source encoding.
   * @param   string  $to_encoding    The target encoding.
   * @return   mixed  The transcoded string, or null if the source was not a string.
   *
   * @link    https://bugs.php.net/bug.php?id=48147
   */
  public static function transcode($source, $from_encoding, $to_encoding)
  {
    if (is_string($source))
    {
      set_error_handler(array(__CLASS__, '_iconvErrorHandler'), E_NOTICE);
      try
      {
        /*
         * "//TRANSLIT//IGNORE" is appended to the $to_encoding to ensure that when iconv comes
         * across a character that cannot be represented in the target charset, it can
         * be approximated through one or several similarly looking characters or ignored.
         */
        $iconv = iconv($from_encoding, $to_encoding . '//TRANSLIT//IGNORE', $source);
      }
      catch (ErrorException $e)
      {
        /*
         * "//IGNORE" is appended to the $to_encoding to ensure that when iconv comes
         * across a character that cannot be represented in the target charset, it is ignored.
         */
        $iconv = iconv($from_encoding, $to_encoding . '//IGNORE', $source);
      }
      restore_error_handler();
      return $iconv;
    }

    return null;
  }

  /**
   * Tests a string as to whether it's valid UTF-8 and supported by the Unicode standard.
   *
   * Note: this function has been modified to simple return true or false.
   *
   * @param   string  $str  UTF-8 encoded string.
   * @return   boolean  true if valid
   *
   * @author  <hsivonen@iki.fi>
   * @see     http://hsivonen.iki.fi/php-utf8/
   * @see     compliant
   */
  public static function valid($str)
  {
    // Cached expected number of octets after the current octet
    // until the beginning of the next UTF8 character sequence
    $mState = 0;

    // Cached Unicode character
    $mUcs4 = 0;

    // Cached expected number of octets in the current sequence
    $mBytes = 1;

    $len = strlen($str);

    for ($i = 0; $i < $len; $i++)
    {
      $in = ord($str{$i});

      if ($mState == 0)
      {
        // When mState is zero we expect either a US-ASCII character or a
        // multi-octet sequence.
        if (0 == (0x80 & ($in)))
        {
          // US-ASCII, pass straight through.
          $mBytes = 1;
        }
        elseif (0xC0 == (0xE0 & ($in)))
        {
          // First octet of 2 octet sequence
          $mUcs4 = ($in);
          $mUcs4 = ($mUcs4 & 0x1F) << 6;
          $mState = 1;
          $mBytes = 2;
        }
        elseif (0xE0 == (0xF0 & ($in)))
        {
          // First octet of 3 octet sequence
          $mUcs4 = ($in);
          $mUcs4 = ($mUcs4 & 0x0F) << 12;
          $mState = 2;
          $mBytes = 3;
        }
        elseif (0xF0 == (0xF8 & ($in)))
        {
          // First octet of 4 octet sequence
          $mUcs4 = ($in);
          $mUcs4 = ($mUcs4 & 0x07) << 18;
          $mState = 3;
          $mBytes = 4;
        }
        elseif (0xF8 == (0xFC & ($in)))
        {
          /* First octet of 5 octet sequence.
           *
           * This is illegal because the encoded codepoint must be either
           * (a) not the shortest form or
           * (b) outside the Unicode range of 0-0x10FFFF.
           * Rather than trying to resynchronize, we will carry on until the end
           * of the sequence and let the later error handling code catch it.
           */
          $mUcs4 = ($in);
          $mUcs4 = ($mUcs4 & 0x03) << 24;
          $mState = 4;
          $mBytes = 5;
        }
        elseif (0xFC == (0xFE & ($in)))
        {
          // First octet of 6 octet sequence, see comments for 5 octet sequence.
          $mUcs4 = ($in);
          $mUcs4 = ($mUcs4 & 1) << 30;
          $mState = 5;
          $mBytes = 6;

        }
        else
        {
          /* Current octet is neither in the US-ASCII range nor a legal first
           * octet of a multi-octet sequence.
           */
          return false;
        }
      }
      else
      {
        // When mState is non-zero, we expect a continuation of the multi-octet
        // sequence
        if (0x80 == (0xC0 & ($in)))
        {
          // Legal continuation.
          $shift = ($mState - 1) * 6;
          $tmp = $in;
          $tmp = ($tmp & 0x0000003F) << $shift;
          $mUcs4 |= $tmp;

          /**
           * End of the multi-octet sequence. mUcs4 now contains the final
           * Unicode codepoint to be output
           */
          if (0 == --$mState)
          {
            /*
             * Check for illegal sequences and codepoints.
             */
            // From Unicode 3.1, non-shortest form is illegal
            if (((2 == $mBytes) && ($mUcs4 < 0x0080)) || ((3 == $mBytes) && ($mUcs4 < 0x0800)) || ((4 == $mBytes) && ($mUcs4 < 0x10000))
              || (4 < $mBytes)
              || (($mUcs4 & 0xFFFFF800) == 0xD800) // From Unicode 3.2, surrogate characters are illegal
              || ($mUcs4 > 0x10FFFF)) // Codepoints outside the Unicode range are illegal
            {
              return false;
            }

            // Initialize UTF8 cache.
            $mState = 0;
            $mUcs4 = 0;
            $mBytes = 1;
          }
        }
        else
        {
          /**
           *((0xC0 & (*in) != 0x80) && (mState != 0))
           * Incomplete multi-octet sequence.
           */
          return false;
        }
      }
    }
    return true;
  }

  /**
   * Tests whether a string complies as UTF-8. This will be much
   * faster than utf8_is_valid but will pass five and six octet
   * UTF-8 sequences, which are not supported by Unicode and
   * so cannot be displayed correctly in a browser. In other words
   * it is not as strict as utf8_is_valid but it's faster. If you use
   * it to validate user input, you place yourself at the risk that
   * attackers will be able to inject 5 and 6 byte sequences (which
   * may or may not be a significant risk, depending on what you are
   * are doing)
   *
   * @param   string  $str  UTF-8 string to check
   * @return   boolean  TRUE if string is valid UTF-8
   * @see     valid
   * @see     http://www.php.net/manual/en/reference.pcre.pattern.modifiers.php#54805
   */
  public static function compliant($str)
  {
    if (strlen($str) == 0)
    {
      return true;
    }
    // If even just the first character can be matched, when the /u
    // modifier is used, then it's valid UTF-8. If the UTF-8 is somehow
    // invalid, nothing at all will match, even if the string contains
    // some valid sequences
    return (preg_match('/^.{1}/us', $str, $ar) == 1);
  }
  
  /**
   * Catch an error and throw an exception.
   *
   * @param   integer  $number   Error level
   * @param   string   $message  Error message
   * @return   void
   *
   * @link    https://bugs.php.net/bug.php?id=48147
   *
   * @throw   ErrorException
   */
  private static function _iconvErrorHandler($number, $message)
  {
    throw new ErrorException($message, 0, $number);
  }
}