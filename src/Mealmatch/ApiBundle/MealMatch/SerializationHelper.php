<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\MealMatch;

use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @todo: Finish PHPDoc!
 * @todo: Finish this class it's WIP
 * A summary informing the user what the class SerializationHelper does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 */
final class SerializationHelper
{
    public static function serializeEntity($entity, string $format = 'json')
    {
        $normalizer = new ObjectNormalizer();

        $encoders = array(
            new CsvEncoder(),
            new JsonEncoder(),
            new XmlEncoder(),
        );
        $dateTimeNorm = new DateTimeNormalizer('d.m.Y H:i');
        $callback = function ($dateTime) {
            if (null === $dateTime) {
                return null;
            }

            return $dateTime->format('d.m.Y H:i');
        };
        $normalizer->setCircularReferenceHandler(
            function ($object) {
                return $object->getId();
            }
        );
        $normalizer->setCallbacks(array('createdAt' => $callback));
        $normalizer->setCallbacks(array('updatedAt' => $callback));
        $normalizer->setCallbacks(array('deletedAt' => $callback));

        $normalizer->setCircularReferenceLimit(1);

        $serializer = new Serializer(array($normalizer, $dateTimeNorm), $encoders);

        return $serializer->serialize($entity, $format);
    }
}
