/**
 * PM2 config: run Laravel app as a server (offline/LAN).
 * Uses server.cjs (Node) so PHP runs with no visible CMD window.
 * Usage:
 *   pm2 start ecosystem.config.cjs
 *   pm2 save
 *   Then for start on Windows boot: pm2-windows-startup (see docs/SYSTEM_EXPLAINED.md)
 */
module.exports = {
  apps: [
    {
      name: 'jesproject',
      script: 'server.cjs',
      cwd: __dirname,
      interpreter: 'node',
      exec_mode: 'fork',
      instances: 1,
      autorestart: true,
      watch: false,
      max_restarts: 10,
      env: {
        PHP_BINARY: process.env.PHP_BINARY || undefined,
      },
    },
  ],
};
