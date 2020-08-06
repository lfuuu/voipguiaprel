"use strict";
const cfg = require('./config.js');
const winston = require('winston');

function makeLogTransport(fileName) {
  let transport = [new (winston.transports.Console)()];
  if(!cfg.onlyConsoleLog) {
    transport.push(new winston.transports.File({ filename: __dirname + fileName}))
  }
  return transport;
}

const logger = winston.createLogger({
  transports: makeLogTransport('/debug.log'),
  exceptionHandlers: makeLogTransport('/exceptions.log'),
  exitOnError: false
});

module.exports = logger;