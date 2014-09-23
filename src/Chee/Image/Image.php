<?php namespace Chee\Image;

use Illuminate\Foundation\Application;

/**
* Image for manage images
* @author Chee   
*/
class Image
{
	/**
	* IoC
	* @var Illuminate\doundination\Application
	*/
	protected $app;
    
    /**
    * Initialize class
    */
    public function __construct(Application $app)
    {
        $this->app = $app;    
    }
    public static function resize($srcFile, $dstt, $width = null, $height = null, $fileType = 'jpg', $force_type = false, &$error = 0)
    {
        
    }
    
    public static function renameFile()
    {
        
    }
    
    public static function cut()
    {
        
    }
    
    public static function deleteFile()
    {
        
    }
    
    /**
   * @param string    $source == 'uploads/image.png'
   * @param string    $dest == 'files/product_id/45/'
   * @param string    $type => [products | categories | manufacturers | suppliers]
   *
   * @return array 
   *
   * @throws ErrorException
   */  
  public function generateImages($source, $dest, $type, $postfix = "", $image_size_name = null) 
  {
    $img_sizes = array();
    $image_sizes = self::getAllImageSizes($image_size_name);
    foreach($image_sizes as $image_size) {
      if($image_size -> image_size_usage[$type]) 
      {
        $mime = getimagesize($source)['mime'];
        try {
          switch($mime) {
            case 'image/jpeg':
            case 'image/jpg':
              $img = imagecreatefromjpeg($source);
              break;
            case 'image/png':
              $img = imagecreatefrompng($source);
              break;
            case 'image/gif':
              $img = imagecreatefromgif($source);
              break;
            default:
              throw new ErrorException('GD only support jpeg, png and gif');
              break;
          }
        } catch(ErrorException $e) 
        {
          throw new ErrorException($e -> getMessage());
        }
        list($width, $height) = getimagesize($source);
        $img_path_parts = pathinfo($source);
        $thumb = imagecreatetruecolor($image_size -> image_size_width, $image_size -> image_size_height);
        imagecopyresized($thumb, $img, 0, 0, 0, 0, $image_size -> image_size_width, $image_size -> image_size_height, $width, $height);
        try {
          switch($mime) {
            case 'image/jpeg':
            case 'image/jpg':
              $file = $dest . '/' . $image_size -> image_size_name . '_' . $postfix . '.' . $img_path_parts['extension'];
              if(File::exists($file)) {
                  File::delete($file);
              }
              imagejpeg($thumb, $file, $image_size -> image_size_quality);
              break;
            case 'image/png':
              $file = $dest . '/' . $image_size -> image_size_name . '_' . $postfix . '.' . $img_path_parts['extension'];
              if(File::exists($file)) {
                  File::delete($file);
              }
              imagepng($thumb, $file, $image_size -> image_size_quality / 100);
              break;
            case 'image/gif':
              $file = $dest . '/' . $image_size -> image_size_name . '_' . $postfix . '.' . $img_path_parts['extension'];
              if(File::exists($file)) {
                  File::delete($file);
              }
              imagegif($thumb, $file);
              break;
            default:
              throw new ErrorException('GD only support jpeg, png and gif');
              break;
          }
        } catch(ErrorException $e) {
          throw new ErrorException($e -> getMessage());
        }
        imagedestroy($img);
        imagedestroy($thumb);
        array_push($img_sizes, $image_size -> image_size_name . '_' . $postfix . '.' . $img_path_parts['extension']);
      }
    }
    return $img_sizes;
  }
  
  /** 
  	*get all record from `image_size` table
  	* @param string 
	*
  */
  protected static function getAllImageSizes($image_size_name = null) 
  {
    if(is_null($image_size_name)) {
        
        $image_sizes = ImageSize::all();
    } else {
        $image_sizes = ImageSize::where('image_size_name', $image_size_name) -> get();
    }
    
    foreach($image_sizes as $image_size) {
      $image_size -> image_size_usage = json_decode($image_size -> image_size_usage, true);
    }
    return $image_sizes;
  }

    public static function moveUploadedImages($file, $uploadDir, $fileName) 
    {
        if($file -> isValid()) 
        {
            if(!is_dir($uploadDir)) 
            {
                mkdir($uploadDir, 0777, true);
            }
            $fileName .= '.' . $file -> getClientOriginalExtension();
            $file -> move($uploadDir, $fileName);
            return $uploadDir. '/' .$fileName;
        } 
        else 
        {
            return false;
        }
    }
    
   
}