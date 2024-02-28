<?php
/**
 * Copyright 2016-2017 MealMatch UG
 *
 * Author: Wizard <wizard@mealmatch.de>
 * Created: 17.08.17 09:28
 */

namespace Tests\ApiBundle\Model;

use Mealmatch\ApiBundle\Entity\Meal\ProMeal;
use Mealmatch\ApiBundle\Model\MetaServiceData;
use Mealmatch\MealmatchKernelTestCase;

class ServiceDataManagerTest extends MealmatchKernelTestCase
{
    public function testCreate() {
        $proMealSD = new MetaServiceData('ProMeal', new ProMeal());
        self::assertNotNull($proMealSD, 'Failed to create MetaServiceData!!!!');
    }
    public function testEntityFunctions() {
        $proMealSD = new MetaServiceData('ProMeal', new ProMeal());

        $proMealSD->addEntity('AnotherProMeal', new ProMeal());
        /** @var ProMeal $anotherProMeal */
        $anotherProMeal = $proMealSD->getEntity('AnotherProMeal');
        $isManaged = $proMealSD->isManaged('AnotherProMeal');
        $isValid = $proMealSD->isValid();
        $allEntities = $proMealSD->getEntities();
        $countAll = $allEntities->count();

        if($isManaged) {
            $this->fail('Should not be managed by now!');
        }

        if($isValid) {
            $this->fail('Should not be valid by now!');
        }

        self::assertEquals(2, $countAll, 'There should be 2 entities inside now! not: ' . $countAll);

    }

}
