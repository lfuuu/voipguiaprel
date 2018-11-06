import React from 'react';
import ReactDom from 'react-dom';

export const nodesList = [
  'lk', 'stat', 'vpbx', 'reg87', 'reg88', 'reg89',
  'reg90', 'reg91', 'reg92', 'reg93', 'reg94', 'reg95',
  'reg96', 'reg97', 'reg98', 'reg99', 'reg100', 'reg101',
  'reg102', 'reg103', 'reg104', 'reg105', 'reg106', 'reg107'
];

class HealthyBarItem extends React.Component {
  render() {
    let className = 'healthy-bar-item';

    if (this.props.healthFlag === 2) {
      className += ' item-ill';
    } else if (this.props.healthFlag === 1) {
      className += ' item-yellow';
    }

    return (
        <div className={className}>
          <div><span>{this.props.nodeName}</span></div>
        </div>
    );
  }
}

export default class HealthyItemsList extends React.Component {
  render() {
    const healthyBarItems = nodesList.map((nodeName) =>
          <HealthyBarItem key={nodeName} nodeName={nodeName} healthFlag={Math.round(Math.random() * 3)} />
    );
    return (
        <div>{healthyBarItems}</div>
    );
  }
}

ReactDom.render(
    <HealthyItemsList/>,
    document.getElementById('root')
);