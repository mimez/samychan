<?php

namespace MM\SamyEditorBundle\Scm\Packer;

use MM\SamyEditorBundle\Entity;
use MM\SamyEditorBundle\Scm\Configuration;

class Packer
{
    const PACKER_TYPE_BINARY = 'binary';
    const PACKER_TYPE_SQLITE3 = 'sqlite3';

    /**
     * @var \Symfony\Bridge\Doctrine\RegistryInterface
     */
    protected $doctrine;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @return Configuration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param Configuration $configuration
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return \Symfony\Bridge\Doctrine\RegistryInterface
     */
    public function getDoctrine()
    {
        return $this->doctrine;
    }

    /**
     * @param \Symfony\Bridge\Doctrine\RegistryInterface $doctrine
     */
    public function setDoctrine($doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * Konstruktor
     *
     * @param \Symfony\Bridge\Doctrine\RegistryInterface $doctrine
     * @param Configuration $configuration
     */
    public function __construct(\Symfony\Bridge\Doctrine\RegistryInterface $doctrine, Configuration $configuration)
    {
        $this->setDoctrine($doctrine);
        $this->setConfiguration($configuration);
    }

    /**
     * @param Entity\ScmPackage $scmPackage
     */
    public function pack(Entity\ScmPackage $scmPackage)
    {
        $packerType = $this->getPackerTypeBySeries($scmPackage->getSeries());

        switch ($packerType) {
            case self::PACKER_TYPE_BINARY:
                $packer = new BinaryPacker($this->getDoctrine(), $this->getConfiguration());
                break;

            case self::PACKER_TYPE_SQLITE3:
                $packer = new Sqlite3Packer($this->getDoctrine(), $this->getConfiguration());
                break;

            default:
                throw new \Exception(sprintf('unsupported packer-type=(%s)', $packerType));
        }

        return $packer->pack($scmPackage);
    }

    /**
     * @param $series
     * @return string
     */
    protected function getPackerTypeBySeries($series)
    {
        if (in_array(strtolower($series), array('c', 'd', 'e', 'f', 'h'))) {
            return self::PACKER_TYPE_BINARY;
        }

        if (in_array(strtolower($series), array('j'))) {
            return self::PACKER_TYPE_SQLITE3;
        }
    }
}