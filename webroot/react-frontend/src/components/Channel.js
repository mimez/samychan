import React from "react"
import Checkbox from "@material-ui/core/Checkbox";
import IconButton from '@material-ui/core/IconButton';
import MoreVertIcon from '@material-ui/icons/MoreVert';
import {makeStyles} from "@material-ui/core/styles";
import { useState } from 'react';

const useStyles = makeStyles(theme => ({
  root: (props) => ({
    boxSizing: "border-box",
    marginBottom: "5px",
    marginRight: "5px",
    width: "100%",
    borderRadius: "5px",
    overflow: "hidden",
    background: props.selected ? "#6b5f73" : "#48434b",
    display: "flex",
    justifyContent: "space-between",
    padding: "5px",
    "& input:not([type='checkbox'])": {
      border: "0",
      backgroundColor: "transparent",
      width: "50px"
    },
    "&:hover input:not([type='checkbox']), & input:focus": {
      outline: "none",
      background: "#ffffff21"
    },
    "& input.name, & input.channel-no": {
      display: "inline-block",
      padding: "5px",
      marginRight: "1rem",
      color: "#fff"
    },
    "& input.channel-no": {
      width: "60px",
      fontSize: "15px",
      textAlign: "right",
      marginRight: "5px"
    },
    "& input.name": {
      flexGrow: 1,
      fontWeight: "bolder",
      textOverflow: "ellipsis",
      width: "10rem",
      overflow: "hidden",
      fontSize: "15px",
      padding: "5px"
    },
    "&:focus, &:focus-within": {
      background: "#6b5f73"
    }
  })
}));

export default React.memo((props) => {
console.log('RENDER CHANNEL')
  /**
   * We have a local state for editing the channel. By entering the edit-mode we copy
   * the parent-state into the local state. After leaving the edit-mode we trigger a
   * event so the parent component can handle the change
   */
  const [isEditMode, setIsEditMode] = useState(false);
  const [channelNo, setChannelNo] = useState("");
  const [channelName, setChannelName] = useState("");
  const classes = useStyles(props);

  const handleSelect = (event) => {
    return
    console.log('handle-select')
    if (props.selected) {
      return
    }

    /*if (this.channelDiv.current !== event.target) {
      return
    }*/
    //props.onSelect || props.onSelect(props.channelData)
  }

  const handleKeyNav = (event) => {
    console.log('handle-key-nav')
    /*if (event.target !== this.channelDiv.current) {
      return
    }*/

    let keys = {
      38: "up",
      40: "down"
    }

    if (typeof (keys[event.keyCode]) !== undefined) {
      props.onKeyNavigation(keys[event.keyCode])
      event.preventDefault()
    }
  }

  /**
   * Activate the Edit-Mode and set the the current name/channel-no for modification
   */
  const enableEditMode = () => {
    console.log('ENTER EDIT MODE')
    setIsEditMode(true)
    setChannelName(props.channelData.name)
    setChannelNo(props.channelData.channelNo)
  }

  /**
   * Disable Edit Mode
   * By disabling edit mode we trigger the onChannelChange event (if any data has changed),
   * so the parent component can handle the change
   */
  const disableEditMode = () => {
    setIsEditMode(false)

    // check if anything has changed, in case if not, do nothing anymore
    if (props.channelData.name === channelName && props.channelData.channelNo === channelNo) {
      return;
    }

    let newData = {}
    newData['name'] = channelName
    newData['channelNo'] = channelNo
    props.onChannelChange({...props.channelData, ...newData})
  }

  return (
    <div style={props.style}>
      <div
        className={classes.root}
        /*onClick={handleSelect}
        onFocus={handleSelect}*/
        onKeyDown={handleKeyNav}
        tabIndex="0"
        /*ref={channelDiv}*/
        id={"channel-" + props.channelData.channelId}
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
          value={isEditMode ? channelNo : props.channelData.channelNo}
          readOnly={isEditMode ? false : true}
          onChange={(e) => {setChannelNo(e.target.value)}}
          onFocus={isEditMode ? null : enableEditMode}
          onBlur={isEditMode ? disableEditMode : null}
        />
        <input
          type="text"
          className="name"
          value={isEditMode ? channelName : props.channelData.name}
          onChange={(e) => {setChannelName(e.target.value)}}
          onFocus={isEditMode ? null : enableEditMode}
          onBlur={disableEditMode}
        />
        {isEditMode ? "EDIT" : "NO_EDIT"}
        {props.selected ? "SELECTED" : ""}

        <IconButton aria-label="delete" size="small">
          <MoreVertIcon />
        </IconButton>
      </div>
    </div>
  )
})
