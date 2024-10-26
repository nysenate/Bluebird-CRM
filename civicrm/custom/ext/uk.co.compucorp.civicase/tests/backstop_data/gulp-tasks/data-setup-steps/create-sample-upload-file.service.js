var fs = require('fs');

/**
 * Create Sample Upload File
 */
function createSampleUploadFile () {
  fs.writeFileSync('sample.txt', 'Sample Text');
}

module.exports = createSampleUploadFile;
