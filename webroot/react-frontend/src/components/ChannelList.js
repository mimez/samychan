import React, {useState} from "react"
import ChannelListSettings from "./ChannelListSettings";
import ChannelListChannels from "./ChannelListChannels";


export default (props) => {

  const [filter, setFilter] = useState({text: ""})

  const [sort, setSort] = useState({field: "channelNo", dir: "asc", type: "number"})

  const [selectedChannels, setSelectedChannels] = useState([])

  const filterChannels = (channels) => {
    let filteredChannels = []
    for (let i in channels) {
      if (channels[i].name.toLowerCase().indexOf(filter.text.toLowerCase()) !== -1) {
        filteredChannels.push(channels[i])
      }
    }
    return filteredChannels
  }

  const sortChannels = (channels) => {
    let retA = 1, retB = -1
    if (sort.dir === "desc") {
      retA = -1
      retB = 1
    }
    switch (sort.type) {
      case "number":
        channels.sort((a,b) => parseInt(a[sort.field]) > parseInt(b[sort.field]) ? retA : retB)
        break;
      default:
      case "text":
        channels.sort((a,b) => a[sort.field] > b[sort.field] ? retB : retA)
    }

    return channels
  }

  const handleSortChange = (field, dir, type) => {
    setSort({field: field, dir: dir, type: type})
  }

  const getChannelsToDisplay = (channels) => {
    let channelsToDisplay = filterChannels(props.channels)
    channelsToDisplay = sortChannels(channelsToDisplay)

    return channelsToDisplay
  }

  const handleChannelChange = (channel) => {
    if (typeof props.onChannelChange === "function") {
      props.onChannelChange(channel)
    }
  }

  const handleSelectionChange = (channelId) => {
    let newSelectedChannels = [...selectedChannels]
    let index = newSelectedChannels.indexOf(channelId)
    if (index === -1) {
      newSelectedChannels.push(channelId)
    } else {
      newSelectedChannels.splice(index, 1)
    }
    setSelectedChannels(newSelectedChannels)
  }

  let channelsToDisplay = getChannelsToDisplay(props.channels)

  return (
    <div className="channel-list">
      <ChannelListSettings
        filterText={filter.text}
        sort={{sortField: "name", sortDir: "desc", sortType: "text"}}
        sortField={sort.field}
        sortDir={sort.dir}
        sortType={sort.type}
        onFilterTextChange={(text) => setFilter({text: text})}
        onSortChange={handleSortChange}
        onSelectionChange={handleSelectionChange}
        selectedChannels={selectedChannels}
        channelActions={props.channelActions}

      />
      <ChannelListChannels
        channels={channelsToDisplay}
        onChannelChange={handleChannelChange}
        onSelectionChange={handleSelectionChange}
        selectedChannels={selectedChannels}
      />
    </div>
  )
}