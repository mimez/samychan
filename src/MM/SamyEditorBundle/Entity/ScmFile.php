<?php

namespace MM\SamyEditorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ScmFile
 */
class ScmFile
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var binary
     */
    private $data;


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
     * Set filename
     *
     * @param string $filename
     * @return ScmFile
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
     * Set data
     *
     * @param binary $data
     * @return ScmFile
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return binary 
     */
    public function getData()
    {
        return $this->data;
    }
    /**
     * @var \MM\SamyEditorBundle\Entity\ScmPackage
     */
    private $scmPackage;


    /**
     * Set scmPackage
     *
     * @param \MM\SamyEditorBundle\Entity\ScmPackage $scmPackage
     * @return ScmFile
     */
    public function setScmPackage(\MM\SamyEditorBundle\Entity\ScmPackage $scmPackage = null)
    {
        $this->scmPackage = $scmPackage;

        return $this;
    }

    /**
     * Get scmPackage
     *
     * @return \MM\SamyEditorBundle\Entity\ScmPackage 
     */
    public function getScmPackage()
    {
        return $this->scmPackage;
    }
    /**
     * @var integer
     */
    private $scm_file_id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $channels;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->channels = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get scm_file_id
     *
     * @return integer 
     */
    public function getScmFileId()
    {
        return $this->scm_file_id;
    }

    /**
     * Add channels
     *
     * @param \MM\SamyEditorBundle\Entity\ScmChannel $channels
     * @return ScmFile
     */
    public function addChannel(\MM\SamyEditorBundle\Entity\ScmChannel $channels)
    {
        $this->channels[] = $channels;

        return $this;
    }

    /**
     * Remove channels
     *
     * @param \MM\SamyEditorBundle\Entity\ScmChannel $channels
     */
    public function removeChannel(\MM\SamyEditorBundle\Entity\ScmChannel $channels)
    {
        $this->channels->removeElement($channels);
    }

    /**
     * Get channels
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * Channel by Channel-No
     *
     * @param integer $channelNo
     * @return bool|ScmChannel
     */
    public function getChannelByChannelNo($channelNo)
    {
        foreach ($this->getChannels() as $scmChannel) {
            if ($scmChannel->getChannelNo() == $channelNo) {
                return $scmChannel;
            }
        }

        return false;
    }
}
