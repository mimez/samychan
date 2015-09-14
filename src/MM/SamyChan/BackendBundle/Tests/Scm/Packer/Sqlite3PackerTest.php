<?php

namespace MM\SamyChan\BackendBundle\Tests\Scm\Packer;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use MM\SamyChan\BackendBundle\Entity\ScmPackage;

class Sqlite3PackerTest extends WebTestCase
{
    protected $em;

    public function setUp()
    {
        self::bootKernel();
    }

    public function testPackingJSeriesWorks()
    {
        $scmPackage = $this->loadPackage('j_cable.zip');
        $dvbc = $scmPackage->getFileByFilename('dvbc');
        $channelArd = $dvbc->getChannelByChannelNo(1);
        $channelRtl = $dvbc->getChannelByChannelNo(3);
        $channelArd->setChannelNo(3);
        $channelRtl->setChannelNo(1);

        $packer = static::$kernel->getContainer()->get('mm_samy_editor.scm_packer');
        $binaryScmPackage = $packer->pack($scmPackage);

        $path = tempnam('/tmp', 'MM');
        file_put_contents($path, $binaryScmPackage);
        $scmPackage2 = static::$kernel->getContainer()->get('mm_samy_editor.scm_parser')->load(new \SplFileObject($path));
        unlink($path);
        $this->assertScmPackagesEqual($scmPackage, $scmPackage2);
    }

    protected function assertScmPackagesEqual(ScmPackage $expected, ScmPackage $actual)
    {
        foreach ($expected->getFiles() as $expectedFile) {
            $actualFile = $actual->getFileByFilename($expectedFile->getFilename());

            // compare filenames
            $this->assertEquals($expectedFile->getFilename(), $actualFile->getFilename());

            // compare channel-count of this file
            $this->assertEquals(count($expectedFile->getChannels()), count($actualFile->getChannels()));

            foreach ($expectedFile->getChannels() as $expectedChannel) {
                $actualChannel = $actualFile->getChannelByChannelNo($expectedChannel->getChannelNo());

                // compare names
                $this->assertEquals($expectedChannel->getName(), $actualChannel->getName());
            }
        }
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