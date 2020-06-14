import React, {Component, useState} from "react"
import Channel from "./Channel";
import ChannelListSettings from "./ChannelListSettings";
import { FixedSizeList as List } from 'react-window';
import AutoSizer from "react-virtualized-auto-sizer";

export default (props) => {

  const [filter, setFilter] = useState({text: ""});
  const [sort, setSort] = useState({field: "channelNo", dir: "asc", type: "number"});
  const [selectedChannelId, setSelectedChannelId] =useState(0);

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

  const handleKeyNavigation = (dir) => {
    console.log(selectedChannelId)
    var currentIndex, newIndex
    let channelsToDisplay = getChannelsToDisplay()
    for (let i in channelsToDisplay) {
      if (selectedChannelId !== channelsToDisplay[i].channelId) {
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
        default:
      }

      if (typeof channelsToDisplay[newIndex] !== "undefined") {
        setSelectedChannelId(channelsToDisplay[newIndex].channelId)
      }
    }
  }

  let channelsToDisplay = getChannelsToDisplay()

  const Row = ({ index, style }) => {
    let channel = channelsToDisplay[index]
    return (
      <Channel
      channelData={channel}
      key={channel.channelId}
      onChannelChange={handleChannelChange}
      onKeyNavigation={handleKeyNavigation}
      onSelect={() => setSelectedChannelId(channel.channelId)}
        /*selected={selectedChannelId === channel.channelId ? true : false}

        */
      /*ref={channelRefs[channel.channelId]}*/
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