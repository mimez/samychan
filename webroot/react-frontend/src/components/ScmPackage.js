import React, {useEffect, useState} from "react";
import {Route} from "react-router-dom";
import File from './File';
import Favorites from './Favorites';
import Navigation from "./Navigation";
import DownloadPackage from "./DownloadPackage";
import CssBaseline from '@material-ui/core/CssBaseline';
import CircularProgress from '@material-ui/core/CircularProgress';
import "./Main.css"
import Theme from "../Theme"
import Api from "../utils/Api"
import AppHeader from "./AppHeader"
import { ThemeProvider } from '@material-ui/core/styles';


export default (props) => {
  const [scmPackage, setScmPackage] = useState(undefined);
  const [navOpen, setNavOpen] = useState(true);

  const handleDrawerToggle = () => {
    setNavOpen(!navOpen);
  };

  useEffect(() => {
    Api.getPackage(props.match.params.scmPackageHash, (data) => setScmPackage(data))
  }, [props.match.params.scmPackageHash])

  var renderApp = () => {
    return (
      <div className={navOpen ? "nav-open" : "nav-closed"}>
        <ThemeProvider theme={Theme}>
          <CircularProgress />
          <CssBaseline />
          <AppHeader scmPackage={scmPackage} onToggleDrawer={handleDrawerToggle}/>
          <Navigation open={navOpen} scmPackage={scmPackage}/>
          <main>
            <Route path="/:scmPackageHash/files/:scmFileId" component={File} />
            <Route path="/:scmPackageHash/favorites/:favNo" component={Favorites} />
            <Route path="/:scmPackageHash/download" component={DownloadPackage} />
          </main>

        </ThemeProvider>
      </div>
    )
  }

  var showLoadingScreen = () => {
    return (
      <CircularProgress />
    )
  }

  return (
    scmPackage ? renderApp() : showLoadingScreen()
  )
}

