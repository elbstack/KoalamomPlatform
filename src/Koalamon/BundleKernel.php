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
    public static function registerBundles($environment)
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

            new \Koalamon\Bundle\IntegrationBundle\KoalamonIntegrationBundle(),
            new \Koalamon\Bundle\GeckoBoardBundle\KoalamonGeckoBoardBundle(),

            new \Koalamon\NotificationBundle\KoalamonNotificationBundle(),
        ];

        return $bundles;
    }
}