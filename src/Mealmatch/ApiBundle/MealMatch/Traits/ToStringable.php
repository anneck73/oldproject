<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\MealMatch\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

trait ToStringable
{
    public function __toString()
    {
        $allProps = implode(
            ', ',
            array_map(
                function ($v, $k) {
                    $trimPos = \strlen(__CLASS__) + 1;
                    $x = substr($k, $trimPos, 20);
                    if ($v instanceof \DateTime) {
                        $v = $v->format('Y-m-d H:i:s');
                    }
                    if ($v instanceof Collection) {
                        $v = 'collection';
                    }
                    if (\is_array($v)) {
                        // $v = implode(', ', $v);
                        $vC = new ArrayCollection($v);
                        if ($vC->containsKey('Name')) {
                            $v = $vC->get('Name');
                        } else {
                            $v = $vC->first();
                        }
                    }

                    return sprintf("%s='%s'", $x, $v);
                },
                $this->toArray(),
                array_keys($this->toArray())
            )
        );

        return __CLASS__.$allProps;
    }

    private function toArray()
    {
        $props = array();
        foreach ((array) $this as $key => $value) {
            $props[$key] = $value;
        }

        return $props;
    }
}
