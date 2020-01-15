const fs = require('fs');
const util = require('util');

function build_cfg_rec(cfg_json) {
    return {
        hubs: cfg_json.hubs || { 0: "none" },
        ami: {
            ip: cfg_json.ami.ip || "127.0.0.1",
            port: cfg_json.ami.port || 5038,
            login:  cfg_json.ami.login || "autocall",
            password: cfg_json.ami.password || "none"
        },
        channel: cfg_json.channel || "Local/s@autocall-out/n",
        extension: cfg_json.extension || "s",
        context: cfg_json.context || "autocall",
        priority: cfg_json.priority || 1,
        waittime: cfg_json.waittime || 3000
    }
}

function config(filename) {

    try {
        var cfg = JSON.parse(fs.readFileSync(filename, 'utf-8'));
    } catch (e) {
        util.log('Failed to load configuration from ' + filename);
        process.exit(-1);
    }

    return build_cfg_rec(cfg);
}

module.exports = config;