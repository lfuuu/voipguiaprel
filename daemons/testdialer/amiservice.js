
const AmiClient = require('asterisk-ami-client');


//const call1 = { num_a: 74951059999, num_b: 79263747216, hub: 99, troute: 26, autocall_uuid: '09916be6-0869-5e01-fa47-e96e2a43842f', duration: 1 };
//amiService.doCall(cfg, call1);
//console.log(doOriginate(cfg, call1));

const doOriginate = (cfg, call) => ({
    Action: 'Originate',
    Variable: `a_number=${call.num_a}\nVariable: b_number=${call.num_b}\n` +
        `Variable: autocall_duration=${call.duration}\nVariable: hub_id=${call.hub}\nVariable: id_troute=${call.troute}\n` +
        `Variable: autocall_uuid=${call.autocall_uuid}`,
    channel: cfg.channel,
    exten: cfg.extension,
    context: cfg.context,
    priority: cfg.priority,
    waittime: cfg.waittime
});

const client = new AmiClient({
    reconnect: true,
    keepAlive: true
});

function doCall(cfg,call) {
    client.connect(cfg.ami.login, cfg.ami.password, { host: cfg.ami.ip, port: cfg.ami.port })
        .then(amiConnection => {

            client
                .on('connect', () => console.log('connect'))
                //.on('event', event => console.log(event))
                //.on('data', chunk => console.log(chunk))
                //.on('response', response => console.log(response))
                .on('disconnect', () => console.log('disconnect'))
                .on('reconnection', () => console.log('reconnection'))
                .on('internalError', error => console.log(error))
                .action({
                    Action: 'Ping'
                })
                .action(doOriginate(cfg, call))
                ;

            setTimeout(() => {
                client.disconnect();
            }, 5000);

        })
        .catch(error => console.log(error));
}

module.exports.doCall = doCall;