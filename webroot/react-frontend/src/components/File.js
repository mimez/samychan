import React, {useEffect, useState} from "react"
import ChannelList from "./ChannelList";
import Api from "../utils/Api"

export default (props) => {

  const [scmFileId, setScmFileId] = useState('');
  const [channels, setChannels] = useState([]);

  useEffect(() => {
    Api.getFile(props.match.params.scmPackageHash, props.match.params.scmFileId, (data) => setChannels(data.channels))
  }, [props.match.params.scmPackageHash, props.match.params.scmFileId])


  const handleChannelChange = (channel) => {
    let newChannels = [...channels]
    for (let i in newChannels) {
      if (newChannels[i].channelId === channel.channelId) {
        newChannels[i] = channel
      }
    }
    setChannels(newChannels)
  }

  return (
    <ChannelList
      channels={channels}
      onChannelChange={handleChannelChange}
    />
  );
}