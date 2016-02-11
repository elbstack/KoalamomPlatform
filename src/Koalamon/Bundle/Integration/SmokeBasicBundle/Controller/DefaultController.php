<?php

namespace Koalamon\Bundle\Integration\SmokeBasicBundle\Controller;

use Koalamon\Bundle\IncidentDashboardBundle\Entity\UserRole;
use Koalamon\Bundle\Integration\KoalaPingBundle\Entity\KoalaPingConfig;
use Koalamon\Bundle\Integration\KoalaPingBundle\Entity\KoalaPingSystem;
use Koalamon\Bundle\IntegrationBundle\Controller\SystemAwareIntegrationController;


class DefaultController extends SystemAwareIntegrationController
{
    // @todo should be done inside the config file
    const API_KEY = '17sdd232-5617-a4rr-9f0d-993edf547522';
    const INTEGRATION_ID = 'SmokeBasic';

    private $rules = array(
        '_HtmlSize',
        '_ImageSize',
        '_HtmlJsCount',
        '_HtmlForeignDomainImageTag',
        '_HttpHeaderSuccessStatus',
        '_HtmlClosingTag',
        '_HtmlNoIndex',
        '_HtmlInvalidUrlsTag',
        '_HtmlUnsecureContent',
        '_HttpDuration',
        '_HttpHeaderGzip',
        '_HttpHeaderCacheExpires',
        '_HttpHeaderCacheMaxAge',
        '_HttpHeaderCacheNoCache',
        '_ImageSize',
        '_ImageFavIcon',
        '_JsonValid',
        '_RssValid',
        '_XmlDuplicateId',
        '_SecurityPasswordSecureTranfer'
    );

    protected function getIntegrationIdentifier()
    {
        return self::INTEGRATION_ID;
    }

    protected function getApiKey()
    {
        return self::API_KEY;
    }

    public function indexAction()
    {
        $this->assertUserRights(UserRole::ROLE_ADMIN);

        sort($this->rules);

        return $this->render('KoalamonIntegrationSmokeBasicBundle:Default:index.html.twig',
            [
                'config' => $this->getConfig(),
                'systems' => $this->getSystems(),
                'integratedSystems' => $this->getIntegratedSystems(),
                'rules' => $this->rules
            ]);
    }
}
