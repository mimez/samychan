import Toolbar from "@material-ui/core/Toolbar";
import IconButton from "@material-ui/core/IconButton";
import MenuIcon from "@material-ui/icons/Menu";
import Typography from "@material-ui/core/Typography";
import Button from "@material-ui/core/Button";
import AppBar from "@material-ui/core/AppBar";
import React from "react";
import { Link } from "react-router-dom";

export default (props) => {

  const handleDrawerToggle = () => {
    props.onToggleDrawer()
  }

  let hash = props.scmPackageHash
  let link = React.forwardRef((props, ref) => <Link {...props} to={"/" + hash + "/download"} ref={ref} />);

  return (
    <AppBar>
      <Toolbar>
        <IconButton edge="start" color="inherit" aria-label="menu" onClick={handleDrawerToggle}>
          <MenuIcon />
        </IconButton>
        <Typography variant="h6">
          {props.scmPackage.filename}
        </Typography>
        <Button variant="contained" color="secondary" component={link}>Download</Button>
      </Toolbar>
    </AppBar>
  )
}