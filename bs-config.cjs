// Snippet mode: no proxy. The theme loads the Browsersync client on the
// local site (see gaia_enqueue_browsersync_client in functions.php).
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');

// Local settings (site domain, certificate paths) in an untracked .env file
try {
  process.loadEnvFile('.env');
} catch {
  // No .env file: use the defaults below
}

const localSiteDomain = process.env.LOCAL_SITE_DOMAIN || 'gaia.local';
const localCertificatesDirectory = path.join(os.homedir(), '.config/Local/run/router/nginx/certs');
const sslCertificatePath = process.env.SSL_CERT_PATH || path.join(localCertificatesDirectory, `${localSiteDomain}.crt`);
const sslKeyPath = process.env.SSL_KEY_PATH || path.join(localCertificatesDirectory, `${localSiteDomain}.key`);
const hasSslCertificate = fs.existsSync(sslCertificatePath) && fs.existsSync(sslKeyPath);

if (!hasSslCertificate) {
  console.warn(`[Browsersync] SSL certificate not found at ${sslCertificatePath}, serving over http.`);
}

module.exports = {
  port: 3000,
  ui: false,
  https: hasSslCertificate ? { cert: sslCertificatePath, key: sslKeyPath } : false,
  files: [
    'assets/css/**/*.css',
    'blocks/**/*.css',
    '**/*.php',
    'assets/js/**/*.js',
    'blocks/**/*.js',
    'blocks/**/block.json',
  ],
  ignore: ['node_modules/**'],
  injectChanges: true,
  logSnippet: false,
  open: false,
  notify: false,
  ghostMode: false,
};
