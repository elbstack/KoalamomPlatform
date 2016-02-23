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
            new Bundle\CoreBundle\KoalamonCoreBundle(),

            new Bundle\IncidentDashboardBundle\KoalamonIncidentDashboardBundle(),
            new Bundle\StatBundle\KoalamonStatBundle(),
            new Bundle\DefaultBundle\KoalamonDefaultBundle(),
            new Bundle\WebhookBundle\KoalamonWebhookBundle(),
            new Bundle\ConsoleBundle\KoalamonConsoleBundle(),
            new Bundle\RestBundle\KoalamonRestBundle(),
            new Bundle\InformationBundle\KoalamonInformationBundle(),
            new Bundle\PluginBundle\KoalamonPluginBundle(),
            new Bundle\ScreenshotBundle\KoalamonScreenshotBundle(),

            new HeartbeatBundle\KoalamonHeartbeatBundle(),

            new Bundle\IntegrationBundle\KoalamonIntegrationBundle(),
            new Bundle\GeckoBoardBundle\KoalamonGeckoBoardBundle(),


            new NotificationBundle\KoalamonNotificationBundle(),

        ];

        return $bundles;
    }
}
