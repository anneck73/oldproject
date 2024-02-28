<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\WebAdminBundle\Controller;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Mealmatch\ApiBundle\Controller\ApiController;
use Mealmatch\ApiBundle\MealMatch\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @todo: Finish PHPDoc!
 * A summary informing the user what the class CSVExporterController does.
 *
 * This controller is registered as a service in order to use other services.
 *
 * @Route(service="web_admin.exporter_controller")
 * @Security("has_role('ROLE_ADMIN')")
 */
class CSVExporterController extends ApiController
{
    /** @var Serializer $serializer */
    private $serializer;

    /** @var string $cacheDir */
    private $cacheDir = '~/tmp';

    /** @var EntityManager $entityManager */
    private $entityManager;

    /**
     * CSVExporterController constructor.
     */
    public function __construct(SerializerInterface $serializer, string $cacheDir, EntityManager $entityManager)
    {
        $this->serializer = $serializer;
        $this->cacheDir = $cacheDir;
        $this->entityManager = $entityManager;
    }

    /**
     * Direct download link/response...
     *
     * @Route("/show")
     * @Method("GET")
     *
     * @return Response
     */
    public function showExporterManagerAction()
    {
        $viewData = array(
            'viewData' => array(
                'title' => 'Admin Exporter',
            ),
        );

        return $this->render(
            '',
            $viewData
        );
    }

    /**
     * Direct download link/response...
     * Example URL /admin/exporter/AllUserProfiles.csv/mmuser.email,mmuser.roles.
     *
     * @Route("/All/{alias}/{entity}.{format}/{fields}",
     *     requirements={"fields": ".+"},
     *     defaults={"alias":"mmuser", "format": "json", "entity": "MMUserBundle:MMUser", "fields": "mmuser.email"})
     * @Method("GET")
     *
     * @return Response
     */
    public function allEntityExportAction(string $alias, string $entity, string $format, string $fields)
    {
        $allUserQB = $this->entityManager
            ->getRepository($entity)
            ->createQueryBuilder($alias)
            ->select($alias.'.id');

        $allFields = explode(',', $fields);
        foreach ($allFields as $field) {
            $allUserQB->addSelect($field);
        }
        $allUser = $allUserQB->getQuery()
                             ->getResult();

        $fileData = $this->doSerialization($format, $allUser);

        $tmpPath = $this->cacheDir.'/export.'.$format;
        file_put_contents(
            $tmpPath,
            $fileData
        );

        return $this->file($tmpPath);
    }

    /**
     * Direct download link/response...
     * Example URL /admin/exporter/NewsletterExport.csv.
     *
     * @Route("/newsletter.csv")
     *
     * @Method("GET")
     *
     * @return Response
     */
    public function newsletterExportAction(UserManager $userManager)
    {
        $format = 'csv';
        $allUserQB = $this->entityManager
            ->getRepository('MMUserBundle:MMUser')
            ->createQueryBuilder('mmuser')
            ->leftJoin('mmuser.profile', 'profile')
            ->select('mmuser.email');

        $allUserQB->addSelect('profile.firstName');
        $allUserQB->addSelect('profile.lastName');
        $allUserQB->addSelect('profile.gender');
        // $allUserQB->addSelect('mmuser.createdAt');
        // $allUserQB->addSelect('mmuser.updatedAt');

        $allUser = $allUserQB->getQuery()
                             ->getResult(AbstractQuery::HYDRATE_ARRAY);
        $fileData = $this->doSerialization($format, $allUser);

        $tmpPath = $this->cacheDir.'/newsletter-export.'.$format;
        file_put_contents(
            $tmpPath,
            $fileData
        );

        return $this->file($tmpPath);
    }

    /**
     * @todo: Finish PHPDoc!
     * A summary informing the user what the associated element does.
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param string $format
     * @param $allUser
     * @param string $myArgument with a *description* of this argument, these may also
     *                           span multiple lines
     *
     * @return bool|float|int|string
     */
    private function doSerialization(string $format, $allUser)
    {
        $normalizer = new ObjectNormalizer();
        $dateTimeNorm = new DateTimeNormalizer('d.m.Y H:i');
        $callback = function ($dateTime) {
            return $dateTime->format('d.m.Y H:i');
        };
        $normalizer->setCircularReferenceHandler(
            function ($object) {
                return $object->getId();
            }
        );
        // if the field's in the array do not exist an error is thrown...
        // @todo: find a better solution no just comment them out.
        // $normalizer->setCallbacks(array('createdAt' => $callback));
        // $normalizer->setCallbacks(array('updatedAt' => $callback));
        // $normalizer->setCallbacks(array('deletedAt' => $callback));

        $normalizer->setCircularReferenceLimit(100);

        $encoders = array(
            new CsvEncoder(),
            new JsonEncoder(),
            new XmlEncoder(),
        );

        $serializer = new Serializer(array($dateTimeNorm, $normalizer), $encoders);
        $fileData = $serializer->serialize($allUser, $format);

        return $fileData;
    }
}
