<?php
namespace MM\SamyChan\BackendBundle\Scm;

use MM\SamyChan\BackendBundle\Entity;

class TransferManager
{
    /**
     * Apply the orders from file to another
     *
     * @param Entity\ScmFile $sourceScmFile
     * @param Entity\ScmFile $targetScmFile
     *
     * @return array $changes
     */
    public function transferChannelOrders(Entity\ScmFile $sourceScmFile, Entity\ScmFile $targetScmFile)
    {
        $changes = array();
        $processedChannelNames = array();
        $missedChannels = array();
        $highestChannelNo = 0;

        // loop over the target channels
        foreach ($targetScmFile->getChannels() as $targetChannel) {

            // try to determine the target-channel in the source-File
            $sourceChannel = $sourceScmFile->getChannelByName($targetChannel->getName());

            // if we didnt match the channel, we cant do anything. skip this channel.
            if (!$sourceChannel) {
                $missedChannels[$targetChannel->getChannelNo()] = $targetChannel;
                continue;
            }

            // check if we have already processed this channel name.
            // this is to avoid double processing of channels, whose name exist multiple times in one list
            if (in_array(strtolower($targetChannel->getName()), $processedChannelNames)) {
                continue;
            }

            // add channel to the proceeded channel names
            $processedChannelNames[] = strtolower($targetChannel->getName());

            // check if the channel nos already the same
            if ($sourceChannel->getChannelNo() == $targetChannel->getChannelNo()) {
                continue;
            }

            // transfer the channel-no
            $changes[] = sprintf('set channel=(%s) from=(%s) to no=(%s)', $targetChannel->getName(), $targetChannel->getChannelNo(), $sourceChannel->getChannelNo());
            $targetChannel->setChannelNo($sourceChannel->getChannelNo());

            // increase the highest channel-no
            $highestChannelNo = max($highestChannelNo, $targetChannel->getChannelNo());
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