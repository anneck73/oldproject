<?php
/**
 * Created by PhpStorm.
 * User: andre
 * Date: 12.09.18
 * Time: 13:52
 */

namespace Mealmatch\ApiBundle\Services;

use Mealmatch\ApiBundle\Entity\Meal\ProMeal;
use Mealmatch\MealmatchKernelTestCase;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Date;

class ProMealServiceTest extends MealmatchKernelTestCase
{

    public function testFindAllByCity()
    {

        $proMealService = static::$kernel->getContainer()->get('api.pro_meal.service');
        $searchCity = 'Berlin';
        $result = $proMealService->findAllByCity($searchCity);
        self::assertNotNull($result);

        self::assertGreaterThan(
            0, count($result),
            'Got ZERO results from findAllByCity:'.$searchCity
        );

        /** @var ProMeal $firstResult */
        $isLeaf = $result[0]["leaf"];
        self:self::assertTrue($isLeaf, 'ERROR! WE HAVE A ROOT MEAL IN THE QUERY RESULT!!');
    }

    public function testFindAllByCityAndStartdate()
    {
        $proMealService = static::$kernel->getContainer()->get('api.pro_meal.service');
        $searchCity = 'Berlin';
        $startDate = new \DateTime('now');
        $result = $proMealService->findAllByCityAndStartdate($searchCity, $startDate);

        self::assertNotNull($result);

        self::assertGreaterThan(
            0, count($result),
            'Got ZERO results from findAllByCity:'.$searchCity.' and StartDate: '.$startDate->format('d.m.Y h:i')
        );

        /** @var ProMeal $firstResult */
        $isLeaf = $result[0]->isLeaf();
        self:self::assertTrue($isLeaf, 'ERROR! WE HAVE A ROOT MEAL IN THE QUERY RESULT!!');

    }
}
