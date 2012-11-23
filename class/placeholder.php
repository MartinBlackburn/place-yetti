<?php

class Placeholder 
{
    private $images = array();
    private $imagesDir;
    private $image;
    private $imageWidth = 300;
    private $imageHeight = 300;
    private $imagePath;
    private $imageType;
    
    /**
	 * Construct the PlaceHoler calls
	 */
    function __construct()
    {        
        //set default image dir
        $this->imagesDir = getcwd() . "/images/";
        
        //add all images to the array
        $this->setImages();
        
        //set image dimensions to those requested
        $this->setDimensions();
    }
    
    
    /**
    * Add all images to the images array
    */
    private function setImages()
    {           
        $dirhandler = opendir($this->imagesDir);
        
        while ($file = readdir($dirhandler))
        {
            clearstatcache();
            
            if(is_file($this->imagesDir . '/' . $file))
            {    
                if ($file != '.' && $file != '..')
                {
                    $this->images[] = $file;
                }
            }
        }
        
        closedir($dirhandler);
    }
    
    
    /**
    * Set the image dimensions
    */
    private function setDimensions()
    {   
        //get request url
        $requestURI = explode("/", $_SERVER["REQUEST_URI"]);
        $width = (int)$requestURI[1];
        $height = (int)$requestURI[2];
        
        //set width if numeric
        if(is_numeric($width))
        {
            $this->imageWidth = $width;
        }
        
        //set height if numeric, otherwise set it same as width
        if(is_numeric($height))
        {
            $this->imageHeight = $height;
        }
        else {
            $this->imageHeight = $width;
        }
    }
    
    
    /**
    * Get a random image from images array
    */
    private function getRandomImage()
    {   
        $imagePath =  $this->imagesDir . $this->images[array_rand($this->images)];
        
        $this->imagePath = $imagePath;
        $this->imageType = image_type_to_mime_type(exif_imagetype($imagePath));
        $this->image = ImageCreateFromString(file_get_contents($imagePath));
    }
    
    
    /**
    * Scales the image based on a new width, maintaining the original aspect ratio.
    * 
    * @param int $width
    */
    private function resizeByWidth($width) 
    {
        $ratio = $this->imageWidth / $width;
        
        $this->imageHeight = round($this->imageHeight * $ratio);
    }
    
    
    /**
     * Scales the image based on a new height, maintaining the original aspect ratio.
     * 
     * @param int $height
     */
    private function resizeByHeight($height) 
    {
        $ratio = $this->imageHeight / $height;
        
        $this->imageWidth = $this->imageWidth * $ratio;
    }
    
    
    /**
    * Resize image to values given
    */
    private function resizeImage()
    {
        //get source with and height
        list($sourceWidth, $sourceHeight) = getimagesize($this->imagePath);
        
        //get ratios
        $widthRatio = $this->imageWidth / $sourceWidth;
        $heightRatio = $this->imageHeight / $sourceHeight;
        
        //check ratios and resize by the bigger one
        if($widthRatio > $heightRatio) {
            $this->resizeByWidth($sourceWidth);
        } else {
            $this->resizeByHeight($sourceHeight);
        }
        
        //create blank image with the correct size
        $imageResized = imagecreatetruecolor($this->imageWidth, $this->imageHeight);
        
        //copy new image at the new size from the source image to the new blank image
        imagecopyresampled($imageResized, $this->image, 0, 0, 0, 0, $this->imageWidth, $this->imageHeight, $sourceWidth, $sourceHeight);
        
        //update the image
        $this->image = $imageResized;
    }
    
    
    /**
    * Set the header based on image type
    */
    private function setHeader()
    {
        header('Content-type: ' . $this->imageType);
    }
    
    
    /**
    * Display image
    */
    public function render() 
    {        
        $this->getRandomImage();
        
        $this->resizeImage();
        
        $this->setHeader();
        
        imagepng($this->image);
        imagedestroy($this->image);        
    }
}