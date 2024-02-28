<?php
/**
 * Copyright 2016-2018 MealMatch GmbH
 *
 * Author: Wizard <wizard@mealmatch.de>
 * Created: 02.04.18 15:30
 */

namespace Mealmatch\SearchBundle\Services;


use Mealmatch\MealmatchKernelTestCase;


/**
 * @todo: Finish PHPDoc!
 * A summary informing the user what the class SearchServiceTest does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 */
class SearchServiceTest extends MealmatchKernelTestCase
{


    public function testSearchByCriteria()
    {
        $searchService = static::$kernel->getContainer()->get('api.search.service');
        $result = $searchService->searchByCriteria(array(
            'city'=>'Essen'
        ));
        static::assertGreaterThan(0, count($result), 'Erwartet >0 erhalten ' . count($result));

        $result = $searchService->searchByCriteria(array(
            'country'=>'DE'
        ));
        static::assertGreaterThan(0, count($result), 'Erwartet >0 erhalten ' . count($result));

        $result = $searchService->searchByCriteria(array(
            'sublocality'=>'Rodenkirchen'
        ));
        static::assertGreaterThan(0, count($result), 'Erwartet >0 erhalten ' . count($result));
        $result = $searchService->searchByCriteria(array(
            'city'=>'Köln',
            'country'=>'DE',
            'sublocality'=>'Rodenkirchen'
        ));
        static::assertGreaterThan(0, count($result), 'Erwartet >0 erhalten ' . count($result));

        $result = $searchService->searchByCriteria(array(
            'searchTerm' => 'ProMeal'
        ));
        static::assertGreaterThan(0, count($result), 'Erwartet >0 erhalten ' . count($result));

        $result = $searchService->searchByCriteria(array(
            'searchTerm' => 'LoadUserData'
        ));
        static::assertGreaterThan(0, count($result), 'Erwartet >0 erhalten ' . count($result));

    }
}
