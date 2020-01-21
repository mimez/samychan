import React, {useState} from "react";
import TextField from '@material-ui/core/TextField';
import Tooltip from '@material-ui/core/Tooltip';
import IconButton from '@material-ui/core/IconButton';
import SortIcon from '@material-ui/icons/Sort';
import Grid from "@material-ui/core/Grid";
import Popper from "@material-ui/core/Popper";
import Paper from '@material-ui/core/Paper';
import ClickAwayListener from '@material-ui/core/ClickAwayListener';
import MenuItem from "@material-ui/core/MenuItem";
import MenuList from "@material-ui/core/MenuList";

export default (props) => {
  const [sortPopperIsVisible, setSortPopperIsVisible] = useState(false)
  const anchorRef = React.useRef(null);
  const sortOptions = [
    {label: "channel no asc", field: "channel_no", dir: "asc", type: "number"},
    {label: "channel no desc", field: "channel_no", dir: "desc", type: "number"},
    {label: "name asc", field: "name", dir: "asc", type: "text"},
    {label: "name desc", field: "name", dir: "desc", type: "text"}
  ]

  const handleFilterTextChange = (e) => {
    props.onFilterTextChange(e.target.value);
  }

  const handleSortPopperClose = () => {
    setSortPopperIsVisible(false)
  }

  const handleSortIconClick = (event) => {
    setSortPopperIsVisible(true)
  }

  const handleSortChange = (sortOption) => {
    props.onSortChange(sortOption.field, sortOption.dir, sortOption.type)
    setSortPopperIsVisible(false);
  }

  return (
    <div>
      <Grid
        justify="space-between" // Add it here :)
        container
        className="channel-list-settings"
      >
        <Grid item>
          <TextField
            label="Search..."
            value={props.filterText}
            onChange={handleFilterTextChange}
          />
        </Grid>
        <Grid item>
          <Tooltip title="Change sort" open={sortPopperIsVisible ? false : undefined}>
            <IconButton ref={anchorRef} aria-label="change sort" onClick={handleSortIconClick}>
              <SortIcon />
            </IconButton>
          </Tooltip>
          <Popper open={sortPopperIsVisible} anchorEl={anchorRef.current}>
            <Paper>
              <ClickAwayListener onClickAway={handleSortPopperClose}>
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
        </Grid>
      </Grid>
    </div>
  );
}