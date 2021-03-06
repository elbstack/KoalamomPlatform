<?php

namespace Koalamon\Bundle\IntegrationBundle\Controller;

use Koalamon\Bundle\IncidentDashboardBundle\Controller\ProjectAwareController;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\Project;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\UserRole;
use Koalamon\Bundle\IntegrationBundle\Entity\IntegrationConfig;
use Koalamon\Bundle\IntegrationBundle\Entity\IntegrationSystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class SystemAwareIntegrationController extends ProjectAwareController
{
    abstract protected function getIntegrationIdentifier();

    abstract protected function getApiKey();

    protected function getConfig()
    {
        $config = $this
            ->getDoctrine()
            ->getRepository('KoalamonIntegrationBundle:IntegrationConfig')
            ->findOneBy(['integration' => $this->getIntegrationIdentifier(), 'project' => $this->getProject()]);

        if (is_null($config)) {
            $config = new IntegrationConfig();
            $config->setProject($this->getProject());
            $config->setIntegration($this->getIntegrationIdentifier());
            $config->setStatus(IntegrationConfig::STATUS_SELECTED);
        };

        return $config;
    }

    protected function getSystems()
    {
        $systems = $this->getDoctrine()
            ->getRepository('KoalamonIncidentDashboardBundle:System')
            ->findBy(['project' => $this->getProject(), 'parent' => null], ['name' => 'ASC']);

        return $systems;
    }

    protected function getIntegratedSystems()
    {
        $systems = $this
            ->getDoctrine()
            ->getRepository('KoalamonIntegrationBundle:IntegrationSystem')
            ->findBy(['integration' => $this->getIntegrationIdentifier(), 'project' => $this->getProject()]);

        $integratedSystems = array();

        foreach ($systems as $system) {
            $integratedSystems[$system->getSystem()->getId()] = $system;
        }

        return $integratedSystems;
    }

    public function storeAction(Request $request)
    {
        $this->assertUserRights(UserRole::ROLE_ADMIN);

        $systems = (array)$request->get('systems');
        $status = $request->get('status');

        $em = $this->getDoctrine()->getManager();

        $config = $this->getDoctrine()
            ->getRepository('KoalamonIntegrationBundle:IntegrationConfig')
            ->findOneBy(['project' => $this->getProject(), 'integration' => $this->getIntegrationIdentifier()]);

        if (is_null($config)) {
            $config = new IntegrationConfig();
            $config->setProject($this->getProject());
            $config->setIntegration($this->getIntegrationIdentifier());
        }

        $config->setStatus($status);

        if ($request->get('saas') == 'false') {
            $config->setUseSaaS(false);
        } else {
            $config->setUseSaaS(true);
        }

        if ($request->get('options')) {
            $config->setOptions($request->get('options'));
        }

        $em->persist($config);

        $integrationSystems = $this->getDoctrine()
            ->getRepository('KoalamonIntegrationBundle:IntegrationSystem')
            ->findBy(['project' => $this->getProject(), 'integration' => $this->getIntegrationIdentifier()]);

        foreach ($integrationSystems as $integrationSystem) {
            $em->remove($integrationSystem);
        }

        $em->flush();

        foreach ($systems as $systemId => $system) {
            if (array_key_exists('enabled', $system)) {
                $koalamonSystem = $this->getDoctrine()
                    ->getRepository('KoalamonIncidentDashboardBundle:System')
                    ->find($systemId);

                if ($koalamonSystem->getProject() != $this->getProject()) {
                    return new JsonResponse(['status' => 'failure', 'message' => 'You tried to store a system from another project.']);
                }

                $integrationSystem = new IntegrationSystem();
                $integrationSystem->setSystem($koalamonSystem);
                $integrationSystem->setIntegration($this->getIntegrationIdentifier());

                if (array_key_exists('options', $system)) {
                    $integrationSystem->setOptions($system['options']);
                }

                $em->persist($integrationSystem);
            }
        }

        $em->flush();

        return new JsonResponse(['status' => 'success', 'message' => 'Configuration stored.']);
    }

    public function restGetSystemsAction(Request $request)
    {
        if ($request->get('integration_key') != $this->getApiKey()) {
            return new JsonResponse(['status' => "failure", 'message' => 'Integration key invalid.']);
        }

        $configs = $this->getDoctrine()
            ->getRepository('KoalamonIntegrationBundle:IntegrationConfig')
            ->findBy(['status' => IntegrationConfig::STATUS_ALL, 'integration' => $this->getIntegrationIdentifier(), 'useSaaS' => true]);

        $activeSystems = array();

        foreach ($configs as $config) {
            $systems = $config->getProject()->getSystems();
            foreach ($systems->toArray() as $system) {
                $activeSystems[] = ['system' => $system, 'options' => $config->getOptions()];
            }
        }

        $configs = $this->getDoctrine()
            ->getRepository('KoalamonIntegrationBundle:IntegrationConfig')
            ->findBy(['status' => IntegrationConfig::STATUS_SELECTED, 'integration' => $this->getIntegrationIdentifier(), 'useSaaS' => true]);

        foreach ($configs as $config) {
            $integrationSystems = $this->getDoctrine()
                ->getRepository('KoalamonIntegrationBundle:IntegrationSystem')
                ->findBy(['project' => $config->getProject(), 'integration' => $this->getIntegrationIdentifier()]);

            foreach ($integrationSystems as $integrationSystem) {
                $activeSystems[] = ['system' => $integrationSystem->getSystem(), 'options' => $integrationSystem->getOptions()];
            }
        }

        $response = new JsonResponse($activeSystems);
        $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);

        return $response;
    }

    protected function getActiveSystemsForProject(Project $project, $integrationIdentifier = null, $useSaas = false)
    {
        if (is_null($integrationIdentifier)) {
            $integrationIdentifier = $this->getIntegrationIdentifier();
        }

        $activeSystems = array();

        $configs = $this->getDoctrine()
            ->getRepository('KoalamonIntegrationBundle:IntegrationConfig')
            ->findBy(['status' => IntegrationConfig::STATUS_ALL, 'integration' => $integrationIdentifier, 'useSaaS' => $useSaas, 'project' => $project]);

        foreach ($configs as $config) {
            $systems = $config->getProject()->getSystems();
            foreach ($systems->toArray() as $system) {
                $activeSystems[$system][] = ['system' => $system, 'options' => $config->getOptions()];
            }
        }

        $configs = $this->getDoctrine()
            ->getRepository('KoalamonIntegrationBundle:IntegrationConfig')
            ->findBy(['status' => IntegrationConfig::STATUS_SELECTED, 'integration' => $integrationIdentifier, 'useSaaS' => $useSaas, 'project' => $project]);

        foreach ($configs as $config) {
            $integrationSystems = $this->getDoctrine()
                ->getRepository('KoalamonIntegrationBundle:IntegrationSystem')
                ->findBy(['project' => $config->getProject(), 'integration' => $integrationIdentifier]);

            foreach ($integrationSystems as $integrationSystem) {
                if ($integrationSystem->getSystem()->getParent()) {
                    $mainSystem = $integrationSystem->getSystem()->getParent()->getIdentifier();
                } else {
                    $mainSystem = $integrationSystem->getSystem()->getIdentifier();
                }
                if (!array_key_exists($mainSystem, $activeSystems)) {
                    $activeSystems[$mainSystem] = [];
                }
                $activeSystems[$mainSystem][] = ['system' => $integrationSystem->getSystem(), 'options' => $integrationSystem->getOptions()];
            }
        }
        return $activeSystems;
    }

    public function restGetSystemsForProjectAction(Request $request)
    {
        $this->assertApiKey($request->get('api_key'));

        $response = new JsonResponse($this->getActiveSystemsForProject($this->getProject()));
        $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);

        return $response;
    }
}
