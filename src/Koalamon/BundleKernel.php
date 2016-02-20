<?php
/**
 * Created by PhpStorm.
 * User: nils.langner
 * Date: 20.02.16
 * Time: 07:45
 */

namespace Koalamon;

class BundleKernel
{
    public static function registerBundles()
    {
        $bundles = [
            new \Koalamon\Bundle\CoreBundle\KoalamonCoreBundle(),

            new \Koalamon\Bundle\IncidentDashboardBundle\KoalamonIncidentDashboardBundle(),
            new \Koalamon\Bundle\StatBundle\KoalamonStatBundle(),
            new \Koalamon\Bundle\DefaultBundle\KoalamonDefaultBundle(),
            new \Koalamon\Bundle\WebhookBundle\KoalamonWebhookBundle(),
            new \Koalamon\Bundle\ConsoleBundle\KoalamonConsoleBundle(),
            new \Koalamon\Bundle\RestBundle\KoalamonRestBundle(),
            new \Koalamon\Bundle\InformationBundle\KoalamonInformationBundle(),
            new \Koalamon\Bundle\PluginBundle\KoalamonPluginBundle(),
            new \Koalamon\Bundle\ScreenshotBundle\KoalamonScreenshotBundle(),

            // integrations -> some will be moved to LeanKoala bundle
            new \Koalamon\Bundle\IntegrationBundle\KoalamonIntegrationBundle(),
            new \Koalamon\Bundle\Integration\KoalaPingBundle\KoalamonIntegrationKoalaPingBundle(),
            new \Koalamon\Bundle\Integration\WebhookBundle\KoalamonIntegrationWebhookBundle(),
            new \Koalamon\Bundle\Integration\MissingRequestBundle\KoalamonIntegrationMissingRequestBundle(),
            new \Koalamon\Bundle\Integration\GooglePageSpeedBundle\KoalamonIntegrationGooglePageSpeedBundle(),
            new \Koalamon\Bundle\Integration\JsErrorScannerBundle\KoalamonIntegrationJsErrorScannerBundle(),
            new \Koalamon\Bundle\Integration\SiteInfoBundle\KoalamonIntegrationSiteInfoBundle(),
            new \Koalamon\Bundle\Integration\SmokeBundle\KoalamonIntegrationSmokeBundle(),
            new \Koalamon\Bundle\Integration\SmokeBasicBundle\KoalamonIntegrationSmokeBasicBundle(),

            new \Koalamon\Bundle\GeckoBoardBundle\KoalamonGeckoBoardBundle(),

            new \Koalamon\NotificationBundle\KoalamonNotificationBundle(),
        ];

        return $bundles;
    }
}