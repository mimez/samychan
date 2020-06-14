import React, {useState} from "react"
import Channel from "./Channel";
import ChannelListSettings from "./ChannelListSettings";
import { FixedSizeList as List } from 'react-window';
import AutoSizer from "react-virtualized-auto-sizer";

export default (props) => {

  const [filter, setFilter] = useState({text: ""})
  const [sort, setSort] = useState({field: "channelNo", dir: "asc", type: "number"})
  const [cursorPos, setCursorPos] = useState({channelId: 0, field: "no"})

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

  const getChannelsToDisplay = () => {
    let channelsToDisplay = filterChannels(props.channels)
    channelsToDisplay = sortChannels(channelsToDisplay)
    return channelsToDisplay
  }

  const handleChannelChange = (channel) => {
    if (typeof props.onChannelChange === "function") {
      props.onChannelChange(channel)
    }
  }

  const handleKeyNavigation = (dir, field) => {
    var currentIndex, newIndex
    let channelsToDisplay = getChannelsToDisplay()
    for (let i in channelsToDisplay) {
      if (cursorPos.channelId !== channelsToDisplay[i].channelId) {
        continue
      }
      currentIndex = parseInt(i)
      switch (dir) {
        case "left":
          newIndex = currentIndex - 1
          break
        case "right":
          newIndex = currentIndex + 1
          break
        case "down":
          newIndex = currentIndex + 1
          break
        case "up":
          newIndex = currentIndex - 1
          break
        case "current":
          newIndex = currentIndex
        default:
      }

      if (typeof channelsToDisplay[newIndex] !== "undefined") {
        setCursorPos({channelId: channelsToDisplay[newIndex].channelId, field: field})
      }
    }
  }

  const handleCursorChange = (channelId, field) => {
    setCursorPos({channelId: channelId, field: field})
  }

  let channelsToDisplay = getChannelsToDisplay()
  let channelTabIndex = 0;

  const Row = ({ index, style }) => {
    let channel = channelsToDisplay[index]
    channelTabIndex = channelTabIndex + 1
    return (
      <Channel
      channelData={channel}
      key={channel.channelId}
      channelTabIndex={channelTabIndex}
      onChannelChange={handleChannelChange}
      onKeyNavigation={handleKeyNavigation}
      onCursorChange={handleCursorChange}
      cursorPos={cursorPos}
      style={style}
      ></Channel>
    )
  }

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
      <div id="channel-list-container">
        <AutoSizer>
          {({ height, width }) => (
            <List
              height={height}
              itemCount={getChannelsToDisplay().length}
              itemSize={55}
              width={width}
              className="channels"
              overscanCount={5}
              itemData={channelsToDisplay}
            >
              {Row}
            </List>
          )}
        </AutoSizer>
      </div>
    </div>
  )
}