/**
 * PM2 config: run Laravel app as a server (offline/LAN).
 * Usage:
 *   pm2 start ecosystem.config.cjs
 *   pm2 save && pm2 startup   (optional: start on PC boot)
 */
module.exports = {
  apps: [
    {
      name: 'jesproject',
      script: 'server.js',
      cwd: __dirname,
      interpreter: 'node',
      instances: 1,
      autorestart: true,
      watch: false,
      max_restarts: 10,
      env: {},
    },
  ],
};
