<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\MealMatch\Doctrine;

use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;

class VersionNamingStrategy extends UnderscoreNamingStrategy
{
    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param string $className
     * @param string $myArgument with a *description* of this argument, these may also
     *                           span multiple lines
     *
     * @return string
     */
    public function classToTableName($className)
    {
        $underscored = parent::classToTableName($className);
        $versioned = $underscored.'_';

        return $versioned;
    }
}
