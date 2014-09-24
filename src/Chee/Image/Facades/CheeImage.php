<?php namespace Chee\Image\Facades;

use Illuminate\Support\Facades\Facade;

class CheeImage extends Facade {

    /**
     * Get the registered name of the component
     *
     * @return string
     */
    protected static function getFacadeAccessor() {
        return 'image';
    }
}