<?php

namespace MM\SamyEditorBundle\Scm;

use MM\SamyEditorBundle\Entity;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\DependencyInjection\Container;

/**
 * Service for reordering channel-lists
 *
 * @package MM\SamyEditorBundle\Scm
 */
class RecentManager
{

    /**
     * @var Container
     */
    protected $container;

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param mixed $container
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param \Symfony\Bridge\Doctrine\RegistryInterface $doctrine
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container);
    }

    /**
     * @param Entity\ScmPackage $scmPackage
     */
    public function addScmPackage(Entity\ScmPackage $scmPackage, $response)
    {
        $request = $this->getContainer()->get('request');

        // get scm packages from cookie
        $recentScmPackages = $this->getRecentScmPackagesFromCookie();

        // add scmPackage to the recentPackages
        $recentScmPackages[] = $scmPackage->getHash();

        // set / update cookie for the response
        $response->headers->setCookie(new Cookie('recent_scm_packages', json_encode($recentScmPackages)));
    }

    /**
     * Read recent packages from cookie
     * @return array
     */
    protected function getRecentScmPackagesFromCookie()
    {
        $request = $this->getContainer()->get('request');

        // redirect and set cookie
        if ($request->cookies->has('recent_scm_packages')) {
            $recentScmPackages = json_decode($request->cookies->get('recent_scm_packages'));
        } else {
            $recentScmPackages = array();
        }

        return $recentScmPackages;
    }

    public function getRecentScmPackages()
    {
        // load from cookie
        $recentScmPackages = $this->getRecentScmPackagesFromCookie();

        // reverse
        $recentScmPackages = array_reverse($recentScmPackages);

        // load from doctrine
        $em = $this->getContainer()->get('doctrine');
        $scmPackageRepository = $em->getRepository('MM\SamyEditorBundle\Entity\ScmPackage');

        // itererate through the (up to) latest 5 scm packages
        $scmPackages = [];
        for ($i = 0; $i < min(count($recentScmPackages), 5); $i++) {
            $hash = $recentScmPackages[$i];
            $scmPackages[] = $scmPackageRepository->findOneBy(array('hash' => $hash));
        }

        return $scmPackages;
    }
}
