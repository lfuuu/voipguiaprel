import React from 'react';
import styled, { keyframes } from 'styled-components';

const Container = styled.div`
  width: 100vh;
  height: 100%;
  position: fixed;
  top: 0;
  left: 0;
  background: radial-gradient(ellipse at center, rgba(255,255,255,0.75) 0%, rgba(255,255,255,0.82) 26%, rgba(250,250,250,0.88) 52%, rgba(250,250,250,1) 100%);
  display: inline-block;
  // box-shadow: inset -10px 0 10px -12px #FFEB3B;
  z-index: 10000;
`;

const Modal = styled.div`
  width: 500px;
  height: 500px;
  position: absolute;
  margin: auto;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  background: ${props => props.theme.bgCard};
  border: 2px solid #959595;
  padding: 20px;
  box-sizing: border-box;
  &:before {
    content: '';
    position: absolute;
    border-bottom: 20px solid #959595;
    bottom: 0px;
    right: -2px;
    width: 100px;
    border-left: 20px solid transparent;
    box-sizing: border-box;
  }
  &:after {
    content: '';
    position: absolute;
    border-bottom: 19px solid #fafafa;
    bottom: -1px;
    right: 0px;
    width: 96px;
    border-left: 19px solid transparent;
    box-sizing: border-box;
  }
`;
const Status = styled.span`
  font-size: 12px;
  color: red;
  position: absolute;
  bottom: 2px;
  right: 15px;
  z-index: 20000;
`;
const ModalHeader = styled.h1`
  color: red;
  text-align: center;
`;

const pulse = keyframes`
  0% {border-color: transparent}
  50% {border-color: rgba(255, 0, 0, .6)}
  100% {border-color: transparent}
`;

const ModalWrapperS = styled.div`
  background-color: transparent;
  border: 40px solid transparent;
  width: 500px;
  height: 500px;
  position: absolute;
  margin: auto;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  animation: ${pulse} 2s linear infinite;
`;
const ModalWrapperM = styled.div`
  background-color: transparent;
  border: 40px solid transparent;
  width: 580px;
  height: 580px;
  position: absolute;
  margin: auto;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  animation: ${pulse} 2s linear infinite;
`;
const ModalWrapperL = styled.div`
  background-color: transparent;
  border: 40px solid transparent;
  width: 660px;
  height: 660px;
  position: absolute;
  margin: auto;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  animation: ${pulse} 2s linear infinite;
`;
const ModalWrapperXL = styled.div`
  background-color: transparent;
  border: 40px solid transparent;
  width: 740px;
  height: 740px;
  position: absolute;
  margin: auto;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  animation: ${pulse} 2s linear infinite;
`;
const ModalWrapperXXL = styled.div`
  background-color: transparent;
  border: 31px solid rgba(255, 0, 0, .2);
  width: 820px;
  height: 820px;
  position: absolute;
  margin: auto;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  animation: ${pulse} 2s linear infinite;
`;


export default class CriticalModal extends React.Component {
  render() {
    return(
      <Container>
        <ModalWrapperXXL/>
        <ModalWrapperXL/>
        <ModalWrapperL/>
        <ModalWrapperM/>
        <ModalWrapperS/>
        <Modal>
          <ModalHeader>A T T E N T I O N</ModalHeader>
          <p>Следующие узлы в данный момент недоступны:</p>
          <div>
            <div>reg89.mcntelecom.ru</div>
            <div>reg14.mcntelecom.ru</div>
            <div>reg15.mcntelecom.ru</div>
          </div>
          <Status>CRITICAL</Status>
        </Modal>
      </Container>
    )
  }
}