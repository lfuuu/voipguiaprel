"use strict";

const winston = require('winston');

const logger = winston.createLogger({
  transports: [
    new (winston.transports.Console)(),
    new winston.transports.File({ filename: __dirname + '/debug.log'})
  ],
  exceptionHandlers: [
    new (winston.transports.Console)(),
    new winston.transports.File({ filename: __dirname + '/exceptions.log'})
  ],
  exitOnError: false
});

module.exports = logger;