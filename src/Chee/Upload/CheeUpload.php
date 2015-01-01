<?php namespace Chee\Upload;
use Illuminate\Foundation\Application;
use Illuminate\Support\MessageBag;

/**
* upload for upload files
* @author Chee
*/
class CheeUpload
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
    protected $extension;

    /**
    * the file's mime
    * @var string
    */
    protected $mime;

    /*
    * moved file path 
    * @var string
    */
    protected $savePath; 

    /**
    * image's width (only for image type)
    * @var int
    */
    protected $width;

    /**
    * image's height (only for image type)
    * @var int
    */
    protected $height;

    /**
    * the uploaded file size
    * @var int
    */
    protected $fileSize; 

    /**
    * the accepted file types (or mimes) for validation
    * @var string
    */   
    protected $allowedTypes;

    /**
    * minHeight property for add to lravel validation rules
    * @var int
    */
    protected $minHeight;

    /**
    * maxHeight property for add to lravel validation rules
    * @var int
    */
    protected $maxHeight;

    /**
    * minWidth property for add to lravel validation rules
    * @var int
    */
    protected $minWidth;

    /**
    * maxWidth property for add to lravel validation rules
    * @var int
    */
    protected $maxWidth;    

    /**
    * maxSize validation rule
    * @var int
    */
    protected $maxSize;

    /**
    * minSize validation rule
    * @var int
    */
    protected $minSize;
    
    /**
     * for add an string to end of file name
     * @var string
    */
    protected $postFix;
    
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

        $this->errors = new MessageBag;
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
        $this->pushError('file not send !');
        return false;
    }
    
    protected function pushError($error)
    {
        $this->errors->add($error);
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
        $this->mime = $this->file->getMimeType();
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
            $this->name = $this->file->getClientOriginalName();
        }
        else
        {
            $this->name = $name;
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
        if(!is_numeric($val))
            return;
        $this->postFix = self::getRandomString((int) $val);
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
    * return file's mime
    * @return string
    */
    public function getMime()
    {
        return $this->mime;
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
    * get file extension.
    * @return string.
    */
    public function getExtension()
    {
        return $this->extension;
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
            if(! mkdir($dest,0777,true))
                $this->pushError('directory path is invalid !');

        //if path not have `/` in ends, add this
        if (! ends_with ($dest, DIRECTORY_SEPARATOR))
        {
            $dest .= DIRECTORY_SEPARATOR;
        }
        
        $this->savePath = $dest;
        
        if ($this->file->move($this->savePath, $this->name))
            return $this->savePath . $this->name;   
        else
            $this->pushError('file can not move');

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
            $this->pushError('The uploaded file exceeds the post_max_size directive in php.ini');
            return false;
        }
        
        if (! $this->file->isValid())
            $this->pushError('this is not a file !');

        if ($this->allowedTypes)
        {
            if ($this->allowedTypes === 'image')
                array_push($tmpArray, 'image');
            else
            {
                $allowTypes = $this->allowedTypes;
                if (is_array($allowTypes))
                    $allowTypes = implode(',', $allowTypes); //convert array to string
                array_push($tmpArray, 'mimes:'. $allowTypes);
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
            array_push($tmpArray, 'max:'. $this->maxSize);                         
        
        $rules[$this->inputName] = implode('|', $tmpArray);
        
        $splitInputName = explode('.', $this->inputName);
        if (isset($splitInputName[1]))
            $input[$this->inputName] = $this->app['request']->file($splitInputName[0])[$splitInputName[1]];    
        else
            $input[$this->inputName]  = $this->app['request']->file($splitInputName[0]);
        
        $validation = $this->app['validator']->make($input, $rules);
        if($validation->fails())
        {
            $this->errors->merge($validation->messages());
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
                return false;
            if ((isset($parameters[0]) && $parameters[0] != 0) && $imageHeight < (int) $this->minHeight)
                return false;

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
                $this->pushError('image dimension not found!');
                return false;
            }
            if ((isset($parameters[0]) && $parameters[0] != 0) && $imageHeight > (int) $this->maxHeight)
                return false;
    
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
                return false;
            if ((isset($parameters[0]) && $parameters[0] != 0) &&  $imageWidth > (int) $this->maxWidth)
                return false;

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
                return false;
            if ((isset($parameters[0]) && $parameters[0] != 0) && $imageWidth < (int) $this->minWidth)
                return false;

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
    public static function getRandomString($length = 5)
    {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, (int) $length);
    }

    /**
    * remove directory
    * @param string directory path
    * @return bool
    */
    public static function deleteDir($dirPath) 
    {
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') 
            $dirPath .= '/';

        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) 
        {
            if (is_dir($file)) 
                self::deleteDir($file);
            else 
                unlink($file);
        }
        if (rmdir($dirPath))
             return true;
        else
            return false;
    }   
}
