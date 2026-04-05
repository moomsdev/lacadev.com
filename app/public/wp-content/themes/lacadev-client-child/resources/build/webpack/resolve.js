/**
 * The internal dependencies.
 */
const utils = require('../lib/utils');

module.exports = {
  modules: [utils.srcScriptsPath(), 'node_modules'],
  extensions: ['.js', '.jsx', '.json', '.css', '.scss'],
  alias: {
    // Các alias cốt lõi trỏ thẳng về Parent Theme để build từ gốc, không cần copy
    '@config': path.resolve(__dirname, '../../../../lacadev-client/config.json'),
    '@scripts': path.resolve(__dirname, '../../../../lacadev-client/resources/scripts'),
    '@styles': path.resolve(__dirname, '../../../../lacadev-client/resources/styles'),
    '@images': path.resolve(__dirname, '../../../../lacadev-client/resources/images'),
    '@fonts': path.resolve(__dirname, '../../../../lacadev-client/resources/fonts'),
    '@vendor': path.resolve(__dirname, '../../../../lacadev-client/resources/vendor'),
    // Phục vụ cho output của child theme
    '@dist': utils.distPath(),
    '@child': utils.srcPath(),
    '@parent': require('path').resolve(__dirname, '../../../../lacadev-client/resources'),
    '~': utils.themeRootPath('node_modules'),
    'isotope': 'isotope-layout',
  },
};
