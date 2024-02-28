<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Mealmatch\ApiBundle\Entity\Meal\BaseMealCategory;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadMealCategoriesData implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{
    /**
     * @todo: Finish PHPDoc!
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $mealCat1 = new BaseMealCategory();
        $mealCat1->setName('Vegetarisch');
        $mealCat1->setDescription('Vegetarisch');
        $mealCat1->setImageURL('cat/Test-Vegan.png');

        $mealCat2 = new BaseMealCategory();
        $mealCat2->setName('Vegan');
        $mealCat2->setDescription('Das ist eine Test Kategorie');
        $mealCat2->setImageURL('cat/Test-Normal.png');

        $mealCat3 = new BaseMealCategory();
        $mealCat3->setName('Fischgericht');
        $mealCat3->setDescription('Das ist eine Test Kategorie');
        $mealCat3->setImageURL('cat/Test-Nicht-Normal.png');

        $mealCat4 = new BaseMealCategory();
        $mealCat4->setName('Fleischgericht');
        $mealCat4->setDescription('Das ist eine Test Kategorie');
        $mealCat4->setImageURL('cat/Test-Nicht-Normal.png');

        $mealCat5 = new BaseMealCategory();
        $mealCat5->setName('Laktosefrei');
        $mealCat5->setDescription('Das ist eine Test Kategorie');
        $mealCat5->setImageURL('cat/Test-Nicht-Normal.png');

        $manager->persist($mealCat1);
        $manager->persist($mealCat2);
        $manager->persist($mealCat3);
        $manager->persist($mealCat4);
        $manager->persist($mealCat5);

        $manager->flush();
    }

    public function getOrder()
    {
        return 2;
    }
}
