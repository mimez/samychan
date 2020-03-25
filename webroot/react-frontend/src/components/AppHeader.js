import React from "react";
import Toolbar from "@material-ui/core/Toolbar";
import Typography from "@material-ui/core/Typography";
import Button from "@material-ui/core/Button";
import AppBar from "@material-ui/core/AppBar";
import { Link } from "react-router-dom";
import { makeStyles } from '@material-ui/core/styles';
import CloudDownloadIcon from '@material-ui/icons/CloudDownload';
import IconButton from "@material-ui/core/IconButton";
import MenuIcon from "@material-ui/icons/Menu";

const useStyles = makeStyles(theme => ({
  filenameHeadline: {
    flexGrow: 1,
  }
}));

export default (props) => {
  const classes = useStyles();
  const handleDrawerToggle = () => {
    props.onToggleDrawer()
  }

  let hash = props.scmPackage.hash
  let link = React.forwardRef((props, ref) => <Link {...props} to={"/" + hash + "/download"} ref={ref} />);

  return (
    <AppBar position="static">
      <Toolbar>
        <IconButton edge="start" color="inherit" aria-label="menu" onClick={handleDrawerToggle}>
          <MenuIcon />
        </IconButton>
        <Typography variant="h6" className={classes.filenameHeadline}>
          {props.scmPackage.filename}
        </Typography>
        <Button variant="contained" color="secondary" component={link} startIcon={<CloudDownloadIcon />}>Download</Button>
      </Toolbar>
    </AppBar>
  )
}