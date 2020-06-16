import React, {useState, useMemo} from "react"
import Channel from "./Channel";
import ChannelListSettings from "./ChannelListSettings";
import ChannelListChannels from "./ChannelListChannels";


export default (props) => {
  console.log("RENDER CHANNEL LIST")
  const [filter, setFilter] = useState({text: ""})
  const [sort, setSort] = useState({field: "channelNo", dir: "asc", type: "number"})


  const filterChannels = (channels) => {
    console.log("filterChannels")
    let filteredChannels = []
    for (let i in channels) {
      if (channels[i].name.toLowerCase().indexOf(filter.text.toLowerCase()) !== -1) {
        filteredChannels.push(channels[i])
      }
    }
    return filteredChannels
  }

  const sortChannels = (channels) => {
  console.log("sortChannels")
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
  console.log("getChannelsToDisplay")
    return props.channels
    let channelsToDisplay = filterChannels(props.channels)
    channelsToDisplay = sortChannels(channelsToDisplay)
    return channelsToDisplay
  }





  let channelsToDisplay = getChannelsToDisplay(props.channels)


  return (
    <div className="channel-list">
      <ChannelListSettings
        filterText={filter.text}
        sortField={sort.field}
        sortDir={sort.dir}
        sortType={sort.type}
        onFilterTextChange={(text) => setFilter({text: text})}
        onSortChange={handleSortChange}
        sort={{sortField: "name", sortDir: "desc", sortType: "text"}}
        options={props.options}
      />
      <ChannelListChannels
        channels={channelsToDisplay}
      />
    </div>
  )
}