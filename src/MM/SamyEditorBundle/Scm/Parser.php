<?php

namespace MM\SamyEditorBundle\Scm;

use MM\SamyEditorBundle\Entity\ScmChannel;
use MM\SamyEditorBundle\Entity\ScmPackage;
use MM\SamyEditorBundle\Entity\ScmFile;

class Parser {

    protected $channelParserConfig = array(
        'D' => array(
            'map-CableD' => array(
                'byte_length' => '320',
                'unpack_format' => 'S1ChannelNo/@64/A200Label'
            ),
            'map-SateD' => array(
                'byte_length' => '172',
                'unpack_format' => 'S1ChannelNo/@36/A100Label'
            ),
            'map-AstraHDPlusD' => array(
                'byte_length' => '212',
                'unpack_format' => 'S1ChannelNo/@48/A100Label'
            )
        ),
        'H' => array(
            'map-CableD' => array(
                'byte_length' => '320',
                'unpack_format' => 'S1ChannelNo/@64/A200Label'
            ),
            'map-SateD' => array(
                'byte_length' => '168',
                'unpack_format' => 'S1ChannelNo/@36/A100Label'
            ),
            'map-AstraHDPlusD' => array(
                'byte_length' => '212',
                'unpack_format' => 'S1ChannelNo/@48/A100Label'
            )
        ),
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
        // Scm files are zip archives. open / load the archive
        $zip = $this->openArchive($file->getRealPath());

        // detect the series (by cloneInfo)
        $series = $this->detectSeries($zip);

        // each series has a own config
        $config = $this->getConfigBySeries($series);

        // create base scm-package
        $scmPackage = new ScmPackage();
        $scmPackage->setHash(uniqid()); // unique hash as access-token
        $scmPackage->setFilename($file->getFilename());
        $scmPackage->setSeries($series);

        for ($index = 0; $zip->getFromIndex($index); $index++)
        {
            $filename = $zip->getNameIndex($index);
            $scmFile = new ScmFile();
            $scmFile->setFilename($filename);
            $scmFile->setData($zip->getFromIndex($index));
            $scmFile->setScmPackage($scmPackage);

            if (isset($config[$filename]))
            {
                $fileConfig = $config[$filename];

                $temp = new \SplTempFileObject();
                $temp->fwrite($scmFile->getData());
                $temp->rewind();
                while ($data = $temp->fread($fileConfig['byte_length']))
                {
                    $scmChannel = new ScmChannel();
                    $unpackedData = unpack($fileConfig['unpack_format'], $data);
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
     * Detect the Series-Char
     *
     * @param \ZipArchive $archive
     * @return bool|string $seriesChar
     */
    protected function detectSeries(\ZipArchive $archive)
    {
        $series = $this->detectSeriesByCloneInfo($archive);

        if (false == $series) {
            throw new \Exception('cannot detect series');
        }

        return $series;
    }

    /**
     * Detect the series by the cloneinfo
     *
     * @param \ZipArchive $archive
     * @return bool|string
     */
    protected function detectSeriesByCloneInfo(\ZipArchive $archive)
    {
        $cloneInfo = $archive->getFromName('CloneInfo');

        // cloneInfo not found
        if (false == $cloneInfo) {
            return false;
        }

        // wir brauchen mind. 9 bytes
        if (strlen($cloneInfo) < 9) {
            return false;
        }

        // 8th char is the series
        $series = strtoupper($cloneInfo[8]);

        if ($series == 'B') {
            // 2013 B-series uses E/F-series format
            $series = 'F';
        }

        return $series;
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

        if (true !== $res)
        {
            throw new \Exception('cannot open zip archive');
        }

        return $zip;
    }

    /**
     * config by series
     *
     * @param $series
     * @return mixed
     * @throws \Exception
     */
    protected function getConfigBySeries($series) {
        if (!isset($this->channelParserConfig[$series])) {
            throw new \Exception(sprintf('requested config for series=(%s) does not exist', $series));
        }

        return $this->channelParserConfig[$series];
    }
}