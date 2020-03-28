import React, { Component } from "react"
import Checkbox from "@material-ui/core/Checkbox";
import IconButton from '@material-ui/core/IconButton';
import MoreVertIcon from '@material-ui/icons/MoreVert';
import {makeStyles} from "@material-ui/core/styles";

const useStyles = makeStyles(theme => ({
  root: props => ({
    background: theme.palette.primary.light,
  })
}));

export default class Channel extends Component {
  constructor(props){
    super(props)
    this.handleBlur = this.handleBlur.bind(this);
    this.handleChange = this.handleChange.bind(this);
    this.handleSelect = this.handleSelect.bind(this);
    this.handleKeyNav = this.handleKeyNav.bind(this);
    this.state = {}
    this.state.channelNo = props.channelData.channelNo
    this.state.name = props.channelData.name
    this.channelDiv = React.createRef();
  }

  shouldComponentUpdate(nextProps, nextState) {

    if (this.props.selected !== nextProps.selected) {
      return true
    }

    if (this.state.channelNo !== nextState.channelNo || this.state.name !== nextState.name) {
      return true
    }

    return false
  }

  handleBlur(field, event) {
    let newData = {}
    newData[field] = event.target.value
    this.props.onChannelChange({...this.props.channelData, ...newData})
  }

  handleChange(field, event) {
    let newData = {}
    newData[field] = event.target.value
    this.setState(newData)
  }

  handleSelect(event) {

    if (this.props.selected) {
      return
    }

    if (this.channelDiv.current !== event.target) {
      return
    }

    this.props.onSelect(this.props.channelData)
  }

  handleKeyNav(event) {
    if (event.target !== this.channelDiv.current) {
      return
    }

    let keys = {
      9: "right",
      37: "left",
      38: "up",
      39: "right",
      40: "down"
    }

    if (event.shiftKey) {
      keys[9] = "left"
    }

    if (typeof (keys[event.keyCode]) !== undefined) {

      this.props.onKeyNavigation(keys[event.keyCode])
      event.preventDefault()
    }
  }



  componentDidMount() {
    if (this.props.selected) {
      this.channelDiv.current.focus()
    }
  }

  componentDidUpdate(prevProps) {
    if (prevProps.selected !== this.props.selected && this.props.selected) {
      this.channelDiv.current.focus()
    }
  }


  render() {
    return (
      <li
        className="channel"
        onClick={this.handleSelect}
        onFocus={this.handleSelect}
        onKeyDown={this.handleKeyNav}
        tabIndex="0"
        ref={this.channelDiv}
        id={"channel-" + this.props.channelData.channelId}
        draggable="true"
      >
        <Checkbox
          value="secondary"
          color="secondary"
          size="small"
          className="checkbox-selector"
        />
          <input
            type="text"
            className="channel-no"
            value={this.state.channelNo}
            onChange={this.handleChange.bind(this, "channelNo")}
            onBlur={this.handleBlur.bind(this, "channelNo")}
            tabIndex="-1"
          />
          <input
            type="text"
            className="name"
            value={this.state.name}
            onChange={this.handleChange.bind(this, "name")}
            onBlur={this.handleBlur.bind(this, "name")}
            tabIndex="-1"
          />

          <IconButton aria-label="delete" size="small">
            <MoreVertIcon />
          </IconButton>
      </li>
    )
  }
}
