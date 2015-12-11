<?php

namespace MM\SamyChan\BackendBundle\Tests\Scm;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use MM\SamyChan\BackendBundle\Entity\ScmPackage;

class ImportManagerTest extends WebTestCase
{
    protected $em;

    public function setUp()
    {
        self::bootKernel();
    }

    public function testImportOrders()
    {
        $scmImportPackage = $this->loadPackage('j_cable.zip');
        $scmImportFile = $scmImportPackage->getFileByFilename('dvbc');

        // switch first two channels
        $importChannel1 = $scmImportFile->getChannelByChannelNo(1);
        $channel2 = $scmImportFile->getChannelByChannelNo(2);
        $importChannel1->setChannelNo(2);
        $channel2->setChannelNo(1);

        $scmPackage = $this->loadPackage('j_cable.zip');
        $scmFile = $scmPackage->getFileByFilename('dvbc');
        $channel1 = $scmFile->getChannelByChannelNo(1);
        $channel2 = $scmFile->getChannelByChannelNo(2);

        $this->fixture = new \MM\SamyChan\BackendBundle\Scm\ImportManager();
        $changes = $this->fixture->importChannelOrders($scmFile, $scmImportFile);

        $this->assertEquals(count($changes), 2, 'expected change-count(2) does not match');
        $this->assertEquals(2, $channel1->getChannelNo());
        $this->assertEquals(1, $channel2->getChannelNo());
    }

    public function testEqualFilesShouldntHaveAnyChanges()
    {
        $scmImportPackage = $this->loadPackage('j_cable.zip');
        $scmImportFile = $scmImportPackage->getFileByFilename('dvbc');

        $scmPackage = $this->loadPackage('j_cable.zip');
        $scmFile = $scmPackage->getFileByFilename('dvbc');

        $this->fixture = new \MM\SamyChan\BackendBundle\Scm\ImportManager();
        $changes = $this->fixture->importChannelOrders($scmFile, $scmImportFile);
        $this->assertEquals(count($changes), 0);
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
        $path = static::$kernel->locateResource('@MMSamyChanBackendBundle/Tests/Resources/testdata/' . $filename);
        $file = new \SplFileObject($path);

        // get parser
        $parser = static::$kernel->getContainer()->get('mm_samy_editor.scm_parser');

        return $parser->load($file);
    }
}