<?php

namespace MM\SamyChan\BackendBundle\Tests\Scm;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use MM\SamyChan\BackendBundle\Entity\ScmPackage;

class TransferManagerTest extends WebTestCase
{
    protected $em;

    public function setUp()
    {
        self::bootKernel();
    }

    public function testTransfer()
    {
        $scmSourcePackage = $this->loadPackage('j_cable.zip');
        $scmSourceFile = $scmSourcePackage->getFileByFilename('dvbc');

        // switch first two channels
        $sourceChannel1 = $scmSourceFile->getChannelByChannelNo(1);
        $targetChannel2 = $scmSourceFile->getChannelByChannelNo(2);
        $sourceChannel1->setChannelNo(2);
        $targetChannel2->setChannelNo(1);

        $scmTargetPackage = $this->loadPackage('j_cable.zip');
        $scmTargetFile = $scmTargetPackage->getFileByFilename('dvbc');
        $targetChannel1 = $scmTargetFile->getChannelByChannelNo(1);
        $targetChannel2 = $scmTargetFile->getChannelByChannelNo(2);

        $this->fixture = new \MM\SamyChan\BackendBundle\Scm\TransferManager();
        $changes = $this->fixture->transferChannelOrders($scmSourceFile, $scmTargetFile);

        $this->assertEquals(count($changes), 2, 'expected change-count(2) does not match');
        $this->assertEquals(2, $targetChannel1->getChannelNo());
        $this->assertEquals(1, $targetChannel2->getChannelNo());
    }

    public function testEqualFilesShouldntHaveAnyChanges()
    {
        $scmSourcePackage = $this->loadPackage('j_cable.zip');
        $scmSourceFile = $scmSourcePackage->getFileByFilename('dvbc');

        $scmTargetPackage = $this->loadPackage('j_cable.zip');
        $scmTargetFile = $scmTargetPackage->getFileByFilename('dvbc');

        $this->fixture = new \MM\SamyChan\BackendBundle\Scm\TransferManager();
        $changes = $this->fixture->transferChannelOrders($scmSourceFile, $scmTargetFile);
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