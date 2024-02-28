<?php
/**
 * Copyright (c) 2017. Mealmatch GmbH
 * Author: Wizard <wizard@mealmatch.de>
 */

namespace ApiBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Mealmatch\ApiBundle\Entity\Meal\BaseMeal;
use Mealmatch\ApiBundle\Entity\Meal\BaseMealCategory;
use Mealmatch\ApiBundle\Entity\Meal\MealEvent;
use Mealmatch\MealmatchKernelTestCase;

/**
 * @todo: Finish PHPDoc!
 * A summary informing the user what the class BaseMealTest does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 */
class BaseMealORMTest extends MealmatchKernelTestCase
{

    public function testBaseMealCreateWithGuest()
    {
        $baseMeal = new BaseMeal();
        $baseMeal->setTitle('Foo');
        $mealEvent = new MealEvent();
        $mealEvent->setStartDateTime(new \DateTime('+3 days'));
        $baseMeal->setMealEvents(new ArrayCollection(array($mealEvent)));

        $baseMeal->setStatus('Testing');
        $baseMeal->setDescription('Description');

        $this->logIn('MMTestGuest');
        $user = $this->em->getRepository('MMUserBundle:MMUser')->findOneByUsername('MMTestGuest');

        $baseMeal->addGuest($user);

        $this->em->persist($baseMeal);

        try {
            $this->em->flush();
        } catch (\Exception $exception) {
            $this->fail($exception->getMessage());
        }


        $guests = $baseMeal->getGuests();
        self::assertEquals(1, $guests->count(), 'There should only be 1 guest, but there where : '.$guests->count());

        $guest = $guests->first();
        self::assertEquals($guest, $user, 'The user in the guest collection is not the user put into it ?!?!?!');


        $this->em->remove($baseMeal);

        try {
            $this->em->flush();
        } catch (\Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }


    public function testBaseMealCreateWithHost()
    {
        $baseMeal = new BaseMeal();
        $baseMeal->setTitle('Foo');
        $mealEvent = new MealEvent();
        $mealEvent->setStartDateTime(new \DateTime('+3 days'));

        $baseMeal->setStatus('Testing');
        $baseMeal->setDescription('Description');

        $this->logIn('MMTestGuest');
        $user = $this->em->getRepository('MMUserBundle:MMUser')->findOneByUsername('MMTestGuest');
        $baseMeal->setHost($user);

        $this->em->persist($baseMeal);

        try {
            $this->em->flush();
        } catch (\Exception $exception) {
            $this->fail($exception->getMessage());
        }

        self::assertEquals($user, $baseMeal->getHost(), 'The Host ist not the Host?!');

        $this->em->remove($baseMeal);

        try {
            $this->em->flush();
        } catch (\Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }

    public function testBaseMealCreateWithCategories()
    {
        $baseMeal = new BaseMeal();
        $baseMeal->setTitle('Foo');
        $mealEvent = new MealEvent();
        $mealEvent->setStartDateTime(new \DateTime('+3 days'));

        $baseMeal->setStatus('Testing');
        $baseMeal->setDescription('Description');

        $baseMeal2 = new BaseMeal();
        $baseMeal2->setTitle('Foo2');
        $mealEvent = new MealEvent();
        $mealEvent->setStartDateTime(new \DateTime('+3 days'));

        $baseMeal2->setStatus('Testing');
        $baseMeal2->setDescription('Description');


        $this->logIn('MMTestGuest');
        $user = $this->em->getRepository('MMUserBundle:MMUser')->findOneByUsername('MMTestGuest');

        $baseMeal->setHost($user);
        $baseMeal2->setHost($user);

        $cat1 = new BaseMealCategory();
        $cat1->setName('CAT1')->setDescription('Category 1');
        $this->em->persist($cat1);

        $cat2 = new BaseMealCategory();
        $cat2->setName('CAT2')->setDescription('Category 2');
        $this->em->persist($cat2);

        try {
            $this->em->flush();
        } catch (\Exception $exception) {
            $this->fail($exception->getMessage());
        }

        $baseMeal->addCategory($cat1);
        $baseMeal->addCategory($cat2);

        $baseMeal2->addCategory($cat1);
        $baseMeal2->addCategory($cat2);

        $this->em->persist($baseMeal);
        $this->em->persist($baseMeal2);

        try {
            $this->em->flush();
        } catch (\Exception $exception) {
            $this->fail($exception->getMessage());
        }

        self::assertEquals($user, $baseMeal->getHost(), 'The Host ist not the Host?!');
        self::assertCount(
            2,
            new ArrayCollection($baseMeal->getCategories()->toArray()),
            'There should be 2 categories, but there where: '
            .$baseMeal->getCategories()->count()
        );

        $cat1FromMeal = $baseMeal->getCategories()->toArray()[0];
        /** @noinspection PhpUndefinedMethodInspection */
        self::assertEquals($cat1->getName(), $cat1FromMeal->getName(), 'Yeah! ummm...no');

        $this->em->remove($baseMeal);
        $this->em->remove($baseMeal2);
        $this->em->remove($cat1);
        $this->em->remove($cat2);

        try {
            $this->em->flush();
        } catch (\Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }

}
