<?php

class Placeholder 
{
    private $images = array();
    private $imageDir;
    
    /**
	 * Construct the PlaceHoler calls
	 */
    function __construct()
    {
        //set default image dir
        $this->imageDir = getcwd() . "/images/";
        
        //add allimages to the array
        $this->getImages();
    }
    
    
    /**
    * Add all images to the images array
    */
    private function getImages()
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