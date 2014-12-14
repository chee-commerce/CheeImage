<?php namespace Chee\Image;

use Illuminate\Foundation\Application;

/**
* Image for manage images
* @author Chee   
*/
class CheeImage
{
  /**
  * the quality for image
  * @var int
  */
  protected $quality;

  /**
  * the source of image
  * @var 
  */
  protected $sourceImage;

  /**
  * the copy of image for edit
  * @var 
  */

  /**
  * the location of source image
  * @var string
  */
  protected $imageLocation ;

  /**
  * the image directory
  * @var string
  */
  protected $imageDirectory;

  /**
  * the image name
  * @var string
  */
  protected $imageName;

  /**
  * the image extenstion
  * @var string
  */
  protected $imageExtension;

  /**
  * image width
  * $var int
  */
  protected $width;

  /**
  * image height
  * $var int
  */
  protected $height;

  /**
  * operation for do!
  * @var string
  */
  protected $operationBy = 'GD';

  protected $errors = array();

  public function __construct($image)
  {
    // check image
    if(!self::isRealImage($image))  
    {
      $this->pushErrors('this file not a image!');
      return false;
    }

    $this->imageLocation = $image;
    $pathInfo = pathinfo($image);
    $this->imageDirectory = $pathInfo['dirname'];
    $this->imageName = $pathInfo['filename'];
    $this->imageExtension = $pathInfo['extension'];
    list($this->width, $this->height) = getimagesize($image);

    if(self::checkForGD())
    {
      $this->operationBy = 'GD';
      //create new image from source image 
      $this->sourceImage = self::createNewImageFromFile();
    }   
  }
  /**
  * set image quality.
  *
  * @param int $quality 
  * @return this
  */
  public function quality($quality)
  {
    if(!is_numeric($quality) || $quality<0 || $quality>100)
    {
      $this->pushErrors('Quality should be in the range 0 to 100!');
      return $this;
    }
    $this->quality = (int) $quality;
    return $this;
  }

  /**
  * change image quality
  *
  *
  *
  */
  public function changeQuality($quality)
  {

  }

  /**
  * crop image
  *
  *@param int $src_x
  *@param int $src_y
  *@param int $dst_x
  *@param int $dst_y
  *@return 
  */
  public function crop($src_x = 0, $src_y = 0, $dst_x = 0 , $dst_y = 0)
  {
    if($this->operationBy == 'GD')
    {
      list($width, $height) = getimagesize($this->imageLocation);
      imagecopyresampled(
          $this->sourceImage ,
          $this->sourceImage ,
          (int) $dst_x ,
          (int) $dst_y ,
          (int) $src_x ,
          (int) $src_y ,
          $this->getWidth(),  // destination width
          $this->getHeight(),  // destination height
          $this->getWidth() , // the width of the image without the black
          $this->getHeight()   // the height of the image without the black
      );
    }
    return $this;
  }

  /**
  * checking for imagick installed in server
  *
  * @return bool
  */
  private function checkForImagick()
  {
    if(extension_loaded('imagick'))
      return true;
    return false;
  }

  /**
  * get mime type
  * 
  * @return string
  */
  public function getMimeType()
  {
    return getimagesize($this->imageLocation)['mime'];
  }

  /**
  * get image width
  * 
  * @return int
  */
  public function getWidth()
  {
    return $this->width;
  }

  /**
  * get image height
  * 
  * @return int
  */
  public function getHeight()
  {
    return $this->height;
  }

  public function getMessage()
  {
    return (array) $this->errors;
  }

  /**
  * checking for GD installed in server
  *
  * @return bool
  */
  private function checkForGD()
  {
    if(extension_loaded('gd'))
      return true;
    return false;
  }

  private function pushErrors($error)
  {
    array_push($this->errors, (string) $error);
  }

  /**
  * check image mime type
  * 
  * @param string $file the file address
  * @param array $mimeTypes the accepted mime types
  * @return bool
  */
  public static function isRealImage($file, $mimeTypes = null)
  {
    if(!file_exists((string) $file))
      exit('file not exists');

    if(is_null($mimeTypes))
      $mimeTypes = array('image/png', 'image/jpeg', 'image/gif', 'image/bmp');
    elseif (!is_array($mimeTypes)) 
      $mimeTypes = (array) $mimeTypes;

    $mimeType = mime_content_type($file);
    if(in_array($mimeType, $mimeTypes))
      return true;

    return false;
  }

  /**
  * rotate image
  * 
  * @param int $degree
  * @param string $RGB the color for background ex : '255,255,255' or '#fff'
  * @return 
  */
  public function rotate($degree, $RGB = '#ffffff')
  {
    if($this->operationBy == 'GD')
    {
      if(!self::checkColor($RGB))
      {
        $color = array(0, 0, 0);
        $this->pushErrors('The color code is invalid');
      }
      else
      {
        //if code like this : #fff
        if(substr(trim($RGB), 0, 1) == '#')
          $color = self::HexToRGB($RGB);
        else
        {
          $color = explode(',', $RGB);
        }
      }
      $color = explode(',', $RGB);
      //check hex code
      if(count($color) == 1) 
        $color = self::HexToRGB($RGB);
      elseif(count($color)<3)
      {
        $this->pushErrors('the color for rotate method is invalid!');
        return $this;
      }
      $color = array_slice($color, 0, 3); 

      $freezone = imagecolorallocate($this->sourceImage, (int)$color[0], (int)$color[1], (int)$color[2]);
      if($tmp = imagerotate($this->sourceImage, (float) $degree, $freezone))
        $this->sourceImage = $tmp;
      else
      {
        $this->pushErrors('error with image rotate');
      }
      return $this; 
    }
    
  }

