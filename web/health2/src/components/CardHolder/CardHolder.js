import React from 'react';
import styled from 'styled-components';

const Base = styled.div`
  position: absolute;
  height: calc(100% + 34px);
  width: 20px;
  background-color: white;
  box-sizing: border-box;
  border-right: ${props => props.right ? 'none' : '2px solid #4f515c'};
  border-left: ${props => props.right ? '2px solid #4f515c' : 'none'};
  right: ${props => props.right ? 'auto' : '-42px'};
  left: ${props => props.right ? '-42px' : 'auto'};
  top: -22px;
`;
// ${props => props.right ? '' : ''}
const Container = styled.div`
  position: absolute;
  height: calc(100% - 25px);
  width: 10px;
  background-color: #4f515c;
  left: ${props => props.right ? 'auto' : '-10px'};
  right: ${props => props.right ? '-10px' : 'auto'};
  top: 10px;
`;
const Top = styled.div`
  position: absolute;
  top: -5px;
  right: ${props => props.right ? 'auto' : '0'};
  left: ${props => props.right ? '0' : 'auto'};
  width: 30px;
  height: 5px;
  background-color: #4f515c;
  &:before {
    content: '';
    border-left: ${props => props.right ? 'none' : '5px solid transparent'};
    border-right: ${props => props.right ? '5px solid transparent' : 'none'};
    border-bottom: 5px solid #4f515c;
    position: absolute;
    left: ${props => props.right ? 'auto' : '-5px'};
    right: ${props => props.right ? '-5px' : 'auto'};
    top: 0;
  }
`;

const Bottom = styled.div`
  position: absolute;
  bottom: 0;
  right: ${props => props.right ? 'auto' : '0'};
  left: ${props => props.right ? '0' : 'auto'};
  background-color: #4f515c;
  &:before {
    content: '';
    border-left: ${props => props.right ? 'none' : '10px solid #4f515c'};
    border-right: ${props => props.right ? '10px solid #4f515c' : 'none'};
    border-bottom: 10px solid transparent;
    position: absolute;
    right: ${props => props.right ? 'none' : '0'};
    left: ${props => props.right ? '0' : 'none'};
  }
  &:after {
    // content: '';
    border-left: ${props => props.right ? 'none' : '10px solid transparent'};
    border-left: ${props => props.right ? 'none' : '10px solid transparent'};
    border-top: 5px solid #4f515c;
    position: absolute;
    right: ${props => props.right ? 'none' : '0'};
    left: ${props => props.right ? '0' : 'none'};
    bottom: -5px;
  }
`;

const BaseTop = styled.div`
  position: absolute;
  bottom: 100%;
  right: ${props => props.right ? 'auto' : '-2px'};
  left: ${props => props.right ? '-2px' : 'auto'};
  height: 10px;
  width: 30px;
  // background-color: white;
  background-color: transparent;
  box-sizing: border-box;
  // border: 2px solid #4f515c;
  // border-bottom: none;
  // border-left: none;
  // border-right: none;
  &:before {
    content: '';
    width: 20px;
    border-left: ${props => props.right ? 'none' : '16px solid transparent'};
    border-right: ${props => props.right ? '16px solid transparent' : 'none'};
    border-bottom: 16px solid #4f515c;
    position: absolute;
    left: ${props => props.right ? 'none' : '-16px'};
    right: ${props => props.right ? '-16px' : 'none'};    
    top: 0px;
  }
  &:after {
    content: '';
    width: 20px;
    border-left: ${props => props.right ? 'none' : '15px solid transparent'};
    border-right: ${props => props.right ? '15px solid transparent' : 'none'};
    border-bottom: 15px solid white;
    position: absolute;
    left: ${props => props.right ? 'none' : '-15px'};
    right: ${props => props.right ? '-15px' : 'none'};
    top: 2px;
  }
`;
const BaseTopBorder = styled.div`
  position: absolute;
  height: 1px;
  width: 24px;
  bottom: -5px;
  left: ${props => props.right ? 'auto' : '-14px'};
  right: ${props => props.right ? '-14px' : 'auto'};
  background-color: grey;
  z-index: 501;
`;
const BaseTopRightCorner = styled.div`
  position: absolute;
  height: 100%;
  width: 100%;
  top: 0;
  right: ${props => props.right ? 'auto' : '0'};
  left: ${props => props.right ? '0' : 'auto'};
  z-index: 500;
  overflow-y: hidden;
  background-color: transparent;
  &:before {
    content: '';
    width: 0;
    height: 0;
    border-right: ${props => props.right ? 'none' : '10px solid transparent'};
    border-left: ${props => props.right ? '10px solid transparent' : 'none'};
    border-bottom: 10px solid #4f515c;
    position: absolute;
    right: ${props => props.right ? 'auto' : '0'};
    left: ${props => props.right ? '0' : 'auto'};
    top: 0;
  }
  &:after {
    content: '';
    width: 0;
    height: 0;
    border-right: ${props => props.right ? 'none' : '10px solid transparent'};
    border-left: ${props => props.right ? '10px solid transparent' : 'none'};
    border-bottom: 10px solid white;
    position: absolute;
    right: ${props => props.right ? 'auto' : '0'};
    left: ${props => props.right ? '0' : 'auto'};
    top: 2px;
  }
`;
const BaseBottom = styled.div`
  position: absolute;
  top: 100%;
  right: ${props => props.right ? 'none' : '-2px'};
  left: ${props => props.right ? '-2px' : 'none'};
  height: 15px;
  width: 35px;
  background-color: white;
  box-sizing: border-box;
  border: 2px solid #4f515c;
  border-top: none;
  border-bottom-left-radius: ${props => props.right ? '0' : '10px'};
  border-bottom-right-radius: ${props => props.right ? '10px' : '0'};
  &:before {
    content: '';
    border-left: ${props => props.right ? 'none' : '15px solid transparent'};
    border-right: ${props => props.right ? '15px solid transparent' : 'none'};
    border-bottom: 15px solid #4f515c;
    position: absolute;
    left: ${props => props.right ? 'none' : '-2px'};
    right: ${props => props.right ? '-2px' : 'none'};
    bottom: 100%;
  }
  &:after {
    content: '';
    border-left: ${props => props.right ? 'none' : '15px solid transparent'};
    border-right: ${props => props.right ? '15px solid transparent' : 'none'};
    border-bottom: 15px solid white;
    position: absolute;
    left: ${props => props.right ? 'none' : '0'};
    right: ${props => props.right ? '0' : 'none'};
    bottom: 100%;
  }
`;

