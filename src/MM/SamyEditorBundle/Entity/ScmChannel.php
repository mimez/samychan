<?php

namespace MM\SamyEditorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ScmChannel
 */
class ScmChannel
{
    /**
     * @var integer
     */
    private $scm_channel_id;

    /**
     * @var integer
     */
    private $channelNo;

    /**
     * @var string
     */
    private $name;

    /**
     * @var binary
     */
    private $data;

    /**
     * @var \MM\SamyEditorBundle\Entity\ScmFile
     */
    private $scmFile;


    /**
     * Get scm_channel_id
     *
     * @return integer 
     */
    public function getScmChannelId()
    {
        return $this->scm_channel_id;
    }

    /**
     * Set channelNo
     *
     * @param integer $channelNo
     * @return ScmChannel
     */
    public function setChannelNo($channelNo)
    {
        $this->channelNo = $channelNo;

        return $this;
    }

    /**
     * Get channelNo
     *
     * @return integer 
     */
    public function getChannelNo()
    {
        return $this->channelNo;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return ScmChannel
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set data
     *
     * @param binary $data
     * @return ScmChannel
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
     * Set scmFile
     *
     * @param \MM\SamyEditorBundle\Entity\ScmFile $scmFile
     * @return ScmChannel
     */
    public function setScmFile(\MM\SamyEditorBundle\Entity\ScmFile $scmFile = null)
    {
        $this->scmFile = $scmFile;

        return $this;
    }

    /**
     * Get scmFile
     *
     * @return \MM\SamyEditorBundle\Entity\ScmFile 
     */
    public function getScmFile()
    {
        return $this->scmFile;
    }
}
