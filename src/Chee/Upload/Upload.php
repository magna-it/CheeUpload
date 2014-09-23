<?php namespace Chee\Image;
use Illuminate\Foundation\Application;

/**
* upload for upload files
* @author Chee
*/
class Uploader
{   
	/**
	* IoC
	* @var Illuminate\doundination\Application
	*/ 
    protected $app;

    /**
    *
    *
    */
    protected $file;

    /**
    *
    *
    */
    protected $inputName;

    /**
    * the name of file for rename
    * @var string
    */
    protected $name;

    /**
    * the file's extension 
    * @var string
    */
    protected $Extension;

    /**
    * moved file path 
    * @var string
    */
    protected $savePath; 

    /**
    * image's width (only for image type)
    * @var integer
    */
    protected $width;

    /**
    * image's height (only for image type)
    * @var integer
    */
    protected $height;

    /**
    * the uploaded file size
    * @var integer
    */
    protected $fileSize; 

    /**
    * the accepted file types (or mimes) for validation
    * @var string
    */   
    protected $allowedTypes;

    /**
    * minHeight property for add to lravel validation rules
    * @var integer
    */
    protected $minHeight;

    /**
    * maxHeight property for add to lravel validation rules
    * @var integer
    */
    protected $maxHeight;

    /**
    * minWidth property for add to lravel validation rules
    * @var integer
    */
    protected $minWidth;

    /**
    * maxWidth property for add to lravel validation rules
    * @var integer
    */
    protected $maxWidth;    

    /**
    * maxSize validation rule
    * @var integer
    */
    protected $maxSize;

    /**
    * minSize validation rule
    * @var integer
    */
    protected $minSize;

    /**
    * errors
    * @var array
    */
    protected $errors = array();
    
    /**
    * Initialize class
    */
    public function __construct(Application $app) 
    {
        $this->app = $app;
    }
    
    /**
    * check if file uploaded or not
    * @param input name 
    * @return bool
    */
    public function checkExist($name)
    {
        if ($this->app['request']->hasFile($name))
        {
            return true;
        }
        return false;
    }
    
    /**
    * set properties and dependencies 
    * @param input name 
    * @param new name of file
    */
    public function set($inputName, $fileName = null)
    {
        $this->file = $this->app['request']->file($inputName);
        $this->inputName = (string) $inputName;
		$this->fileSize = (int) $this->file->getSize();
		$this->extension = $this->file->getClientOriginalExtension();
		$this->setName($fileName);
    }
    
    /**
    * set name and extension 
    * @param file name
    */
    private function setName($name)
    {
    	if (is_null($name))
    	{
    		$this -> name = $this->file->getClientOriginalName();
    	}
    	else
    	{
    		$this -> name = $name;
    		$this->setExtension();
    	}
    }
    
    /**
    * set file extension 
    */
    private function setExtension()
    {
        
    	$this->name = $this->name . '.' . $this->extension;
    }
    
    /**
    * get allowed types for validation 
    * @param string
    */
    public function mimes($mimeType)
    {
    	$this->allowedTypes = $mimeType;   
    }

    /**
    * get max size for validation 
    * @param integer
    */
    public function maxSize($val)
    {
        $this->maxSize = (int) $val;
    }

    /**
    * get min size for validation 
    * @param integer
    */
    public function minSize($val)
    {
        $this->minSize = (int) $val;
    }

    /**
    * get min height for image validation 
    * @param integer
    */
    public function minHeight($val)
    {
        $this->minHeight = (int) $val;
    }

    /**
    * get max height for validation 
    * @param integer
    */
    public function maxHeight($val)
    {
        $this->maxHeight = (int) $val;
    }

    /**
    * get min width for validation 
    * @param integer
    */
    public function minWidth($val)
    {
        $this->minWidth = (int) $val;
    }

    /**
    * get max width for validation 
    * @param integer
    */
    public function maxWidth($val)
    {
        $this->maxWidth = (int) $val;
    }
    
    /**
    * add random characters to end of file name 
    * @param integer
    */
    public function randomString($val)
    {
    	$fileParts = pathinfo ($this->name);
        $fileParts['filename'] .= self::getRandomString((int) $val);
        $this->name = $fileParts['filename'] . '.' . $this->extension;
    }
    
    /**
    * return uploaded file name
    * @return string
    */
    public function getName()
    {
    	return $this->name;
    }
    
    /**
    * return uploaded image width
    * @return integer
    */
    public function getWidth()
    {
        return $this->width;
    }

    /**
    * return uploaded image height
    * @return integer
    */
    public function getHeight()
    {
        return $this->height;
    }

    /**
    * return uploaded file path
    * @return string 
    */
    public function getSavePath()
    {
        return $this->savePath;
    }

    /**
    * return uploaded file size
    * @return integer
    */
    public function getFileSize()
    {
        return $this->fileSize;
    }

