<?php

namespace Samuelrd\FileCrypt;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Samuelrd\FileCrypt\Skeleton\SkeletonClass
 */
class FileCryptFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'filecrypt';
    }
}
