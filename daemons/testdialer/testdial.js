"use strict";

const cfg = require('./config.js');

const amiService = require('./amiservice.js');

// Включение веб-апи
const restApi = require('./restapi.js');


//const call1 = { num_a: 74951059999, num_b: 79263747216, hub: 99, troute: 26, autocall_uuid: '09916be6-0869-5e01-fa47-e96e2a43842f', duration: 1 };
//amiService.doCall(cfg, call1);
//console.log(call1);

// const AstPool = amiService.AstPool();
// AstPool.addAsterisk(1,{
//     login: cfg.ami.login, 
//     password: cfg.ami.password,
//     ip:  cfg.ami.ip, 
//     port: cfg.ami.port
// });

// AstPool.addAsterisk(2,{
//     login: cfg.ami.login, 
//     password: cfg.ami.password,
//     ip:  cfg.ami.ip, 
//     port: cfg.ami.port
// });

//setTimeout(AstPool.disconnect)