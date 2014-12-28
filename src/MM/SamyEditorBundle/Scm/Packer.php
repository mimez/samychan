<?php

namespace MM\SamyEditorBundle\Scm;

use MM\SamyEditorBundle\Entity;

class Packer {

    /**
     * @var \Symfony\Bridge\Doctrine\RegistryInterface
     */
    protected $doctrine;

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
     */
    public function __construct(\Symfony\Bridge\Doctrine\RegistryInterface $doctrine)
    {
        $this->setDoctrine($doctrine);
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
            if ($file->getFilename() == 'map-AstraHDPlusD') {
                #echo bin2hex($this->assambleFileData($file));die;
            }
            $zip->addFromString($file->getFilename(), $this->assambleFileData($file));
        }

        $zip->close();

        // content lesen und die temp-datei loeschen
        $content = file_get_contents($filepath);
        unlink($filepath);

        return $content;
    }

    /**
     * assamble FileData
     *
     * @param Entity\ScmFile $scmFile
     * @return string
     */
    protected function assambleFileData(Entity\ScmFile $scmFile)
    {
        $scmChannels = $this->getDoctrine()->getRepository('MM\SamyEditorBundle\Entity\ScmChannel')->findBy(
            array('scmFile' => $scmFile),
            array('scm_channel_id' => 'ASC')
        );

        // if we dont have any channels, return the original binary data
        if (count($scmChannels) == 0) {
            return stream_get_contents($scmFile->getData());
        }

        // update binary data of the channel and return the whole data
        $data = '';

        foreach ($scmChannels as $scmChannel) {
            $this->updateChannelData($scmChannel);
            $data .= is_resource($scmChannel->getData()) ? stream_get_contents($scmChannel->getData()) : $scmChannel->getData();
        }

        return $data;
    }

    /**
     * Binary Data des Channels aktualisieren
     *
     * @param ScmChannel $scmChannel
     */
    public function updateChannelData(Entity\ScmChannel $scmChannel)
    {
        // check if the channel is a real channel
        if ($scmChannel->getChannelNo() == 0) {
            return;
        }

        $originalData = stream_get_contents($scmChannel->getData());

        $data = new \SplFileObject('php://memory', 'w+');
        $data->fwrite($originalData);
        $data->rewind();

        // channel-no aktualisieren
        $data->fwrite(pack('s', $scmChannel->getChannelNo()), 2);
        $data->rewind();

        // name aktualisieren
        // @todo

        // checksum aktualisieren
        $binaryChecksumByte = $this->calculateChecksum($data->fread(strlen($originalData)));
        $data->fseek(-1, SEEK_END);
        $data->fwrite($binaryChecksumByte, 1);
        $data->rewind();

        $scmChannel->setData($data->fread(strlen($originalData)));
    }

    /**
     * Checksum berechnen
     * Checksumme wird ueber alle bytes (bis auf die letzten 2) berechnet
     * es ist eine byte-addition, das ist in php bissi scheisse, daher workaround
     *
     * @param $data
     *
     * @return string $checksum binary
     */
    public function calculateChecksum($data)
    {
        $checksumByteOffset = strlen($data) - 1; // the last 2 bytes

        $checksum = 0;
        for ($i = 0; $i < $checksumByteOffset; $i++)
        {
            $checksum += hexdec(bin2hex($data[$i]));
        }

        $checksum = base_convert($checksum, 10, 2); // Step 1: Konvertieren nach Binär
        $checksum = substr($checksum, -8); // nur das erste byte (von hinten) rausziehen
        $checksum = base_convert($checksum, 2, 16); // Zurueck nach Hex konvertieren
        $checksum = str_pad($checksum, 2, '0', STR_PAD_LEFT);

        return hex2bin($checksum);
    }
}