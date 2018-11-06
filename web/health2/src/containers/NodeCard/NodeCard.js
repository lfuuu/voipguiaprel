import React from 'react';
import styled, { keyframes } from 'styled-components';

import CardHolder from '../../components/CardHolder/CardHolder';

const Container = styled.div`
  margin-bottom: 60px;
  background: ${props => props.theme.bgCard};
  position: relative;
  color: ${props => props.theme.fontColorCard};
  padding: 10px 20px;
  box-sizing: border-box;
  text-shadow: 0px 0px 10px white;
  height: calc((100% - 200px)/4);
  // transition: height .2s linear, top .3s linear, margin .2s linear, padding .2s linear;
  top: 0;
  // box-shadow: 0 5px 20px -10px rgba(0,0,0,.6);
  box-shadow: 0 5px 30px -15px rgba(0,0,0,.6);
  border: 2px solid #4f515c;
  position: relative;
  z-index: 2000;
  &:before {
    content: '';
    position: absolute;
    border-top: 20px solid #4f515c;
    top: 0px;
    right: -2px;
    width: 100px;
    border-left: 20px solid transparent;
    box-sizing: border-box;
    z-index: 1000;
  }
`;
const Status = styled.span`
  font-size: 12px;
  color: ${props => props.level === 'DANGER' ? 'red' : 'yellow'};
  position: absolute;
  top: 1px;
  right: 10px;
  z-index: 1001;
`;

const CardBase = styled.div`
  position: absolute;
  height: calc(100% + 10px);
  width: 10px;
  top: -2px;
  right: ${props => props.right ? 'auto' : '-12px'};
  left: ${props => props.right ? '-12px' : 'auto'};
  // z-index: 500;
  background-color: white;
  &:before {
    content: '';
    width: 80px;
    border-left: ${props => props.right ? 'none' : '12px solid transparent'};
    border-right: ${props => props.right ? '12px solid transparent' : 'none'};
    border-bottom: 12px solid #4f515c;
    position: absolute;
    right: ${props => props.right ? 'auto' : '1px'};
    left: ${props => props.right ? '1px' : 'auto'};
    top: -12px;
  }
  &:after {
    content: '';
    width: 80px;
    border-left: ${props => props.right ? 'none' : '10px solid transparent'};
    border-right: ${props => props.right ? '10px solid transparent' : 'none'};
    border-bottom: 10px solid white;
    position: absolute;
    right: ${props => props.right ? 'auto' : '0'};
    left: ${props => props.right ? '0' : 'auto'};
    top: -10px;
  }
`;
const CardBaseBottomRight = styled.div`
  position: absolute;
  height: 11px;
  width: 22px;
  bottom: -5px;
  right: ${props => props.right ? 'auto' : '0'};
  left: ${props => props.right ? '0' : 'auto'};
  background-color: white;
  border-bottom: 2px solid #4f515c;
  box-sizing: border-box;
  &:before {
    // content: '';
    width: 80px;
    height: 20px;
    border-left: ${props => props.right ? 'none' : '12px solid transparent'};
    border-right: ${props => props.right ? '12px solid transparent' : 'none'};
    border-top: 12px solid #4f515c;
    position: absolute;
    right: ${props => props.right ? 'auto' : '100%'};
    left: ${props => props.right ? '100%' : 'auto'};
    top: 0;
  }
  &:after {
    // content: '';
    width: 80px;
    height: 20px;
    border-left: ${props => props.right ? 'none' : '10px solid transparent'};
    border-right: ${props => props.right ? '10px solid transparent' : 'none'};
    border-top: 10px solid white;
    position: absolute;
    right: ${props => props.right ? 'auto' : '100%'};
    left: ${props => props.right ? '100%' : 'auto'};
    top: 0;
  }
`;
const CardBaseBottomMid = styled.div`
  position: absolute;
  height: 8px;
  width: 98px;
  bottom: -2px;
  right: ${props => props.right ? 'auto' : '22px'};
  left: ${props => props.right ? '22px' : 'auto'};
  background-color: white;
  // border-bottom: 2px solid #4f515c;
  box-sizing: border-box;
  &:before {
    content: '';
    width: 93px;
    border-right: ${props => props.right ? 'none' : '7px solid transparent'};
    border-left: ${props => props.right ? '7px solid transparent' : 'none'};
    border-top: 7px solid #4f515c;
    position: absolute;
    right: ${props => props.right ? 'auto' : '-2px'};
    left: ${props => props.right ? '-2px' : 'auto'};
    top: calc(100% + 1px);
  }
  &:after {
    content: '';
    width: 93px;
    border-right: ${props => props.right ? 'none' : '6px solid transparent'};
    border-left: ${props => props.right ? '6px solid transparent' : 'none'};
    border-top: 6px solid white;
    position: absolute;
    right: ${props => props.right ? 'auto' : '0'};
    left: ${props => props.right ? '0' : 'auto'};
    top: calc(100%);
  }
`;
const CardBaseBottomLeft = styled.div`
  position: absolute;
  height: 5px;
  width: 10px;
  bottom: 1px;
  right: ${props => props.right ? 'auto' : '118px'};
  left: ${props => props.right ? '118px' : 'auto'};
  background-color: white;
  // border-bottom: 2px solid #4f515c;
  box-sizing: border-box;
  &:before {
    content: '';
    border-left: ${props => props.right ? 'none' : '16px solid transparent'};
    border-right: ${props => props.right ? '16px solid transparent' : 'none'};
    border-top: 16px solid #4f515c;
    position: absolute;
    right: ${props => props.right ? 'auto' : '2px'};
    left: ${props => props.right ? '2px' : 'auto'};
    top: 0;
  }
  &:after {
    content: '';
    border-left: ${props => props.right ? 'none' : '14px solid transparent'};
    border-right: ${props => props.right ? '14px solid transparent' : 'none'};
    border-top: 14px solid white;
    position: absolute;
    right: ${props => props.right ? 'auto' : '2px'};
    left: ${props => props.right ? '2px' : 'auto'};
    top: 0;
  }
`;
const Tech = styled.span`
  color: #00A9FF;
  font-size: 10px;
  position: absolute;
  bottom: -6px;
  right: ${props => props.right ? 'auto' : '45px'};
  left: ${props => props.right ? '45px' : 'auto'};
`;

