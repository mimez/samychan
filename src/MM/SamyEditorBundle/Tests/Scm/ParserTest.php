<?php

namespace MM\SamyEditorBundle\Tests\Scm;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use MM\SamyEditorBundle\Entity\ScmPackage;

class ParserTest extends WebTestCase
{
    protected $em;

    public function setUp()
    {
        self::bootKernel();
    }

    public function testJSeriesCableCanBeLoaded()
    {
        $scmPackage = $this->loadPackage('j_cable.zip');
        $zip = $this->loadZip('j_cable.zip');

        $this->assertScmPackage($scmPackage, $zip, array(
            'files' => array(
                'dvbc' => array(
                    'channel_count' => 540,
                    'channels' => array(
                        1 => array('name' => 'Das Erste HD'),
                        409 => array('name' => 'Power Türk TV'),
                        10004 => array('name' => 'STB channel config.')
                    )
                )
            )
        ));
    }

    public function testJSeriesSatCanBeLoaded()
    {
        $scmPackage = $this->loadPackage('j_sat.zip');
        $zip = $this->loadZip('j_sat.zip');

        $this->assertScmPackage($scmPackage, $zip, array(
            'files' => array(
                'dvbs' => array('channel_count' => 1468)
            )
        ));
    }

    public function testHSeriesCableCanBeLoaded()
    {
        $scmPackage = $this->loadPackage('h_cable.scm');
        $zip = $this->loadZip('h_cable.scm');

        $this->assertScmPackage($scmPackage, $zip, array(
            'files' => array(
                'map-CableD' => array('channel_count' => 1000)
            )
        ));
    }

    public function testHSeriesSatCanBeLoaded()
    {
        $scmPackage = $this->loadPackage('h_sat.scm');
        $zip = $this->loadZip('h_sat.scm');

        $this->assertScmPackage($scmPackage, $zip, array(
            'files' => array(
                'map-SateD' => array('channel_count' => 2000)
            )
        ));
    }

    public function testFSeriesSatCanBeLoaded()
    {
        $scmPackage = $this->loadPackage('f_sat.scm');
        $zip = $this->loadZip('f_sat.scm');

        $this->assertScmPackage($scmPackage, $zip, array(
            'files' => array(
                'map-SateD' => array('channel_count' => 2000)
            )
        ));
    }

    public function testESeriesSatCanBeLoaded()
    {
        $scmPackage = $this->loadPackage('e_sat.scm');
        $zip = $this->loadZip('e_sat.scm');

        $this->assertScmPackage($scmPackage, $zip, array(
            'files' => array(
                'map-SateD' => array('channel_count' => 1000, 'channels' => array(1 => array('name' => 'Das Erste HD'))),
                'map-AirA' => array('channel_count' => 1000),
                'map-AirD' => array('channel_count' => 1000, 'channels' => array(15 => array('name' => 'Südwest'))),
                'map-AstraHDPlusD' => array('channel_count' => 1000, 'channels' => array(124 => array('name' => 'SR SÜDWEST Ferns.'))),
            )
        ));
    }

    public function testDSeriesSatCanBeLoaded()
    {
        $scmPackage = $this->loadPackage('d_sat.scm');
        $zip = $this->loadZip('d_sat.scm');

        $this->assertScmPackage($scmPackage, $zip, array(
            'files' => array(
                'map-SateD' => array('channel_count' => 3000, 'channels' => array(18 => array('name' => 'Bayerisches FS Süd'))),
                'map-AirA' => array('channel_count' => 3000, 'channels' => array(3 => array('name' => 'RTL'))),
                'map-AstraHDPlusD' => array('channel_count' => 1000, 'channels' => array(11 => array('name' => 'Eurosport'))),
                'map-CableD' => array('channel_count' => 1000, 'channels' => array(3 => array('name' => 'Bayerisches FS Süd'))),
            )
        ));
    }

    /**
     * @param ScmPackage $scmPackage
     * @param \ZipArchive $zip
     * @param $config
     */
    protected function assertScmPackage(ScmPackage $scmPackage, \ZipArchive $zip, $config)
    {
        // file cound should be equal
        $this->assertEquals($zip->numFiles, count($scmPackage->getFiles()));

        // check if each file exists
        foreach ($scmPackage->getFiles() as $scmFile) {
            $this->assertTrue($zip->statName($scmFile->getFilename()) !== false);
        }

        // check files by passed fileconfig
        foreach ($config['files'] as $filename => $fileconfig) {
            $scmFile = $scmPackage->getFileByFilename($filename);
            $this->assertEquals($fileconfig['channel_count'], count($scmFile->getChannels()));

            if (!isset($fileconfig['channels'])) {
                continue;
            }

            // compare channels
            foreach ($fileconfig['channels'] as $channelNo => $fields) {
                $scmChannel = $scmFile->getChannelByChannelNo($channelNo);
                foreach ($fields as $fieldName => $expectedValue) {
                    $this->assertEquals($expectedValue, $scmChannel->{'get' . ucfirst($fieldName)}());
                }
            }
        }
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
        $zip->open(static::$kernel->locateResource('@MMSamyEditorBundle/Tests/Resources/testdata/' . $filename));

        return $zip;
    }

    /**
     * Load Package
     *
     * @param string $filename
     * @return ScmPackage $scmPackage
     */
    protected function loadPackage($filename)
    {
        // load file
        $path = static::$kernel->locateResource('@MMSamyEditorBundle/Tests/Resources/testdata/' . $filename);
        $file = new \SplFileObject($path);

        // get parser
        $parser = static::$kernel->getContainer()->get('mm_samy_editor.scm_parser');

        return $parser->load($file);
    }
}