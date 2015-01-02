<?php

namespace MM\SamyEditorBundle\Scm;

use MM\SamyEditorBundle\Entity\ScmChannel;
use MM\SamyEditorBundle\Entity\ScmPackage;
use MM\SamyEditorBundle\Entity\ScmFile;

class Parser {

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
     * Constructor
     *
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->setConfiguration($configuration);
    }

    /**
     * @param \SplFileObject $file
     * @return ScmPackage
     * @throws \Exception
     */
    public function load(\SplFileObject $file, $series = null)
    {
        // Scm files are zip archives. open / load the archive
        $zip = $this->openArchive($file->getRealPath());

        // detect the series (by cloneInfo)
        $series = isset($series) ? $series : $this->detectSeries($zip);

        // each series has a own config
        $config = $this->getConfiguration()->getConfigBySeries($series);

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
            $scmPackage->addFile($scmFile);

            if (!isset($config[$filename])) {
                continue;
            }

            $fileConfig = $config[$filename];

            $temp = new \SplTempFileObject();
            $temp->fwrite($scmFile->getData());
            $temp->rewind();
            while ($data = $temp->fread($fileConfig['byte_length']))
            {
                $scmChannel = new ScmChannel();
                $scmChannel->setData($data);
                $scmChannel->setScmFile($scmFile);
                $scmFile->addChannel($scmChannel);

                foreach ($fileConfig['fields'] as $fieldName => $fieldConfig) {
                    $value = $this->getValueFromByteString($data, $fieldConfig['offset'], $fieldConfig['length'], $fieldConfig['type']);
                    $setterMethod = 'set' . ucfirst($fieldName);
                    $scmChannel->{$setterMethod}($value);
                }
            }
        }

        return $scmPackage;
    }

    /**
     * Extract Value from ByteString
     *
     * @param string $byteString
     * @param integer $offset
     * @param integer $length
     * @param string $type
     *
     * @return mixed
     */
    protected function getValueFromByteString($byteString, $offset, $length, $type)
    {
        // extract bytes from data
        $value = substr($byteString, $offset, $length);

        // load $type and cast from binary
        $dataType = new $type;
        $value = $dataType->fromBinary($value);

        return $value;
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
}