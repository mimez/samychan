<?php
namespace MM\SamyChan\BackendBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PackagesArchiveCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('samychan:packages:archive')
            ->setDescription('cleans up the database and archives old packages')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $scmPacker = $this->getApplication()->getKernel()->getContainer()->get('mm_samy_editor.scm_packer');
        $em = $this->getApplication()->getKernel()->getContainer()->get('doctrine')->getManager();
        $packagesDir = $this->getApplication()->getKernel()->getRootDir() . '/data/packages';
        if (!is_dir($packagesDir)) {
            throw new \Exception(sprintf('path=(%s) does not exist', $packagesDir));
        }

        $qb = $em->createQueryBuilder();
        $result = $qb->select('COUNT(p) AS cnt')
            ->from('MM\SamyChan\BackendBundle\Entity\ScmPackage' , 'p')
            ->where('p.isArchived = false')
            ->getQuery()
            ->getOneOrNullResult();
        if ($result['cnt'] <= 10000) {
            $output->writeln('We have just 10000 records or less, therefore we will do nothing');
            return;
        }

        $query = $em->createQuery('SELECT p FROM MM\SamyChan\BackendBundle\Entity\ScmPackage p ORDER BY p.scm_package_id')->setMaxResults(100);
        $scmPackages = $query->getResult();
        foreach ($scmPackages as $scmPackage) {
            $output->writeln(sprintf('archiving package=(%s)', $scmPackage->getHash()));
            file_put_contents($packagesDir . '/' . $scmPackage->getHash(), $scmPacker->pack($scmPackage));
            foreach ($scmPackage->getFiles() as $scmFile) {
                $em->remove($scmFile);
            }
            $scmPackage->setIsArchived(true);
            $em->persist($scmPackage);
            $em->flush();
        }
    }
}