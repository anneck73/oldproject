<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\MealMatch\Twig;

use Symfony\Component\HttpKernel\Kernel;
use Twig_Extension as TwigExtension;
use Twig_SimpleFunction as TwigSimpleFunction;

/**
 * @todo: Finish PHPDoc!
 * A summary informing the user what the class PhpInfoExtension does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 */
class PhpInfoExtension extends TwigExtension
{
    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param string $myArgument with a *description* of this argument, these may also
     *                           span multiple lines
     *
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new TwigSimpleFunction('phpinfo', array($this, 'getPhpInfos')),
            new TwigSimpleFunction('symfonyVersion', array($this, 'getSymfonyVersion')),
        );
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param string     $myArgument with a *description* of this argument, these may also
     *                               span multiple lines
     * @param mixed|null $key
     *
     * @return array|string
     */
    public function getPhpInfos($key = null)
    {
        $infos = array(
            'PHP' => array('name' => 'PHP', 'version' => PHP_VERSION),
        );
        $extensions = get_loaded_extensions();
        foreach ($extensions as $extension) {
            $infos += array($extension => array('name' => $extension, 'version' => phpversion($extension)));
        }

        if (null === $key) {
            return $infos;
        }

        return $key.': '.$infos[$key]['version'];
    }

    public function getSymfonyVersion()
    {
        return Kernel::VERSION;
    }

    public function getName()
    {
        return 'PhpInfos';
    }
}
