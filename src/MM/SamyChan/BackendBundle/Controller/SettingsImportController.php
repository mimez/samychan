<?php
namespace MM\SamyChan\BackendBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class SettingsImportController extends Controller
{
    public function importSettingsPostAction($hash, Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        $importManager = $this->get('mm_samy_editor.scm_import_manager');

        $files = $request->get('files');
        $persist = $request->get('dryrun') != 'true' ;
        foreach ($files as $fileId => $fileSettings) {

            $file = $em->getRepository('MM\SamyChan\BackendBundle\Entity\ScmFile')->findAndValidateHash($fileId, $hash);

            if (isset($fileSettings['importOrderFromFile'])) {
                $fileToImport = $em->getRepository('MM\SamyChan\BackendBundle\Entity\ScmFile')->find($fileSettings['importOrderFromFile']);
                $changes = $importManager->importChannelOrders($file, $fileToImport);
            }

            if ($persist) {
                $em->persist($file);
            }
        }

        if ($persist) {
            $em->flush();
        }

        return new JsonResponse(array('changes' => $changes));
    }
}