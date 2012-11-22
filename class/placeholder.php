<?php

class Placeholder 
{
    private $images = array();
    private $imageDir;
    private $imageWidth = 300;
    private $imageHeight = 300;
    
    /**
	 * Construct the PlaceHoler calls
	 */
    function __construct()
    {        
        //set default image dir
        $this->imageDir = getcwd() . "/images/";
        
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
        $dirhandler = opendir($this->imageDir);
        
        while ($file = readdir($dirhandler))
        {
            clearstatcache();
            
            if(is_file($this->imageDir . '/' . $file))
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
        $width = 300;
        $height = 300;
        
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
    * 
    * @return string
    */
    private function getImage()
    {
        return $this->images[array_rand($this->images)];
    }
    
    
    /**
    * Display image
    */
    public function render() 
    {
        header('Content-type: image/png');
        
        readfile($this->imageDir . $this->getImage());
    }
}