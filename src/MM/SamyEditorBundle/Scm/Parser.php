<?php

namespace MM\SamyEditorBundle\Scm;

use MM\SamyEditorBundle\Entity\ScmChannel;
use MM\SamyEditorBundle\Entity\ScmPackage;
use MM\SamyEditorBundle\Entity\ScmFile;
use MM\SamyEditorBundle\Database\StringLoader;

class Parser {

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var SeriesDetector
     */
    protected $seriesDetector;

    /**
     * @return SeriesDetector
     */
    public function getSeriesDetector()
    {
        return $this->seriesDetector;
    }

    /**
     * @param SeriesDetector $seriesDetector
     */
    public function setSeriesDetector($seriesDetector)
    {
        $this->seriesDetector = $seriesDetector;
    }

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
    public function __construct(Configuration $configuration, SeriesDetector $seriesDetector)
    {
        $this->setConfiguration($configuration);
        $this->setSeriesDetector($seriesDetector);
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
        $series = isset($series) ? $series : $this->getSeriesDetector()->detectSeries($zip);

        // each series has a own config
        $config = $this->getConfiguration()->getConfigBySeries($series);

        return $this->buildScmPackage($zip, $config, $file->getFilename(), $series);
    }

    /**
     * ScmPackage bauen
     *
     * @param \ZipArchive $zip
     * @param array $config
     * @param string $packageFilename
     * @param string $series
     *
     * @return ScmPackage
     */
    protected function buildScmPackage(\ZipArchive $zip, array $config, $packageFilename, $series)
    {
        // create base scm-package
        $scmPackage = new ScmPackage();
        $scmPackage->setHash(uniqid()); // unique hash as access-token
        $scmPackage->setFilename($packageFilename);
        $scmPackage->setSeries($series);

        // loop over the files of the zip and add them to the scmPackage
        for ($index = 0; $zip->getFromIndex($index); $index++) {

            $filename = $zip->getNameIndex($index);

            // create scmFile and link it to scmPackage
            $scmFile = new ScmFile();
            $scmFile->setFilename($filename);
            $scmFile->setData($zip->getFromIndex($index));
            $scmFile->setScmPackage($scmPackage);
            $scmPackage->addFile($scmFile);

            // if we dont have a config for this file, skip further parsing
            if (!isset($config[$filename])) {
                continue;
            }

            // parse the Channels of this file
            $scmChannels = $this->getChannelsByFile($zip->getFromIndex($index), $config[$filename]);

            foreach ($scmChannels as $scmChannel) {
                $scmChannel->setScmFile($scmFile);
                $scmFile->addChannel($scmChannel);
            }
        }

        return $scmPackage;
    }

    /**
     * Get all Channels from a binaryString
     *
     * @param string $binaryFile
     * @param array $fileConfig
     *
     * @return array with ScmChannel Objects
     *
     * @throws \Exception
     */
    protected function getChannelsByFile($binaryFile, $fileConfig)
    {
        switch ($fileConfig['type']) {
            case 'sqlite3':

                return $this->getChannelsFromSqlite($binaryFile, $fileConfig);

            case 'binary':

                return $this->getChannelsFromBinary($binaryFile, $fileConfig);

            default:
                throw new \Exception(sprintf('type=(%s) not supported', $fileConfig['type']));
        }
    }

    /**
     * Channels from binary
     *
     * @param string $binary
     * @param array $fileConfig
     *
     * @return array ScmChannel Objects
     */
    protected function getChannelsFromBinary($binary, $fileConfig)
    {
        $scmChannels = array();

        $temp = new \SplTempFileObject();
        $temp->fwrite($binary);
        $temp->rewind();
        while ($data = $temp->fread($fileConfig['byte_length'])) {
            $scmChannel = new ScmChannel();
            $scmChannel->setData($data);

            foreach ($fileConfig['fields'] as $fieldName => $fieldConfig) {
                $value = $this->getValueFromByteString($data, $fieldConfig['offset'], $fieldConfig['length'], $fieldConfig['type']);
                $setterMethod = 'set' . ucfirst($fieldName);
                $scmChannel->{$setterMethod}($value);
            }

            $scmChannels[] = $scmChannel;
        }

        return $scmChannels;
    }

    /**
     * Channels from Sqlite3
     *
     * @param string $binarySqlite
     * @param array $fileConfig
     * @return array ScmChannel Objects
     */
    protected function getChannelsFromSqlite($binarySqlite, $fileConfig)
    {
        $scmChannels = array();

        // load database
        $db = new Sqlite3Database($binarySqlite);
        $pdo = $db->getPdo();

        // query database
        $sth = $pdo->query($fileConfig['channelSqlQuery']);

        // iterate over the channels and create ScmChannel-Objects
        foreach ($sth->fetchAll() as $channel) {
            $scmChannel = new ScmChannel();
            $scmChannel->setData(json_encode($channel));

            foreach ($fileConfig['fields'] as $fieldName => $fieldConfig) {
                $type = new $fieldConfig['type'];
                $value = $type->fromBinary($channel[$fieldName]);
                $setterMethod = 'set' . ucfirst($fieldName);
                $scmChannel->{$setterMethod}($value);
            }

            $scmChannels[] = $scmChannel;
        }

        return $scmChannels;
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