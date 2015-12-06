<?php
namespace MM\SamyChan\BackendBundle\Tests\Scm;

use MM\SamyChan\BackendBundle\Scm\SeriesDetector;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SeriesDetectorTest extends WebTestCase
{
    public function setUp()
    {
        self::bootKernel();
        $this->_fixture = new SeriesDetector();
    }

    public function testJSeriesDetected()
    {
        $zip = $this->loadZip('j_cable.zip');
        $series = $this->_fixture->detectSeries($zip);

        $this->assertEquals('J', $series);
    }

    public function testHSeriesDetected()
    {
        $zip = $this->loadZip('h_cable.scm');
        $series = $this->_fixture->detectSeries($zip);

        $this->assertEquals('H', $series);
    }

    public function testESeriesDetected()
    {
        $zip = $this->loadZip('e_sat.scm');
        $series = $this->_fixture->detectSeries($zip);

        $this->assertEquals('E', $series);
    }

    public function testJSeriesScmAsHSeriesDetected()
    {
        $zip = $this->loadZip('j_cable.scm');
        $series = $this->_fixture->detectSeries($zip);

        $this->assertEquals('H', $series);
    }

    /**
     * ZipLoader
     *
     * @param string $filename
     * @return \ZipArchive
     */
    protected function loadZip($filename)
    {
        $zip = new \ZipArchive;
        $zip->open(static::$kernel->locateResource('@MMSamyChanBackendBundle/Tests/Resources/testdata/' . $filename));

        return $zip;
    }
}