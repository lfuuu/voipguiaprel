import React from 'react';
import styled from 'styled-components';

const Container = styled.div`
  background-color: ${props => props.theme.bgCard};
  height: 38px;
  margin: 6px 20px 6px 10px;
  position: relative;
  z-index: 1;
  border-radius: 6px;
  border-top-right-radius: 0;
  // border-bottom-left-radius: 0;
  border: 2px solid #4f515c;
  box-sizing: border-box;
  cursor: pointer;
  
  &:before {
    content: '';
    position: absolute;
    top: -2px;
    right: -12px;
    z-index: 2;
    width: 10px;
    height: 2px;
    background-color: #4f515c;
    // border-top: 10px solid transparent;
    // border-left: 10px solid #222326;
  }
  &:after {
    content: '';
    position: absolute;
    top: 3px;
    right: -25px;
    z-index: 2;
    width: 15px;
    height: 2px;
    transform: rotate(45deg);
    background-color: #4f515c;
    // border-top: 10px solid transparent;
    // border-left: 10px solid #222326;
  }
`;

const NodeText = styled.div`
  text-align: center;
`;
const NodeSubname = styled.div`
  color: #12adf9;
  font-size: 10px;
`;

export default class HealthyBarItem extends React.Component {
  render() {
    return (
      <Container>
        <NodeText>
          <span>{this.props.nodeName}</span>
          {this.props.nodeSubname.toString().length > 0 && (
            <NodeSubname>
              <span>{this.props.nodeSubname}</span>
            </NodeSubname>
          )}
        </NodeText>
        {this.props.healthStatus !== 'STATUS_OK' && this.props.healthStatus !== 'STATUS_SYNC' && (
          <span
            className="circle-ripple"
            style={{position: 'absolute', right: '-25px', top: 9}}
          />
        )}
      </Container>
    );
  }
}