const BlinkWrapper = styled.div`
  width: 100%;
  height: 100%;
  position: absolute;
  left: 0;
  top: 0;
  overflow: hidden;
  z-index: -1;
`;

// const healthPulse = keyframes`
//   0% {left: -50%;}
//   9% {left: 140%;}
//   100% {left: 150%;}
// `;
// const Blink = styled.div`
//   position: absolute;
//   transform: rotate(20deg);
//   background-color: rgba(255, 0, 0, .3);
//   // background-color: blue;
//   width: 100px;
//   height: 150%;
//   top: -25%;
//   left: -50%;
//   animation: ${healthPulse} 3s linear ${props => props.delay}s infinite;
// `;
// const healthPulse = keyframes`
//   0% {background:radial-gradient(ellipse at center, rgba(255,255,255,1) 0%, rgba(255,0,0,1) 100%);background-size:0%}
//   9% {background:radial-gradient(ellipse at center, rgba(255,255,255,1) 0%, rgba(255,0,0,1) 100%);background-size:100%}
//   10% {background:transparent;background-size:100%}
//   100% {background:transparent;background-size:100%}
// `;
// const Blink = styled.div`
//   position: absolute;
//   // transform: rotate(20deg);
//   background-size:100%;
//   // background:radial-gradient(ellipse at center, rgba(255,255,255,1) 0%, rgba(255,0,0,1) 100%);
//   // background-color: rgba(255, 0, 0, .3);
//   background-color: transparent;
//   width: 100%;
//   height: 100%;
//   top: 0;
//   left: 0;
//   animation: ${healthPulse} 3s linear ${props => props.delay}s infinite;
// `;

const healthPulse = keyframes`
  // 0% {background:radial-gradient(ellipse at center, rgba(255,255,255,0) 0%, rgba(255,0,0,0) 100%);}
  // 9% {background:radial-gradient(ellipse at center, rgba(255,255,255,1) 0%, rgba(255,0,0,1) 100%);}
  // 10% {background:transparent;}
  // 100% {background:transparent;}
  0% {opacity: 0}
  9% {opacity: 1}
  // 10% {opacity: .8}
  100% {opacity: 0}
`;
const Blink = styled.div`
  position: absolute;
  opacity: 0;
  // transform: rotate(20deg);
  // background-size:10% 10%;
  background: ${props => props.warning ? 'linear-gradient(to right, rgba(255,255,255,1) 0%, rgba(255,255,0,1) 100%)' : 'linear-gradient(to right, rgba(255,0,0,1) 0%, rgba(255,255,255,1) 100%)' };
  // background-color: rgba(255, 0, 0, .3);
  // background-color: transparent;
  width: 100%;
  height: 100%;
  // border-radius: 50%;
  top: 0;
  left: 0;
  // animation: ${healthPulse} 2.5s linear ${props => props.delay}s infinite;
  animation: ${healthPulse} 2.5s linear infinite;
`;

