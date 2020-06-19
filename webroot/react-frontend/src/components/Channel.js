import React from "react"
import {makeStyles} from "@material-ui/core/styles";
import {useState} from 'react';

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

  /**
   * Testplan
   * Creatign a good user expierience is very important for editing a whole bunch of channels.
   * Users are very different, some users will use mouse navigation others will use key-board navigation very heavily
   * We have many different points to check, so there is a manual test plan to check:
   *
   * - Keyboard-Navigation without changes works properly (navigating by tab, enter, key-down/up)
   * - By clicking on a INPUT starts the edit mode and the input gets focused
   * - By entering the edit-mode the text / number gets selected
   * - If we click again we can set the cursor to a specific char
   * - writing inside the input works
   * - After editing a value and bluring the input it gets saved
   * - After ediiting a value and doing keyboard-navigation (enter / key down) we get to the expected row
   * - Saving by tabs works properly
   */

  /**
   * We have a local state for editing the channel. By entering the edit-mode we copy
   * the parent-state into the local state. After leaving the edit-mode we trigger a
   * event so the parent component can handle the change
   */
  const [isEditMode, setIsEditMode] = useState(false)
  const [channelNo, setChannelNo] = useState("")
  const [channelName, setChannelName] = useState("")
  const classes = useStyles(props)

  /**
   * Focus name / number-field
   * 1) We select all the text (it should by more user friendly)
   * 2) By focusing the input, we notify the parent about the new cursor position
   * 3) We enter the edit mode
   * @param event
   */
  const focusInput = (event) => {
    event.target.select()
    notifyCursorPos(event.target.dataset.field)
    enterEditMode(event)
  }

  /**
   * Blur Event
   * We just land here if we blur the input by mouse. Navigating by keyboard will not result in bluring input
   * @param event
   */
  const blurInput = (event) => {
    disableEditMode(0, null)
  }

  /**
   * Activate the Edit-Mode and set the the current name/channel-no for modification
   * @param event
   */
  const enterEditMode = (event) => {
    if (isEditMode) {
      return
    }

    setIsEditMode(true)
    setChannelName(props.channelData.name)
    setChannelNo(props.channelData.channelNo)
  }

  /**
   * Notify parent about cursor position (active field)
   * @param field
   */
  const notifyCursorPos = (field) => {

    // if cursor-position is update, we nothing to do
    if (props.cursorPos.channelId === props.channelData.channelId && props.cursorPos.field === field) {
      return
    }

    // call parent event handler
    props.onCursorChange(props.channelData.channelId, field)
  }

  /**
   * Keboard-Navigation
   * @param event
   */
  const handleKeyNav = (event) => {
    let keys = {
      38: "up", // KEY_UP
      40: "down", // KEY_DOWN
      13: "down" // ENTER
    }

    // tab and current field is "name" then switch to next channel
    if (event.shiftKey && event.keyCode === 9 && event.target.dataset.field === 'no') {
      disableEditMode("up", "name")
      event.preventDefault()
    } else if (event.shiftKey && event.keyCode === 9 && event.target.dataset.field === 'name') {
      disableEditMode("current", "no")
      event.preventDefault()
    } else if (event.keyCode === 9 && event.target.dataset.field === 'name') {
      disableEditMode("down", "no")
      event.preventDefault()
    } else if (event.keyCode === 9 && event.target.dataset.field === 'no') {
      disableEditMode("current", "name")
      event.preventDefault()
    }

    if (event.keyCode in keys) {
      disableEditMode(keys[event.keyCode], event.target.dataset.field)
      event.preventDefault()
    }
  }

  /**
   * Disable Edit Mode
   * By disabling edit mode we trigger the onChannelChange event (if any data has changed),
   * so the parent component can handle the change
   */
  const disableEditMode = (nextChannelToEnter, nextFieldToEnter) => {
    setIsEditMode(false)

    // check if anything has changed, in case if not, do nothing anymore
    if (props.channelData.name !== channelName || props.channelData.channelNo !== channelNo) {
      let newData = {}
      newData['name'] = channelName
      newData['channelNo'] = channelNo
      props.onChannelChange({...props.channelData, ...newData})
    }

    // navigate to next channel if requested
    if (["up", "down", "current"].includes(nextChannelToEnter)) {
      props.onKeyNavigation(nextChannelToEnter, nextFieldToEnter)
    } else {
      props.onCursorChange(0, null)
    }
  }

  const toggleChannelSelection = (event) => {
    if (typeof props.onSelectionChange === "function") {
      props.onSelectionChange(props.channelData.channelId)
    }
  }

  return (
    <div style={props.style}>
      <div
        className={classes.root}
        onKeyDown={handleKeyNav}
        id={"channel-" + props.channelData.channelId}
      >
        <input
          type="checkbox"
          onChange={toggleChannelSelection}
          checked={props.selected}
        />
        <input
          type="text"
          className="channel-no"
          data-field="no" // field-type for cursorPos
          tabIndex={props.channelTabIndex * 10000 + 1} // tabIndex for looping through inputs by tab
          value={isEditMode ? channelNo : props.channelData.channelNo}
          readOnly={isEditMode ? false : true}
          onChange={(e) => {setChannelNo(e.target.value)}}
          onFocus={focusInput} // if we get the focus we automatically enter the editmodus
          autoFocus={props.cursorPos.channelId === props.channelData.channelId && props.cursorPos.field === 'no'}
          onBlur={blurInput}
        />
        <input
          type="text"
          className="name"
          data-field="name"
          tabIndex={props.channelTabIndex * 10000 + 2}
          value={isEditMode ? channelName : props.channelData.name}
          onChange={(e) => {setChannelName(e.target.value)}}
          onFocus={focusInput}
          autoFocus={props.cursorPos.channelId === props.channelData.channelId && props.cursorPos.field === 'name'}
          onBlur={blurInput}
        />

      </div>
    </div>
  )
})
