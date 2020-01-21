import React from 'react';
import './App.css';
import './custom.scss';
import 'jquery/dist/jquery.min.js'
import ScmPackage from './components/ScmPackage';
import {BrowserRouter as Router, Route} from "react-router-dom";

export default (props) => {
  return (
    <Router>
      <Route path="/:scmPackageHash" component={ScmPackage} />
    </Router>
  );
}