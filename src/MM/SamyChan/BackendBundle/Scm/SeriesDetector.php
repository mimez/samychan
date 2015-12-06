<?php
namespace MM\SamyChan\BackendBundle\Scm;

class SeriesDetector
{
    /**
     * Detect the Series-Char
     *
     * @param \ZipArchive $archive
     *
     * @return bool|string $seriesChar
     *
     * @throws \Exception
     */
    public function detectSeries(\ZipArchive $archive)
    {
        // J-Series
        $series = $this->detectSeriesByMetaDataXml($archive);

        if (false == $series) {
            $series = $this->detectSeriesByCloneInfo($archive);
        }

        if (false == $series) {
            throw new \Exception('cannot detect series');
        }

        return $series;
    }

    /**
     * Detect the series by the metadata.xml (J-Series
     *
     * @param \ZipArchive $archive
     * @return bool|string
     */
    protected function detectSeriesByMetaDataXml(\ZipArchive $archive)
    {
        $metadataXml = $archive->getFromName('metadata.xml');

        if (false == $metadataXml) {
            return false;
        }

        return 'J';
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

        // we need at least 9 bytes
        if (strlen($cloneInfo) < 9) {
            return false;
        }

        // 8th char is the series
        $series = strtoupper($cloneInfo[8]);

        if ($series == 'B') {
            // 2013 B-series uses E/F-series format
            $series = 'F';
        }

        // A File with CloneInfo is the old scm-format. If we get a series greater then H, return H
        // (some J-Series TVs has still the old scm-format)
        $series = ord($series) > ord('H') ? 'H' : $series;

        return $series;
    }
}