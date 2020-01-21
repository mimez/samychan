import React from "react";
import { Link } from "react-router-dom";
import Drawer from '@material-ui/core/Drawer';
import Divider from '@material-ui/core/Divider';
import ListItem from '@material-ui/core/ListItem';
import List from '@material-ui/core/List';
import ListItemIcon from '@material-ui/core/ListItemIcon';
import ListItemText from '@material-ui/core/ListItemText';
import MailIcon from '@material-ui/icons/Mail';
import clsx from 'clsx';
import "./Navigation.css"

export default (props) => {

  const getNavFiles = () => {
    if (typeof props.scmPackage.files === "undefined") return []
    return props.scmPackage.files.map((file) => {
      let hash = props.scmPackage.hash
      let link = React.forwardRef((props, ref) => <Link {...props} to={"/" + hash + "/files/" + file.scmFileId} ref={ref} />);
      return (
        <ListItem key={"list-item-file-" + file.scmFileId} button component={link}>
          <ListItemIcon><MailIcon /></ListItemIcon>
          <ListItemText primary={file.label} secondary={file.channelCount + " channels"} />
        </ListItem>
      )
    })
  }

  const getNavFavorites = () => {
    if (typeof props.scmPackage.favorites === "undefined") return []

    return props.scmPackage.favorites.map((favorite) => {
      let hash = props.scmPackage.hash
      let link = React.forwardRef((props, ref) => <Link {...props} to={"/" + hash + "/favorites/" + favorite.favNo} ref={ref} />);
      return (
        <ListItem key={"list-item-fav-" + favorite.favNo} button component={link}>
          <ListItemIcon><MailIcon /></ListItemIcon>
          <ListItemText primary={"Fav #" + favorite.favNo} secondary={favorite.channelCount + " channels"} />
        </ListItem>
      )
    })
  }

  return (
    <Drawer
      variant="permanent"
      className={clsx({open: props.open, closed: !props.open})}
    >
      <List>
        {getNavFiles()}
      </List>
      <Divider />
      <List>
        {getNavFavorites()}
      </List>
    </Drawer>
  );
}

