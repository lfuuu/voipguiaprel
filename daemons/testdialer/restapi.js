"use strict";

const express = require('express');
const requestIp = require('request-ip');

const amiService = require('./amiservice.js');
const cfg = require('./config.js');
const wlogger = require('./wlogger.js');

const app = express();

function logger() {
    const format = ':remote - - [:date] :method :url'
    const regexp = /:(\w+)/g;

    return function createLogger(req, res, next) {

        const values = {
            method: req["method"],
            url: req["url"],
            remote: requestIp.getClientIp(req), 
            date: new Date()
        }    
        const str = format.replace(regexp, (match, property) =>values[property]);
        wlogger.info(str);
        next();
    };
}

app.use(logger()).get('/', function (req, res) {
    res.send(`testcall daemon.ip=${req.ip}`);
});

function uuidv4() {

    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
        var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
    });
}

app.get("/docall", function (req, res, next) {
//app.use(logger()).get("/docall", function (req, res, next) {

    let query = req.query;

    if (query.num_a == undefined ||
        query.num_b == undefined ||
        query.hub == undefined ||
        query.troute == undefined
    ) {
        res.status(403).json(["error", "num_a,num_b,hub,troute - обязательные параметры"]);
    } else {

        let uuid = query.autocall_uuid == undefined ? uuidv4() : query.autocall_uuid;

        const call = {
            num_a: query.num_a,
            num_b: query.num_b,
            num_c: query.num_c || '',
            hub: query.hub,
            troute: query.troute,
            autocall_uuid: uuid,
            duration: query.duration || 1,
            oca_ip: query.nas_ip_address
        };
        console.log(call);
        amiService.doCall(cfg, call);
        res.json({ "res": "OK", "call": call, "uuid": uuidv4 });
    }
});


app.use(logger()).listen(3000, function () {
    console.log("Server running on port 3000");
});

module.exports.apiapp = app;