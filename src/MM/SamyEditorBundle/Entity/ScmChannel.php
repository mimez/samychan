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
    /**
     * @var \DateTime
     */
    private $updatedAt;


    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return ScmChannel
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
    /**
     * @var integer
     */
    private $fav1sort;

    /**
     * @var integer
     */
    private $fav2sort;

    /**
     * @var integer
     */
    private $fav3sort;

    /**
     * @var integer
     */
    private $fav4sort;

    /**
     * @var integer
     */
    private $fav5sort;


    /**
     * Set fav1sort
     *
     * @param integer $fav1sort
     * @return ScmChannel
     */
    public function setFav1sort($fav1sort)
    {
        $this->fav1sort = $fav1sort;

        return $this;
    }

    /**
     * Get fav1sort
     *
     * @return integer 
     */
    public function getFav1sort()
    {
        return $this->fav1sort;
    }

    /**
     * Set fav2sort
     *
     * @param integer $fav2sort
     * @return ScmChannel
     */
    public function setFav2sort($fav2sort)
    {
        $this->fav2sort = $fav2sort;

        return $this;
    }

    /**
     * Get fav2sort
     *
     * @return integer 
     */
    public function getFav2sort()
    {
        return $this->fav2sort;
    }

    /**
     * Set fav3sort
     *
     * @param integer $fav3sort
     * @return ScmChannel
     */
    public function setFav3sort($fav3sort)
    {
        $this->fav3sort = $fav3sort;

        return $this;
    }

    /**
     * Get fav3sort
     *
     * @return integer 
     */
    public function getFav3sort()
    {
        return $this->fav3sort;
    }

    /**
     * Set fav4sort
     *
     * @param integer $fav4sort
     * @return ScmChannel
     */
    public function setFav4sort($fav4sort)
    {
        $this->fav4sort = $fav4sort;

        return $this;
    }

    /**
     * Get fav4sort
     *
     * @return integer 
     */
    public function getFav4sort()
    {
        return $this->fav4sort;
    }

    /**
     * Set fav5sort
     *
     * @param integer $fav5sort
     * @return ScmChannel
     */
    public function setFav5sort($fav5sort)
    {
        $this->fav5sort = $fav5sort;

        return $this;
    }

    /**
     * Get fav5sort
     *
     * @return integer 
     */
    public function getFav5sort()
    {
        return $this->fav5sort;
    }
}
