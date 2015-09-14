<?php

namespace MM\SamyChan\BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ScmPackage
 */
class ScmPackage
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $hash;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set hash
     *
     * @param string $hash
     * @return ScmPackage
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Get hash
     *
     * @return string 
     */
    public function getHash()
    {
        return $this->hash;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $files;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->files = new \Doctrine\Common\Collections\ArrayCollection();
        $this->setDemo(false);
    }

    /**
     * Add files
     *
     * @param \MM\SamyChan\BackendBundle\Entity\ScmFile $files
     * @return ScmPackage
     */
    public function addFile(\MM\SamyChan\BackendBundle\Entity\ScmFile $files)
    {
        $this->files[] = $files;

        return $this;
    }

    /**
     * Remove files
     *
     * @param \MM\SamyChan\BackendBundle\Entity\ScmFile $files
     */
    public function removeFile(\MM\SamyChan\BackendBundle\Entity\ScmFile $files)
    {
        $this->files->removeElement($files);
    }

    /**
     * Get files
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @var integer
     */
    private $scm_package_id;


    /**
     * Get scm_package_id
     *
     * @return integer 
     */
    public function getScmPackageId()
    {
        return $this->scm_package_id;
    }
    /**
     * @var string
     */
    private $filename;


    /**
     * Set filename
     *
     * @param string $filename
     * @return ScmPackage
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename
     *
     * @return string 
     */
    public function getFilename()
    {
        return $this->filename;
    }
    /**
     * @var string
     */
    private $series;


    /**
     * Set series
     *
     * @param string $series
     * @return ScmPackage
     */
    public function setSeries($series)
    {
        $this->series = $series;

        return $this;
    }

    /**
     * Get series
     *
     * @return string 
     */
    public function getSeries()
    {
        return $this->series;
    }


    /**
     * @param $filename
     *
     * @return mixed|ScmFile
     */
    public function getFileByFilename($filename)
    {
        foreach ($this->getFiles() as $scmFile) {
            if ($scmFile->getFilename() == $filename) {
                return $scmFile;
            }
        }

        return false;
    }

    /**
     * @var boolean
     */
    private $demo;

    /**
     * Set demo
     *
     * @param boolean $demo
     * @return ScmPackage
     */
    public function setDemo($demo)
    {
        $this->demo = $demo;

        return $this;
    }

    /**
     * Get demo
     *
     * @return boolean 
     */
    public function getDemo()
    {
        return $this->demo;
    }
}
