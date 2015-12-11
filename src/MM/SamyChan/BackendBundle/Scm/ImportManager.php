<?php
namespace MM\SamyChan\BackendBundle\Scm;

use MM\SamyChan\BackendBundle\Entity;

class ImportManager
{
    /**
     * Apply the orders from one file to another
     *
     * @param Entity\ScmFile $importScmFile
     * @param Entity\ScmFile $scmFile
     *
     * @return array $changes
     */
    public function importChannelOrders(Entity\ScmFile $scmFile, Entity\ScmFile $importScmFile)
    {
        $changes = array();
        $processedChannelNames = array();
        $missedChannels = array();
        $highestChannelNo = 0;

        // loop over the target channels
        foreach ($scmFile->getChannels() as $chanenl) {

            // try to determine the target-channel in the source-File
            $importChannel = $importScmFile->getChannelByName($chanenl->getName());

            // if we didnt match the channel, we cant do anything. skip this channel.
            if (!$importChannel) {
                $missedChannels[$chanenl->getChannelNo()] = $chanenl;
                continue;
            }

            // check if we have already processed this channel name.
            // this is to avoid double processing of channels, whose name exist multiple times in one list
            if (in_array(strtolower($chanenl->getName()), $processedChannelNames)) {
                continue;
            }

            // add channel to the proceeded channel names
            $processedChannelNames[] = strtolower($chanenl->getName());

            // check if the channel nos already the same
            if ($importChannel->getChannelNo() == $chanenl->getChannelNo()) {
                continue;
            }

            // transfer the channel-no
            $changes[] = sprintf('set channel=(%s) from=(%s) to=(%s)', $chanenl->getName(), $chanenl->getChannelNo(), $importChannel->getChannelNo());
            $chanenl->setChannelNo($importChannel->getChannelNo());

            // increase the highest channel-no
            $highestChannelNo = max($highestChannelNo, $chanenl->getChannelNo());
        }

        // at this point, we changed all possible channels to the new channel-no.
        // now we have to deal with the missed channels. we could have number-conflicts.
        // to solve this, we set all mised channels to the end.
        ksort($missedChannels);

        $offset = 1;
        foreach ($missedChannels as $missedChannel) {
            $missedChannel->setChannelNo($highestChannelNo + $offset);
            $offset++;
        }

        return $changes;
    }
}