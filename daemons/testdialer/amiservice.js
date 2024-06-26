
"use strict";

const AmiClient = require('asterisk-ami-client');
const EventEmitter = require('events').EventEmitter;

const doOriginate = (cfg, call) => {
    let c = {
        Action: 'Originate',
        ActionID: 123,
        Variable: `a_number=${call.num_a}\nVariable: b_number=${call.num_b}\n` + ((call.num_c !== undefined) ? `Variable: c_number=${call.num_c}\n` : '') +
            `Variable: autocall_duration=${call.duration}\nVariable: hub_id=${call.hub}\nVariable: id_troute=${call.troute}\n` +
            `Variable: autocall_uuid=${call.autocall_uuid}\nVariable: oca_ip=${call.oca_ip}`,
        channel: cfg.channel,
        exten: cfg.extension,
        context: cfg.context,
        priority: cfg.priority,
        Async: 'true',
        waittime: cfg.waittime
    };
    console.log(c);
    return c;
};

const client = new AmiClient(

    {
        reconnect: true,
        keepAlive: true,
        emitEventsByTypes: true,
        emitResponsesById: true,
        dontDeleteSpecActionId: true
    }

);
// {
//     reconnect: true,
//     keepAlive: true
// });


// { Event: 'UserEvent',
//   Privilege: 'user,all',
//   UserEvent: 'AutoCallStart',
//   Uniqueid: '1579525421.384',
//   autocall_uuid: '09916be6-0869-5e01-fa47-e96e2a43842f' }

function doCall(cfg, call) {
    client.connect(cfg.ami.login, cfg.ami.password, { host: cfg.ami.ip, port: cfg.ami.port })
        .then(amiConnection => {

            client
                // .on('Dial', event => console.log(event))
                // .on('Hangup', event => console.log(event))
                // .on('Hold', event => console.log(event))
                // .on('Bridge', event => console.log(event))
                // .on('resp_123', response => {
                //     console.log(response);
                //     //client.disconnect();
                // })
                // .on('connect', () => console.log('connect'))
                // .on('event', event => console.log(event))
                // //                .on('data', chunk => console.log(chunk))
                // .on('response', response => console.log(response))
                // .on('disconnect', () => console.log('disconnect'))
                // .on('reconnection', () => console.log('reconnection'))
                // .on('internalError', error => console.log(error))
                .action({
                    Action: 'Ping'
                })
                .action(doOriginate(cfg, call))
                ;

            setTimeout(() => {
                client.disconnect();
            }, 2000);

        })
        .catch(error => console.log(error));
}

/**
 * AsteriskConn
 */

class AstPool extends EventEmitter {
    constructor(options) {
        super();
        Object.assign(this, {
            options: options,
            pool: new Map()
        }
        );
    }

    addAsterisk(id, astconn, callback) {
        const client = new AmiClient(
            {
                reconnect: true,
                keepAlive: true,
                emitEventsByTypes: true,
                emitResponsesById: true,
                dontDeleteSpecActionId: true
            });

        client.connect(astconn.login, astconn.password, { host: astconn.ip, port: astconn.port })
            .then(amiConnection => {
                this.pool.set(id, client);
                console.log(`AMI[${id}]:try connect`);
                client
                    .on('connect', () => console.log(`AMI[${id}]:connect`))
                    .on('event', (event) => console.log(`event:AMI[${id}]`,event))
                    .on('disconnect', () => console.log(`disconnect:AMI[${id}]:disconnect`))
                    .on('internalError', error => console.log(`error:AMI[${id}]:`,error))
                    .action({
                        Action: 'Ping'
                    });
                setTimeout(() => {
                    client.disconnect();
                }, 1000);

            })
            .catch(error => console.log(`AMI[${id}]:`, error));;
        if (callback !== undefined) return callback();    
    }

}


module.exports = {
    AstPool: () => new AstPool(),
    doCall: doCall
}