  /**
  * resize image
  *
  *@param int $newWidth 
  *@param int newHeight
  *@return $this
  */
  public function resize($newWidth, $newHeight)
  {
    $newWidth = abs((int) $newWidth);
    $newHeight = abs((int) $newHeight);
    $thumb = imagecreatetruecolor($newWidth, $newHeight);
    if(imagecopyresized($thumb, $this->sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $this->getWidth(), $this->getHeight()))
    {
      $this->sourceImage = $thumb;
      $this->width = $newWidth;
      $this->height = $newHeight;
    }
    else
      $this->pushErrors('Image resize has been faild!');
    return $this;

  } 

  public function convert($toType)
  {
    $thumb = imagecreatetruecolor($this->getWidth(), $this->getHeight());
    if(imagecopyresampled($thumb, $this->sourceImage, 0, 0, 0, 0, $this->getWidth(), $this->getHeight(), $this->getWidth(), $this->getHeight()))
    {
      $this->sourceImage = $thumb;
      $this->imageExtension = (string) $toType;
    }
    else
      $this->pushErrors('convert image has been faild!');
    return $this;
  }

  public function save($dest = null)
  {
    if(!is_null($dest))
      $this->imageLocation = (string) $dest;
    else
    {
      $this->imageLocation = $this->imageName . '.' . $this->imageExtension;
    }
    if($this->operationBy == 'GD')
    {
      switch ($this->imageExtension) 
      {
        case 'jpg' :
        case 'jpeg' :
          imagejpeg($this->sourceImage, $this->imageLocation, (int) $this->quality);
        break;
        case 'gif':
          imagegif($this->sourceImage, $this->imageLocation, (int) $this->quality);
        break;
        case 'png':
        {
          //the quality in png format must be 0-9
          $compressionLevel = null;
          if($this->quality == 0)
            $compressionLevel = 9;
          elseif(!is_null($this->quality))
            $compressionLevel = ($this->quality * 9 / 100);
          imagepng($this->sourceImage, $this->imageLocation, $compressionLevel);
        }
        break;
        default :
          exit('GD only support jpeg, png and gif');
      }
    }
  }

  private function createNewImageFromFile()
  {
    try 
    {
      $mime = self::getMimeType();
      switch($mime) 
      {
        case 'image/jpeg':
        case 'image/jpg':
          return imagecreatefromjpeg($this->imageLocation);
        break;
        case 'image/png':
          return imagecreatefrompng($this->imageLocation);
        break;
        case 'image/gif':
          return imagecreatefromgif($this->imageLocation);
        break;
        default:
          throw new ErrorException('GD only support jpeg, png and gif');
        break;
      }
    } 
    catch(ErrorException $e) 
    {
      throw new ErrorException($e -> getMessage());
    }
  }

  /**
  * convert and parse hex color code
  *
  * @param string $hex the color code ex : #2521532
  * @return array
  */
  public static function HexToRGB($hex) 
  {
    if(substr($hex, 0, 1) != '#')
      $hex = '#'.$hex;
    $hex = ereg_replace('#', '', $hex);
    $color = array();
 
    if(strlen($hex) == 3) {
      $color[] = hexdec(substr($hex, 0, 1) . $r);
      $color[] = hexdec(substr($hex, 1, 1) . $g);
      $color[] = hexdec(substr($hex, 2, 1) . $b);
    }
    else if(strlen($hex) == 6) {
      $color[] = hexdec(substr($hex, 0, 2));
      $color[] = hexdec(substr($hex, 2, 2));
      $color[] = hexdec(substr($hex, 4, 2));
    }
 
    return $color;
  }

  /**
  * convert RGB to Hex color code
  *
  * @param string $code rgb color code  ex : '255,255,255'
  * @return string
  */
  public static function RGBToHex($code) 
  {
    $color = explode(',', $code);
    //String padding bug found and the solution put forth by Pete Williams (http://snipplr.com/users/PeteW)
    $hex = "#";
    $hex.= str_pad(dechex($color[0]), 2, "0", STR_PAD_LEFT);
    $hex.= str_pad(dechex($color[1]), 2, "0", STR_PAD_LEFT);
    $hex.= str_pad(dechex($color[2]), 2, "0", STR_PAD_LEFT);
 
    return $hex;
  }

  public static function checkColor($string)
  {
    $pattern = '/^(#[A-Fa-f0-9]{6}|#[A-Fa-f0-9]{3}|([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5]),([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5]),([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5]))$/';
    if(preg_match($pattern, $string))
      return true;
    return false;
  }
}
