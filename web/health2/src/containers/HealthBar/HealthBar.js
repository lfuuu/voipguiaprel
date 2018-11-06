import React from 'react';
import styled from 'styled-components';

import HealthBarItem from '../../components/HealthBarItem/HealthBarItem';

const Container = styled.div`
  height: 100%;
  width: 100px;
  background: ${props => props.theme.bgHealthBar};
  border-left: none;
  border-right: 3px solid #12adf9;
  display: flex;
  flex-direction: column;
  justify-content: center;
`;

export default class HealthBar extends React.Component {
  render() {
    const healthBarItems = this.props.nodes.map((node, i) =>
      <HealthBarItem
        key={i}
        nodeName={node.name}
        nodeSubname={node.subname}
        healthStatus={node.status}
      />
    );

    return(
      <Container>
        {healthBarItems}
      </Container>
    )
  }
}