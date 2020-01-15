"use strict";

const Config = require('./config.js');

const args = require('minimist')(process.argv.slice(2));
const CONFIG_PATH = args.config || 'testdial_cfg.json';

const cfg = Config(CONFIG_PATH);

const amiService = require('./amiservice.js');


const express = require('express');
const bodyParser = require('body-parser');

const app = express();
app.use(bodyParser.urlencoded({ extended: true }));
app.use(bodyParser.json());
app.set('trust proxy', true);

app.get('/', function (req, res) {
    
    var forwardedIpsStr = req.header('x-forwarded-for');
    var IP = '';
 
    if (forwardedIpsStr) {
       IP = forwardedIps = forwardedIpsStr.split(',')[0];  
    }

    res.send(`testcall daemon.ip=${req.ip}`);
});

function uuidv4() {

    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
        var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
    });
}

app.get("/docall", function (req, res, next) {

    let query = req.query;

    if (query.num_a == undefined ||
        query.num_b == undefined ||
        query.hub == undefined ||
        query.troute == undefined
    ) {
        res.json(["error", "num_a,num_b,hub,troute - обязательные параметры"]);
    } else {

        let uuid = query.autocall_uuid == undefined ? uuidv4() : query.autocall_uuid;

        const call = {
            num_a: query.num_a,
            num_b: query.num_b,
            hub: query.hub,
            troute: query.troute,
            autocall_uuid: uuid,
            duration: query.duration || 1
        };
        console.log(call);
        amiService.doCall(cfg, call);
        res.json({ "res": "OK", "call": call, "uuid": uuidv4 });
    }
});


app.listen(3000, function () {
    console.log("Server running on port 3000");
});
