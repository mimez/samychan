import React, { Component } from "react"
import Channel from "./Channel";
import ChannelListSettings from "./ChannelListSettings";

export default class ChannelList extends Component {
  constructor(props) {
    super(props)
    this.handleFilterTextChange = this.handleFilterTextChange.bind(this)
    this.handleSortChange = this.handleSortChange.bind(this)
    this.handleChannelChange = this.handleChannelChange.bind(this)
    this.handleChannelSelect = this.handleChannelSelect.bind(this)
    this.handleKeyNavigation = this.handleKeyNavigation.bind(this)
    this.channelRefs = {}
    this.channelRefs[69] = React.createRef()
    this.state = {
      channelsToDisplay: [],
      filter: {
        text: ""
      },
      sort: {
        field: "channelNo",
        dir: "asc",
        type: "number"
      },
      selectedChannelId: ""
    }
  }

  filterChannels(channels) {
    let filteredChannels = []
    for (let i in channels) {
      if (channels[i].name.toLowerCase().indexOf(this.state.filter.text.toLowerCase()) !== -1) {
        filteredChannels.push(channels[i])
      }
    }
    return filteredChannels
  }

  sortChannels(channels) {
    let retA = 1, retB = -1
    if (this.state.sort.dir === "desc") {
      retA = -1
      retB = 1
    }
    switch (this.state.sort.type) {
      case "number":
        channels.sort((a,b) => parseInt(a[this.state.sort.field]) > parseInt(b[this.state.sort.field]) ? retA : retB)
        break;
      default:
      case "text":
        channels.sort((a,b) => a[this.state.sort.field] > b[this.state.sort.field] ? retB : retA)
    }

    return channels
  }

  handleFilterTextChange(filterText) {
    this.setState({
      filter: {text: filterText}
    });
  }

  handleSortChange(field, dir, type) {
    this.setState({
      sort: {field: field, dir: dir, type: type}
    });
  }

  getChannelsToDisplay() {
    let channelsToDisplay = this.filterChannels(this.props.channels)
    channelsToDisplay = this.sortChannels(channelsToDisplay)
    return channelsToDisplay
  }

  handleChannelChange(channel) {
    if (typeof this.props.onChannelChange === "function") {
      this.props.onChannelChange(channel)
    }
  }

  handleChannelSelect(channel) {
    this.setState({selectedChannelId: channel.channelId})
  }

  handleKeyNavigation(dir) {
    var currentIndex, newIndex
    let channelsToDisplay = this.getChannelsToDisplay()
    for (let i in channelsToDisplay) {
      if (this.state.selectedChannelId !== channelsToDisplay[i].channelId) {
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
          newIndex = currentIndex + this.getColumnCount();
          break
        case "up":
          newIndex = currentIndex - this.getColumnCount();
          break
        default:
      }

      if (typeof channelsToDisplay[newIndex] !== "undefined") {
        this.setState({selectedChannelId: channelsToDisplay[newIndex].channelId})
      }
    }
  }

  getColumnCount() {
    var count = 0
    var elements = document.querySelectorAll('.channel-list .channel')
    var lastY = elements[0].getBoundingClientRect().y;
    for (var element of elements) {
      if (lastY !== element.getBoundingClientRect().y) {
        return count
      }
      count++
    }
  }

  render() {
    let channelsToDisplay = this.getChannelsToDisplay()
    let channels = []
    for (let i in channelsToDisplay) {
        channels.push(<Channel
          channelData={channelsToDisplay[i]}
          key={channelsToDisplay[i].channelId}
          onChannelChange={this.handleChannelChange}
          selected={this.state.selectedChannelId === channelsToDisplay[i].channelId ? true : false}
          onSelect={this.handleChannelSelect}
          onKeyNavigation={this.handleKeyNavigation}
          ref={this.channelRefs[channelsToDisplay[i].channelId]}
        ></Channel>)
    }

    return (
      <div className="channel-list">
        <ChannelListSettings
          filterText={this.state.filter.text}
          sortField={this.state.sort.field}
          sortDir={this.state.sort.dir}
          sortType={this.state.sort.type}
          onFilterTextChange={this.handleFilterTextChange}
          onSortChange={this.handleSortChange}
          sort={{sortField: "name", sortDir: "desc", sortType: "text"}}
          options={this.props.options}
        />
        <ul className="channels">
          {channels}
        </ul>
      </div>
    )
  }
}