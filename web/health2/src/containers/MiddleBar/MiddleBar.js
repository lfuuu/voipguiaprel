import React from 'react';
import styled from 'styled-components';

const Container = styled.div`
  width: 50px;
  height: 100%;
  background: linear-gradient(to bottom, rgba(255,255,255,1) 0%,rgba(246,246,246,1) 47%,rgba(237,237,237,1) 100%);
  border-right: 1px solid #33343a;
  border-left: 1px solid #33343a;
  display: inline-block;
  float: left;
  padding: 60px 10px 10px 20px;
  box-sizing: border-box;
  position: relative;
  // box-shadow: inset -10px 0 10px -12px #FFEB3B;
`;

export default class YellowBar extends React.Component {
  render() {
    return(
      <Container>
        
      </Container>
    )
  }
}