<?php
/**
 * Copyright (c) 2017. Mealmatch GmbH
 * Author: Wizard <wizard@mealmatch.de>
 */

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            // http plug
            new Http\HttplugBundle\HttplugBundle(),
            // FOSRest
            new FOS\RestBundle\FOSRestBundle(),
            // FOSUser
            new FOS\UserBundle\FOSUserBundle(),
            // FOSMessaging
            new FOS\MessageBundle\FOSMessageBundle(),
            // FOSJSRouting
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            // OAuth
            new HWI\Bundle\OAuthBundle\HWIOAuthBundle(),
            // JMS the great ...
            new JMS\SerializerBundle\JMSSerializerBundle($this),
            new JMS\I18nRoutingBundle\JMSI18nRoutingBundle(),
            new JMS\TranslationBundle\JMSTranslationBundle(),
            new JMS\DiExtraBundle\JMSDiExtraBundle($this),
            new JMS\AopBundle\JMSAopBundle(),
            // Webpack Encore
            new Symfony\WebpackEncoreBundle\WebpackEncoreBundle(),
            // CKEditor for Views
            new Ivory\CKEditorBundle\IvoryCKEditorBundle(),
            // GeoCode for GMaps backend integration
            new Bazinga\GeocoderBundle\BazingaGeocoderBundle(),
            // Doctrine Migrations to update the db
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            // Doctrine Behaviours (!)-> contains Point Entity-Type!
            new Knp\DoctrineBehaviors\Bundle\DoctrineBehaviorsBundle(),
            // STOF Doctrine Extensions
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            // The Filesystem abstraction layer ...
            // new Knp\Bundle\GaufretteBundle\KnpGaufretteBundle(),
            // LIIP Image Manipulation
            new Liip\ImagineBundle\LiipImagineBundle(),
            // Amazon bundle
            new Aws\Symfony\AwsBundle(),
            // WhiteOctober SwiftMailer Doctrine bundle ...
            new WhiteOctober\SwiftMailerDBBundle\WhiteOctoberSwiftMailerDBBundle(),
            // WhiteOctober PagerFanta
            new WhiteOctober\PagerfantaBundle\WhiteOctoberPagerfantaBundle(),
            // Shivas Versioning
            new Shivas\VersioningBundle\ShivasVersioningBundle(),
            // Calendar ...
            new ADesigns\CalendarBundle\ADesignsCalendarBundle(),
            // FullJSCalendar
            new AncaRebeca\FullCalendarBundle\FullCalendarBundle(),
            // EasyAdmin Bundle
            // deprecated since 1.6 ... new JavierEguiluz\Bundle\EasyAdminBundle\EasyAdminBundle(),
            new EasyCorp\Bundle\EasyAdminBundle\EasyAdminBundle(),
            // KNP Form Flow
            // new Craue\FormFlowBundle\CraueFormFlowBundle(),
            // Font Awesome Bundle
            new Bmatzner\FontAwesomeBundle\BmatznerFontAwesomeBundle(),
            // Vich Fileuploader
            new Vich\UploaderBundle\VichUploaderBundle(),
            // Flysystem
            new Oneup\FlysystemBundle\OneupFlysystemBundle(),
            // Cron-Tab
            new Cron\CronBundle\CronCronBundle(),
            // Sonata SEO
            new Sonata\SeoBundle\SonataSeoBundle(),
            // MealMatch Bundles
            new MMUserBundle\MMUserBundle(),
            new MMWebFrontBundle\MMWebFrontBundle(),
            new MMApiBundle\MMApiBundle(),
            new Mealmatch\ApiBundle\ApiBundle(),
            new Mealmatch\GameLogicBundle\MMGameLogicBundle(),
            new Mealmatch\MemeMemoryBundle\MealmatchMemeMemoryBundle(),
            new Mealmatch\PayPalBundle\MealmatchPayPalBundle(),
            new Mealmatch\ServiceTasksBundle\MealmatchServiceTasksBundle(),
            new Mealmatch\WebAdminBundle\MealmatchWebAdminBundle(),
            new Mealmatch\CalendarBundle\MealmatchCalendarBundle(),
            new Mealmatch\WorkflowBundle\MealmatchWorkflowBundle(),
            new Mealmatch\SearchBundle\MealmatchSearchBundle(),
            new Mealmatch\RestaurantWebFrontBundle\MealmatchRestaurantWebFrontBundle(),
            new Sideclick\BootstrapModalBundle\SideclickBootstrapModalBundle(),
            // Mangopay
            new Mealmatch\MangopayBundle\MealmatchMangopayBundle(),
            new Mealmatch\CouponBundle\MealmatchCouponBundle(),
            new Mealmatch\UICouponBundle\MealmatchUICouponBundle(),
            new Gos\Bundle\WebSocketBundle\GosWebSocketBundle(),
            new Gos\Bundle\PubSubRouterBundle\GosPubSubRouterBundle(),
            new Mealmatch\UIPublicBundle\UIPublicBundle(),
        ];

        if (in_array($this->getEnvironment(), ['dev', 'test', 'pipeline'], true)) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle();
            $bundles[] = new Symfony\Bundle\WebServerBundle\WebServerBundle();
            $bundles[] = new DAMA\DoctrineTestBundle\DAMADoctrineTestBundle();
        }

        return $bundles;
    }

    public function getCacheDir()
    {
        return dirname(__DIR__).'/var/cache/'.$this->getEnvironment();
    }

    public function getLogDir()
    {
        return dirname(__DIR__).'/var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }

    public function getRootDir()
    {
        return __DIR__;
    }
}
