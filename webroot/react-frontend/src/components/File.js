import React, {useEffect, useState} from "react"
import ChannelList from "./ChannelList";

export default (props) => {

  const [scmFileId, setScmFileId] = useState('');
  const [channels, setChannels] = useState([]);

  useEffect(() => {
    fetch("http://samychan.devbox.local/backend/5e11c1bd532f4/file/13/json/")
      .then(results => {
        return results.json()
      })
      .then(data => {
        setChannels(data.channels)
      })
  }, [scmFileId])


  const handleChannelChange = (channel) => {
    let newChannels = channels
    for (let i in newChannels) {
      if (newChannels[i].channelId === channel.channelId) {
        newChannels[i] = channel
      }
    }
    setChannels(newChannels);
  }

  return (
    <div>
      <ChannelList
        channels={channels}
        onChannelChange={handleChannelChange}
      />
    </div>
  );
}