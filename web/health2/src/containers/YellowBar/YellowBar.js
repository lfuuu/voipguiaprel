import React from 'react';
import ReactDOM from 'react-dom';
import styled from 'styled-components';

import NodeCard from '../NodeCard/NodeCard';

const Container = styled.div`
  width: calc(50% - 1px);
  height: 100%;
  border-right: 1px solid #33343a;
  background: linear-gradient(to bottom, rgba(255,255,255,1) 0%,rgba(246,246,246,1) 47%,rgba(237,237,237,1) 100%);
  // background-color: #12adf9;
  // border-right: 1px solid #33343a;
  display: inline-block;
  float: left;
  padding: 35px 50px 10px 20px;
  box-sizing: border-box;
  position: relative;
  // box-shadow: inset -10px 0 10px -12px #FFEB3B;
`;

export default class YellowBar extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      cards: this.props.nodes,
      firstCardIn: false
    };
    this.firstRef = React.createRef();
    this.lastRef = React.createRef();

    this.interval = () => {
      let firstNode = this.firstRef.current;
      this.setState({
        firstCardIn: true
      }, () => {
        console.log(this.state)
        setTimeout(() => {
          setTimeout(() => {
            this.setState((prevState) => {
              // обработать, может быть ошибка, когда нет карточек [undefined]
              let newCards = prevState.cards,
                firstCard = newCards.shift();
              newCards.push(firstCard);
              return {
                cards: newCards,
                firstCardIn: false
              }
            }, () => console.log(this.state))
          }, 200);
        }, 300);
      })
    };

    if (this.props.nodes.length > 4) {
      setInterval(this.interval, 5000);
    }
  }

  componentDidUpdate(oldProps) {
    const newNodes = this.props.nodes;
    if (newNodes !== oldProps.nodes) {
      this.setState({
        cards: newNodes
      });

      if (newNodes.length < 5) {
        clearInterval(this.interval);
      } else if (oldProps.nodes.length < 5 && newNodes.length > 4) {
        setInterval(this.interval, 5000);
      }
    }
  }

  componentWillUnmount() {
    clearInterval(this.interval);
  }

  render() {
    let cards = this.state.cards && this.state.cards.map((val, i) => (
      <NodeCard
        level="WARNING"
        key={i}
        nodeName={val.fullName}
        ref={i === 0 ? this.firstRef : (i === this.state.cards.length - 1 ? this.lastRef : '')}
        className={i === 0 && this.state.firstCardIn === true ? 'react-card-out' : ''}
        data={val}
        armorNumber={i > 8 ? (i + 1).toString() : '0' + (i + 1)}
      />
    ));
    console.log(this.props.nodes)
    return(
      <Container>
        {cards}
      </Container>
    )
  }
}