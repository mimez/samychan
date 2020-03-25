import React, {useEffect, useState} from "react"
import ChannelList from "./ChannelList";
import Api from "../utils/Api";
import Fab from "@material-ui/core/Fab";
import AddIcon from "@material-ui/icons/Add";
import Tooltip from "@material-ui/core/Tooltip";

export default (props) => {

  const [channels, setChannels] = useState([])
  const [selectedChannels, setSelectedChannels] = useState([])
  const channelListOptions = [
    <Tooltip title="Add channel">
      <Fab color="secondary" size="medium" aria-label="add">
        <AddIcon />
      </Fab>
    </Tooltip>
  ]

  useEffect(() => {
    Api.getFavorites(props.match.params.scmPackageHash, props.match.params.favNo, (data) => {
      console.log(data)
      setChannels(data.channels)
      setSelectedChannels(data.selectedChannels)
    })
  }, [props.match.params.scmPackageHash, props.match.params.favNo])

  const addChannel = () => {

  }

  return (
    <div>
        <ChannelList
          channels={selectedChannels}
          options={channelListOptions}
        />
    </div>
  );
}