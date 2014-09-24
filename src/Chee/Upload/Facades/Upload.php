<?php namespace Chee\Upload\Facades;

use Illuminate\Support\Facades\Facade;

class Upload extends Facade {

    /**
     * Get the registered name of the component
     *
     * @return string
     */
    protected static function getFacadeAccessor() {
        return 'upload';
    }
}