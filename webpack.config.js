var webpack = require('webpack');

module.exports = {
    entry: {
        calendar: __dirname + '/site/scripts/calendar.js'
    },
    output: {
	path: '/srv/http/site/scripts/',
        filename: '[name].bundle.js'
    }
}