    /**
    * return uploaded file extension
    * @return string
    */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
    * return errors
    * @return array
    */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
    * move tmp file to destination path
    * @param string destination path
    * @return string file path
    */
    public function move($dest)
    {
    	//if direcory not found create it
    	if (! file_exists ($dest))
    	{
			if(! mkdir($dest,0777,true))
			{
				array_push($this->errors, 'directory path is invalid !');
			}
		}

		//if path not have `/` in ends, add this
		if (! ends_with ($dest, DIRECTORY_SEPARATOR))
		{
		    $dest .= DIRECTORY_SEPARATOR;
		}

    	$this->savePath = $dest;
    	
        if ($this->file->move($this->savePath, $this->name))
        {
        	return $this->savePath . '/' . $this->name;	
        }
        else
        {
            array_push($this->errors, 'file can not move');
        } 
       return false ; 
    }
    
    /**
    * validate file before upload
    * @return bool
    */
    public function validation()
    {
    	//push tmp validation rules to this array 
        $tmpArray = array();
        //create laravel validation rule from tmpArray
        $rules = array();

    	$postMaxSize = ini_get('post_max_size');
    	if (! $postMaxSize && ($postMaxSize < $this->fileSize))
    	{
    		array_push($this->errors, 'The uploaded file exceeds the post_max_size directive in php.ini');
    		return false;
    	}
    	
    	if (! $this->file->isValid())
		{
			array_push($this->errors, 'this is not a file !');
		}

    	if ($this->allowedTypes)
    	{
            if ($this->allowedTypes === 'image')
            {
                array_push($tmpArray, 'image');
            }
            else
            {
                array_push($tmpArray, 'mimes:'. $this->allowedTypes);
            }
    	}
        if ($this->minHeight)
        {
            $this->addMinHeightRule();
            array_push($tmpArray, 'minHeight:'. $this->minHeight);                        
        }
        if ($this->maxHeight)
        {
            $this->addMaxHeightRule();
            array_push($tmpArray, 'maxHeight:'. $this->maxHeight);                         
        }
        if ($this->minWidth)
        {
            $this->addMinWidthRule();
            array_push($tmpArray, 'minWidth:'. $this->minWidth);                        
        }
        if ($this->maxWidth)
        {
            $this->addMaxWidthRule();
            array_push($tmpArray, 'maxWidth:'. $this->maxWidth);                       
        }
        if ($this->maxSize)
        {
            array_push($tmpArray, 'max:'. $this->maxSize);                         
        }
        
        $rules[$this->inputName] = implode('|', $tmpArray);
        
        $splitInputName = explode('.', $this->inputName);
        if (isset($splitInputName[1]))
        {
            $input[$this->inputName] = $this->app['request']->file($splitInputName[0])[$splitInputName[1]];    
        }
        else
        {
            $input[$this->inputName]  = $this->app['request']->file($splitInputName[0]);
        }
        
        
        $validation = $this->app['validator']->make($input, $rules);
        if($validation->fails())
        {
            array_push($this->errors, $validation->messages()->toJson());
            return false;
        }
    	return true;
    }


    /**
     * add `minHeight` to laravel rules
     */
    private function addMinHeightRule()
    {
        $this->app['validator']->extend('minHeight', function($attribute, $value, $parameters)
        {
            list ($imageWidth, $imageHeight) = getimagesize($value);
            if (! $imageHeight)
            {
                return false;
            }
            if ((isset($parameters[0]) && $parameters[0] != 0) && $imageHeight < (int) $this->minHeight)
            {
                return false;
            }
            return true;
        });
    }
	
    /**
     * add `maxHeight` to laravel rules
     */
    private function addMaxHeightRule()
    {
        $this->app['validator']->extend('maxHeight', function($attribute, $value, $parameters)
        {
            list ($imageWidth, $imageHeight) = getimagesize($value);
            if (! $imageHeight)
            {
                array_push ($this->errors, 'image dimension not found!');
                return false;
            }
            if ((isset($parameters[0]) && $parameters[0] != 0) && $imageHeight > (int) $this->maxHeight)
            {
                return false;
            }
            return true;
        });
    }
    
    /**
     * add `maxWidth` to laravel rules
     */
    private function addMaxWidthRule()
    {
        $this->app['validator']->extend('maxWidth', function($attribute, $value, $parameters)
        {
            list ($imageWidth) = getimagesize($value);
            if (! $imageWidth)
            {
                return false;
            }
            if ((isset($parameters[0]) && $parameters[0] != 0) &&  $imageWidth > (int) $this->maxWidth)
            {
                return false;
            }
            return true;
        });
    }
    
    /**
     * add `minWidth` to laravel rules
     */
    private function addMinWidthRule()
    {
        $this->app['validator']->extend('minWidth', function($attribute, $value, $parameters)
        {
            list ($imageWidth) = getimagesize($value);
            if (! $imageWidth)
            {
                return false;
            }
            if ((isset($parameters[0]) && $parameters[0] != 0) && $imageWidth < (int) $this->minWidth)
            {
                return false;
            }
            return true;
        });
    }
    
    /**
     * return errors
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * generate random characters
     * @param integer length of characters
     * @return string
     */
    private static function getRandomString($length = 5)
    {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, (int) $length);
    }
}