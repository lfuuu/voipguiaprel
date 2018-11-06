import React from 'react';
import ReactDOM from 'react-dom';
import styled, { ThemeProvider } from 'styled-components';

import themes from './config/themes';

import HealthBar from './containers/HealthBar/HealthBar';
import YellowBar from './containers/YellowBar/YellowBar';
import MiddleBar from './containers/MiddleBar/MiddleBar';
import RedBar from './containers/RedBar/RedBar';
import CriticalModal from './containers/CriticalModal/CriticalModal';

import fetchData from './containers/Data/Data';

const Wrapper = styled.div`
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: row;
`;

const statusPriorities = {
  'STATUS_OK': 0,
  'STATUS_SYNC': 1,
  'STATUS_UNKNOWN': 2,
  'STATUS_WARNING': 3,
  'STATUS_ERROR': 4,
  'STATUS_CRITICAL': 5
};

class App extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      theme: themes.light,
      data: {},
      nodes: [],
      healthBarNodes: [],
      yellowNodes: [],
      redNodes: [],
      criticalNodes: []
    };

    fetchData().then((response) => {
      this.dataConstructor(response);
    });

    setInterval(() => {
      fetchData().then((response) => {
        this.dataConstructor(response);
      })
    }, 10000);
  }

  dataConstructor(response) {
    console.log(response)

    // перебираем узлы
    let nodes = [],
      healthBarNodes = [],
      yellowNodes = [],
      redNodes = [],
      criticalNodes = [];
    console.log(yellowNodes)

    for (let key in response) {
      if (response.hasOwnProperty(key)) {
        if (key === 'lastUpdate') continue;
        let node = response[key],
          name = '', // короткое имя узла
          subname = '',
          currentCalls = '',
          runtime = '',
          badItems = [],
          instanceId = '',
          extendedInfo = '',
          autoLockFinance = '',
          regionList = '',
          resourceUrl = '';

        if (key === 'iberus.mcn.ru:8032') {
          name = 'iberus';
          subname = '(:8032)';
        } else if (key === 'iberus.mcn.ru:8099') {
          name = 'iberus';
          subname = '(:8099)';
        } else if (key === 'stat.mcn.ru (for managers)') {
          name = 'stat';
          subname = '(managers)';
        } else {
          name = key.split('.')[0];
        }

        node.name = name;
        nodes.push(node);

        // перебираем item'ы каждого узла
        let status = 0, statusName;
        for (let nodeKey in node) {
          if (node.hasOwnProperty(nodeKey)) {
            if (nodeKey.indexOf('item') !== -1) {
              status = Math.max(statusPriorities[node[nodeKey].statusId], status);
              if (node[nodeKey].statusId !== 'STATUS_OK' && node[nodeKey].statusId !== 'STATUS_SYNC') {
                badItems.push({
                  itemId: node[nodeKey].itemId,
                  itemStatus: node[nodeKey].statusId,
                  itemStatusMessage: node[nodeKey].statusMessage
                })
              }
            }
            if (nodeKey.indexOf('currentCalls') !== -1) {
              currentCalls = node[nodeKey];
            }
            if (nodeKey.indexOf('runTime') !== -1) {
              runtime = node[nodeKey];
            }
            if (nodeKey.indexOf('instanceId') !== -1) {
              instanceId = node[nodeKey];
            }
            if (nodeKey.indexOf('extendedInfo') !== -1) {
              extendedInfo = node[nodeKey];
            }
            if (nodeKey.indexOf('autoLockFinance') !== -1) {
              autoLockFinance = node[nodeKey];
            }
            if (nodeKey.indexOf('regionList') !== -1) {
              regionList = node[nodeKey];
            }
            if (nodeKey.indexOf('resourceUrl') !== -1) {
              resourceUrl = node[nodeKey];
            }
          }
        }

        // выясняем текущий статус узла
        for (let statusKey in statusPriorities) {
          if (statusPriorities.hasOwnProperty(statusKey) && statusPriorities[statusKey] === status) {
            statusName = statusKey;
          }
        }

        // формируем массив желтых нод
        if (status === 3) {
          console.log('STATUS 3')
          for (let iii = 0; iii < 1; iii++) {
            yellowNodes.push({
              fullName: key,
              currentCalls,
              runtime,
              badItems,
              instanceId,
              extendedInfo,
              autoLockFinance,
              regionList,
              resourceUrl
            });
          }
        }
        // формируем массив красных нод
        if (status === 4 || status === 5) {
          console.log('STATUS 4')
          for (let iii = 0; iii < 1; iii++) {
            redNodes.push({
              fullName: key,
              currentCalls,
              runtime,
              badItems,
              instanceId,
              extendedInfo,
              autoLockFinance,
              regionList,
              resourceUrl
            });
          }
        }

        healthBarNodes.push({name, subname, status: statusName});
      }
    }
    // console.log(yellowNodes);
    this.setState({
      data: response,
      nodes,
      healthBarNodes,
      yellowNodes,
      redNodes,
      criticalNodes
    })
  }

  render() {
    return(
      <ThemeProvider theme={this.state.theme}>
        <Wrapper>
          <HealthBar
            nodes={this.state.healthBarNodes}
          />
          <YellowBar
            nodes={this.state.yellowNodes}
          />
          <MiddleBar />
          <RedBar
            nodes={this.state.redNodes}
          />
          {/*<CriticalModal />*/}
        </Wrapper>
      </ThemeProvider>
    )
  }
}

ReactDOM.render(<App />, document.getElementById('newRoot'));