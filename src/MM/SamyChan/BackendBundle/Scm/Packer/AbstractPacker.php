<?php

namespace MM\SamyChan\BackendBundle\Scm\Packer;

use MM\SamyChan\BackendBundle\Entity;
use Symfony\Component\Yaml;
use MM\SamyChan\BackendBundle\Scm\Configuration;

abstract class AbstractPacker {

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
     * @param ScmPackage $scmPackage
     * @param Configuration $configuration
     */
    public function __construct(\Symfony\Bridge\Doctrine\RegistryInterface $doctrine, Configuration $configuration)
    {
        $this->setDoctrine($doctrine);
        $this->setConfiguration($configuration);
    }

    /**
     * SCM packen
     * @return string
     */
    public function pack(Entity\ScmPackage $scmPackage)
    {

        // tempname
        $filepath = tempnam('/tmp/', 'MM');

        // zip-archive erstellen
        $zip = new \ZipArchive;
        $zip->open($filepath, \ZipArchive::CREATE);

        // Dateien des SCM-Packages durchlaufen und der ZIP-Datei hinzufuegen
        foreach ($scmPackage->getFiles() as $file)
        {
            $newBinaryData = $this->assambleFileData($file);
            $zip->addFromString($file->getFilename(), $newBinaryData);
        }

        $zip->close();

        // content lesen und die temp-datei loeschen
        $content = file_get_contents($filepath);
        unlink($filepath);

        return $content;
    }

    abstract protected function assambleFileData(Entity\ScmFile $scmFile);
}