const Rivets = styled.div`
  position: absolute;
  width: 100%;
  height: 100%;
  top: 0;
  left: ${props => props.right ? 'none' : '0'};
  right: ${props => props.right ? '0' : 'none'};
  z-index: 1000;
`;
const Rivet = styled.div`
  position: absolute;
  border-radius: 50%;
  border: 1px solid #4f515c;
  width: 2px;
  height: 2px;
`;

const Spray = styled.div`
  width: 100%;
  height: 100%;
  position: absolute;
  overflow: hidden;
  
`;
const Decal = styled.span`
  display: block;
  position: absolute;
  left: -50%;
  transform: rotate(-45deg);
  font-size: 12px;
  color: #00A9FF;
  width: 200%;
  height: 6px;
  // text-align: center;
`;

const Holder = styled.div`
  width: 0px;
  height: 0px;
  top: 50%;
  // margin-top: -16px;
  right: ${props => props.right ? 'auto' : '-2px'};
  left: ${props => props.right ? '-2px' : 'auto'};
  position: absolute;
  &:before {
    content: '';
    bottom: 1px;
    right: ${props => props.right ? 'auto' : '-10px'};
    left: ${props => props.right ? '-10px' : 'auto'};
    border-left: ${props => props.right ? 'none' : '10px solid #4f515c'};
    border-right: ${props => props.right ? '10px solid #4f515c' : 'none'};
    border-top: 6px solid transparent;
    width: 0;
    height: 10px;
    display: inline-block;
    position: absolute;
  }
  &:after {
    content: '';
    top: 1px;
    right: ${props => props.right ? 'auto' : '-10px'};
    left: ${props => props.right ? '-10px' : 'auto'};
    border-left: ${props => props.right ? 'none' : '10px solid #4f515c'};
    border-right: ${props => props.right ? '10px solid #4f515c' : 'none'};
    border-bottom: 6px solid transparent;
    width: 0;
    height: 10px;
    display: inline-block;
    position: absolute;
  }
`;

export default class CardHolder extends React.Component {
  constructor(props) {
    super(props);
    this.state = {

    };
  }

  render() {
    return(
      <Base right={this.props.right}>
        {/*<BaseTopFrame>*/}

        {/*</BaseTopFrame>*/}
        <BaseTop right={this.props.right}>
          <BaseTopRightCorner right={this.props.right}/>
          <BaseTopBorder right={this.props.right}/>
          {this.props.right ? (
            <Rivets right={this.props.right}>
              <Rivet style={{top: 3, right: 3}} />
              <Rivet style={{bottom: -3, right: -5}} />
              <Rivet style={{top: 3, left: 12}} />
              <Rivet style={{bottom: -3, left: 5}} />
            </Rivets>
          ) : (
            <Rivets>
              <Rivet style={{top: 3, left: 3}} />
              <Rivet style={{bottom: -3, left: -5}} />
              <Rivet style={{top: 3, right: 12}} />
              <Rivet style={{bottom: -3, right: 5}} />
            </Rivets>
          )}
        </BaseTop>
        <Container right={this.props.right}>
          <Top right={this.props.right}/>
          <Bottom right={this.props.right}/>
        </Container>
        {this.props.right ? (
          <Holder right={this.props.right} />
        ) : (
          <Holder />
        )}
        <BaseBottom right={this.props.right}>
          {this.props.right ? (
            <Rivets right={this.props.right}>
              <Rivet style={{top: -2, right: 5}} />
              <Rivet style={{bottom: 3, right: 5}} />
              <Rivet style={{bottom: 3, left: 3}} />
            </Rivets>
          ) : (
            <Rivets>
              <Rivet style={{top: -2, left: 5}} />
              <Rivet style={{bottom: 3, left: 5}} />
              <Rivet style={{bottom: 3, right: 3}} />
            </Rivets>
          )}
        </BaseBottom>
        {this.props.right ? (
          <Rivets right={this.props.right}>
            <Rivet style={{bottom: '50%', left: 3}} />
            <Rivet style={{bottom: 12, right: 3}} />
          </Rivets>
        ) : (
          <Rivets>
            <Rivet style={{bottom: '50%', right: 3}} />
            <Rivet style={{bottom: 12, left: 3}} />
          </Rivets>
        )}
        <Spray>
          <Decal style={{backgroundColor: '#4f515c', bottom: 56}}></Decal>
          <Decal style={{backgroundColor: 'white', bottom: 48}}></Decal>
          <Decal style={{backgroundColor: '#00A9FF', bottom: 40}}></Decal>
          <Decal style={{backgroundColor: 'transparent', bottom: 38, marginLeft: 5}}>{this.props.armorNumber}</Decal>
        </Spray>
      </Base>
    )
  }
}