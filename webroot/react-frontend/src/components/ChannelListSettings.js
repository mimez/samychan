import React, {useState} from "react";
import TextField from '@material-ui/core/TextField';
import Tooltip from '@material-ui/core/Tooltip';
import IconButton from '@material-ui/core/IconButton';
import Button from '@material-ui/core/Button';
import SortIcon from '@material-ui/icons/Sort';
import Popper from "@material-ui/core/Popper";
import Paper from '@material-ui/core/Paper';
import ClickAwayListener from '@material-ui/core/ClickAwayListener';
import MenuItem from "@material-ui/core/MenuItem";
import MenuList from "@material-ui/core/MenuList";
import ExportIcon from '@material-ui/icons/InsertDriveFile';
import Toolbar from '@material-ui/core/Toolbar';
import Typography from '@material-ui/core/Typography';
import ExpandMoreIcon from '@material-ui/icons/ExpandMore';

export default (props) => {

  const [sortPopperIsVisible, setSortPopperIsVisible] = useState(false)
  const [selectionPopperIsVisible, setSelectionPopperIsVisible] = useState(false)

  const sortAnchorRef = React.useRef(null);
  const selectionAnchorRef = React.useRef(null);

  const sortOptions = [
    {label: "channel no asc", field: "channel_no", dir: "asc", type: "number"},
    {label: "channel no desc", field: "channel_no", dir: "desc", type: "number"},
    {label: "name asc", field: "name", dir: "asc", type: "text"},
    {label: "name desc", field: "name", dir: "desc", type: "text"}
  ]

  const handleFilterTextChange = (e) => {
    props.onFilterTextChange(e.target.value);
  }

  const handleSortChange = (sortOption) => {
    props.onSortChange(sortOption.field, sortOption.dir, sortOption.type)
    setSortPopperIsVisible(false);
  }
  let header;
  if (props.selectedChannels.length > 0 && props.channelActions.length > 0) {
    header =
      <div>
        <Button
          variant="contained"
          color="secondary"
          onClick={() => setSelectionPopperIsVisible(true)}
          ref={selectionAnchorRef}
        >
          {props.selectedChannels.length} items selected
          <ExpandMoreIcon />
        </Button>
        <Popper open={selectionPopperIsVisible} anchorEl={selectionAnchorRef.current}>
          <Paper>
            <ClickAwayListener onClickAway={() => setSelectionPopperIsVisible(false)}>
              <MenuList>
                {props.channelActions.map((item, key) =>
                  <MenuItem key={key} onClick={(event) => {item.onClick(props.selectedChannels); setSelectionPopperIsVisible(false)}}>
                    {item.label}
                  </MenuItem>
                )}
              </MenuList>
            </ClickAwayListener>
          </Paper>
        </Popper>
      </div>
  } else {
    header = <Typography variant="h6" noWrap>
      Cable Digital
    </Typography>
  }

  return (
    <Toolbar className="channel-list-settings">
      {header}
      <div>
        <TextField
          label="Search..."
          value={props.filterText}
          onChange={handleFilterTextChange}
        />
        <Tooltip title="Export as CSV">
          <IconButton aria-label="delete">
            <ExportIcon />
          </IconButton>
        </Tooltip>
        <Tooltip title="Change sort" open={sortPopperIsVisible ? false : undefined}>
          <IconButton ref={sortAnchorRef} aria-label="change sort" onClick={() => setSortPopperIsVisible(true)}>
            <SortIcon />
          </IconButton>
        </Tooltip>
        <Popper open={sortPopperIsVisible} anchorEl={sortAnchorRef.current}>
          <Paper>
            <ClickAwayListener onClickAway={() => setSortPopperIsVisible(false)}>
              <MenuList>
                {sortOptions.map((sortOption, index) => (
                  <MenuItem key={sortOption.label} onClick={(event) => handleSortChange(sortOption)}>
                    {sortOption.label}
                  </MenuItem>
                ))}
              </MenuList>
            </ClickAwayListener>
          </Paper>
        </Popper>
        {props.options}
      </div>
    </Toolbar>
  );
}