const Card = styled.div`
  z-index: 1000;
`;

const badItemsPulse = keyframes`
  0% {opacity: 0}
  50% {opacity: 1}
  100% {opacity: 0}
`;
const BadItemContainer = styled.div`
  opacity: 0;
  animation: ${badItemsPulse} 2.5s linear infinite;
`;
const BadItem = styled.span`
  display: inline-block;
  width: 320px;
  height: 70px;
  overflow-y: hidden;
  word-wrap: break-word;
`;

export default class NodeCard extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      visibleBadItem: 0
    };
    this.interval = () => {
      if (this.state.visibleBadItem < this.props.data.badItems.length - 1) {
        this.setState((prevState) => {
          return {
            visibleBadItem: prevState.visibleBadItem + 1
          };
        });
      } else {
        this.setState({visibleBadItem: 0});
      }
    };

    if (this.props.data.badItems.length > 1) {
      setInterval(this.interval, 2500);
    }
  }

  getSubtitle(title) {
    let array = title.split(' ');
    array.shift();
    return array.join(' ');
  }

  componentDidUpdate(oldProps) {
    console.log(oldProps.data.badItems)
    const newItems = this.props.data.badItems;
    if (newItems !== oldProps.data.badItems) {
      if (this.props.data.badItems.length < 2) {
        clearInterval(this.interval);
      } else if (oldProps.data.badItems.length < 2 && newItems.length > 1) {
        setInterval(this.interval, 2500);
      }
    }
  }

  componentWillUnmount() {
    clearInterval(this.interval);
  }

  render() {
    console.log('render')
    let badItems = this.props.data.badItems.map((val, i) => {
      return (
        <BadItemContainer
          key={i}
          style={i === this.state.visibleBadItem ?
            {position: 'absolute', fontSize: 14} :
            {position: 'absolute', display: 'none'}}
        >
          <strong>{val.itemId}: </strong><BadItem>{val.itemStatusMessage}</BadItem>
        </BadItemContainer>
      )
    });

    return(
      <Container className={`${this.props.className}`}>
        <CardBase right={this.props.right}>
          <CardBaseBottomRight right={this.props.right} />
          <CardBaseBottomMid right={this.props.right} />
          <CardBaseBottomLeft right={this.props.right} />
          <Tech right={this.props.right}>MCN&nbsp;TECH</Tech>
        </CardBase>
        <BlinkWrapper>
          {this.props.level === 'WARNING' ? (
            <Blink warning delay={(Math.random() * 5).toFixed(2)} />
          ) : (
            <Blink delay={(Math.random() * 5).toFixed(2)} />
          )}
        </BlinkWrapper>
        <Card>
          <Status level={this.props.level}>{this.props.level}</Status>
          <span className='node-title'>{this.props.nodeName.split(' ').length > 1 ? this.props.nodeName.split(' ')[0] : this.props.nodeName}</span>
          <span className='node-subtitle' style={{display: 'block'}}>{this.props.nodeName.split(' ').length > 1 ? this.getSubtitle(this.props.nodeName) : ''}</span>
          <div className='node-header-info'>
            {typeof this.props.data === 'object' && this.props.data.runtime.length > 0 && (
              <span className='runtime'>время работы: <span className='hours'>04</span><span className='time-pulse'>:</span><span className='minutes'>47</span></span>
            )}
            {this.props.data.extendedInfo.length > 0 ? `${this.props.data.extendedInfo}` : ' | '}
            {this.props.data && this.props.data.currentCalls && (
              <span>звонки: {this.props.data.currentCalls}</span>
            )}
          </div>

          <div className='problematic-items-display'>
            {badItems}
          </div>

          <div style={{position: 'absolute', display: 'flex', bottom: 10, left: 0, flexDirection: 'column'}}>
            <span style={{width: '10px', height: '6px', marginBottom: 5, backgroundColor: '#4f515c', borderBottomRightRadius: 4, borderTopRightRadius: 4}}></span>
            <span style={{width: '10px', height: '6px', marginBottom: 5, backgroundColor: '#4f515c', borderBottomRightRadius: 4, borderTopRightRadius: 4}}></span>
            <span style={{width: '10px', height: '6px', marginBottom: 5, backgroundColor: '#4f515c', borderBottomRightRadius: 4, borderTopRightRadius: 4}}></span>
          </div>
          <CardHolder right={this.props.right} armorNumber={this.props.armorNumber}/>
          {/*<div className="circle-ripple"></div>*/}
        </Card>
      </Container>
    )
  }
}