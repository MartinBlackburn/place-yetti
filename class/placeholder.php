<?php

class Placeholder 
{
    private $images = array();
    private $imagesDir;
    private $sourceImage;
    private $sourceImagePath;
    private $sourceImageType;
    private $placeHolderImage;
    private $desiredWidth = 300;
    private $desiredHeight = 300;
    
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
            $this->desiredWidth = $width;
        }
        
        //set height if numeric, otherwise set it same as width
        if(is_numeric($height))
        {
            $this->desiredHeight = $height;
        }
        else {
            $this->desiredHeight = $width;
        }
    }
    
    
    /**
    * Get a random image from images array
    */
    private function getRandomImage()
    {   
        $imagePath =  $this->imagesDir . $this->images[array_rand($this->images)];
        
        $this->sourceImagePath = $imagePath;
        $this->sourceImageType = image_type_to_mime_type(exif_imagetype($imagePath));
        $this->sourceImage = ImageCreateFromString(file_get_contents($imagePath));
    }
    
    
    /**
    * Resize image to values given
    */
    private function resizeImage()
    {
        //get source with and height
        list($sourceWidth, $sourceHeight) = getimagesize($this->sourceImagePath);
        
        //get aspect ratios
        $sourceRatio = $sourceWidth / $sourceHeight;
        $desiredRatio = $this->desiredWidth / $this->desiredHeight;
        
        //check aspect ratios and resize as needed
        if ($sourceRatio > $desiredRatio) 
        {
            $newHeight = $this->desiredHeight;
            $newWidth = (int)($this->desiredHeight * $sourceRatio);
        } else {
            $newWidth = $this->desiredWidth;
            $newHeight = (int)($this->desiredWidth / $sourceRatio);
        }
        
        //make a temporary image, resized based on the source image and desired aspect ratio
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($resizedImage, $this->sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight);
        
        //get crop positions
        $cropPosX = ($newWidth - $this->desiredWidth) / 2;
        $cropPosY = ($newHeight - $this->desiredHeight) / 2;
        
        //create placeholder image from the resized one, cropped around the center
        $placeHolderImage = imagecreatetruecolor($this->desiredWidth, $this->desiredHeight);
        imagecopy($placeHolderImage, $resizedImage, 0, 0, $cropPosX, $cropPosY, $this->desiredWidth, $this->desiredHeight);
        
        //update the image
        $this->placeHolderImage = $placeHolderImage;
        
        //destroy images
        imagedestroy($resizedImage);
    }
    
    
    /**
    * Set the header based on image type
    */
    private function setHeader()
    {
        header('Content-type: ' . $this->sourceImageType);
    }
    
    
    /**
    * Display image
    */
    public function render() 
    {        
        $this->getRandomImage();
        
        $this->resizeImage();
        
        $this->setHeader();
        
        imagepng($this->placeHolderImage);
        imagedestroy($this->placeHolderImage);        
    }
}