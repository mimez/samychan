<?php

namespace MM\SamyEditorBundle\Scm;

use MM\SamyEditorBundle\Entity\ScmChannel;
use MM\SamyEditorBundle\Entity\ScmPackage;
use MM\SamyEditorBundle\Entity\ScmFile;

class Packer {

    protected $scmPackage;

    /**
     * @return ScmPackage
     */
    public function getScmPackage()
    {
        return $this->scmPackage;
    }

    /**
     * @param ScmPackage $scmPackage
     */
    public function setScmPackage($scmPackage)
    {
        $this->scmPackage = $scmPackage;
    }

    /**
     * Konstruktor
     *
     * @param ScmPackage $scmPackage
     */
    public function __construct(ScmPackage $scmPackage)
    {
        $this->scmPackage = $scmPackage;
    }

    /**
     * SCM packen
     * @return string
     */
    public function getScm()
    {

        // tempname
        $filepath = tempnam('/tmp/', 'MM');

        // zip-archive erstellen
        $zip = new \ZipArchive;
        $zip->open($filepath, \ZipArchive::CREATE);

        // Dateien des SCM-Packages durchlaufen und der ZIP-Datei hinzufuegen
        foreach ($this->getScmPackage()->getFiles() as $file)
        {
            $zip->addFromString($file->getFilename(), stream_get_contents($file->getData()));
        }

        $zip->close();

        // content lesen und die temp-datei loeschen
        $content = file_get_contents($filepath);
        unlink($filepath);

        return $content;
    }
}