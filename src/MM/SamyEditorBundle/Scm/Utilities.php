<?php

namespace MM\SamyEditorBundle\Scm;

use MM\SamyEditorBundle\Entity\ScmChannel;
class Utilities {

    /**
     * Binary Data des Channels aktualisieren
     *
     * @param ScmChannel $scmChannel
     */
    public function updateChannelData(ScmChannel $scmChannel)
    {
        $data = new \SplFileObject('php://memory', 'w+');
        $data->fwrite(stream_get_contents($scmChannel->getData()));
        $data->rewind();

        // channel-no aktualisieren
        $data->fwrite(pack('s', $scmChannel->getChannelNo()), 2);
        $data->rewind();

        // name aktualisieren
        // @todo

        // checksum aktualisieren
        $binaryChecksum = $this->calculateChecksum($data->fgets());
        $data->fseek(-2, SEEK_END);
        $data->fwrite($binaryChecksum, 2);
        $data->rewind();

        $scmChannel->setData($data->fgets());
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
        $checksum = 0;
        for ($i = 0; $i < 319; $i++)
        {

            $checksum += hexdec(bin2hex($data[$i]));
        }

        $checksum = base_convert($checksum, 10, 2); // Step 1: Konvertieren nach Binär
        $checksum = substr($checksum, -8); // nur das erste byte (von hinten) rausziehen
        $checksum = base_convert($checksum, 2, 10); // Zurueck nach Dezimal konvertieren
        $checksum = pack('n', $checksum); // checksum als binary 16 bit integer

        return $checksum;
    }
}