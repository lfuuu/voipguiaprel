import React from 'react';
import ReactDom from 'react-dom';

const typicalErrors = [
  {
    name: 'ApihostRadiusStatus',
    message: 'Apihost received RADIUS request 0 seconds ago from server_id:99'
  },
  {
    name: 'publicApiWorks',
    message: 'Public API NOT AVAILABLE'
  }
];

export default class Card extends React.Component {
  render() {
    const errorsList = typicalErrors.map((error) =>
        <Error key={error.name} itemName={error.name} itemMessage={error.message} />
    );

    return (
        <div className='yellow-card card'>
          <span className='node-title'>reg89.mcntelecom.ru</span>
          <div className='node-header-info'>
            <span className='runtime'>runtime: <span className='hours'>04</span><span className='tim e-pulse'>:</span><span className='minutes'>47</span></span> | <span>calls: 3655</span>
          </div>
          <div className='problematic-items'>{errorsList}</div>
          <div className='problematic-items-display'/>
        </div>
    );
  }
}

class Error extends React.Component {
  render() {
    return (
        <div className='problematic-item'>
          <span className='item-name'>{this.props.itemName}</span>
          <br/>
          <span className='message' title={this.props.itemMessage}>{this.props.itemMessage}</span>
        </div>
    );
  }
}

ReactDom.render(
    <Card/>,
    document.getElementById('criticalArea')
);