const path = require('path');

module.exports = {
  mode: 'development',
  // entry: './srcOld/js/health.js',
  entry: './src/App.js',
  output: {
    filename: 'health.js',
    path: path.resolve(__dirname, 'dist')
  },
  module: {
    rules: [
      {
        test: /\.(js|jsx)$/,
        exclude: /node_modules/,
        loader: 'babel-loader',
      }
    ]
  },
  watch: false,
};