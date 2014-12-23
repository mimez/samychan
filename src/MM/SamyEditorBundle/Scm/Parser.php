<?php

namespace MM\SamyEditorBundle\Scm;

use MM\SamyEditorBundle\Entity\ScmChannel;
use MM\SamyEditorBundle\Entity\ScmPackage;
use MM\SamyEditorBundle\Entity\ScmFile;

class Parser {

    protected $channelParserConfig = array(
        'map-CableD' => array(
            'byte_length' => '320',
            'unpack_format' => 'S1ChannelNo/@64/A200Label'
        )
    );

    public function loadFromPath($path)
    {

    }

    /**
     * @param \SplFileObject $file
     * @return ScmPackage
     * @throws \Exception
     */
    public function load(\SplFileObject $file)
    {
        $zip = $this->openArchive($file->getRealPath());

        $scmPackage = new ScmPackage();
        $scmPackage->setHash(uniqid());
        $scmPackage->setFilename($file->getFilename());

        for ($index = 0; $zip->getFromIndex($index); $index++)
        {
            $filename = $zip->getNameIndex($index);
            $scmFile = new ScmFile();
            $scmFile->setFilename($filename);
            $scmFile->setData($zip->getFromIndex($index));
            $scmFile->setScmPackage($scmPackage);

            if (isset($this->channelParserConfig[$filename]))
            {
                $config = $this->channelParserConfig[$filename];

                $temp = new \SplTempFileObject();
                $temp->fwrite($scmFile->getData());
                $temp->rewind();
                while ($data = $temp->fread($config['byte_length']))
                {
                    $scmChannel = new ScmChannel();
                    $unpackedData = unpack($config['unpack_format'], $data);
                    $scmChannel->setChannelNo($unpackedData['ChannelNo']);
                    $scmChannel->setName($this->convertString($unpackedData['Label']));
                    $scmChannel->setData($data);
                    $scmChannel->setScmFile($scmFile);
                    $scmFile->addChannel($scmChannel);
                }
            }

            $scmPackage->addFile($scmFile);
        }

        return $scmPackage;
    }

    /**
     * String konvertieren
     * @param $string
     * @return string
     */
    protected function convertString($string)
    {
        return trim(mb_convert_encoding($string, 'utf-8', 'utf-16'));
    }

    /**
     * ZIP Archive Oeffnen
     *
     * @param $path
     * @return \ZipArchive
     * @throws \Exception
     */
    protected function openArchive($path)
    {
        $zip = new \ZipArchive;
        $res = $zip->open($path);

        if (!$res)
        {
            throw new \Exception('cannot open zip archive');
        }

        return $zip;
    }
}