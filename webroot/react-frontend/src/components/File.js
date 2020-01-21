import React, { Component } from "react"
import ChannelList from "./ChannelList";

class File extends Component {

  constructor(props) {
    super(props)
    this.state = {scmFileId: ''}
    this.handleChannelChange = this.handleChannelChange.bind(this)
  }

  componentDidMount() {
    this.loadData()
  }

  loadData() {
    fetch("http://samychan.devbox.local/backend/5e11c1bd532f4/file/13/json/")
      .then(results => {
        return results.json();
      })
      .then(data => {
        this.setState(data);
      })
  }

  handleChannelChange(channel) {
    console.log(channel)
    let channels = {...this.state.channels}
    for (let i in channels) {
      if (channels[i].channelId === channel.channelId) {
        channels[i] = channel
      }
    }
    this.setState({channels: channels})
  }

  render() {

    return (
      <div>
        <h1>File #{this.state.scmFileId}</h1>
        <ChannelList
          channels={this.state.channels}
          onChannelChange={this.handleChannelChange}
        />
      </div>
    );
  }
}

export default File;