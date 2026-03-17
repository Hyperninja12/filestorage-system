/**
 * PM2 config: run Laravel app as a server (offline/LAN).
 * Uses a .bat file so PHP runs with correct PATH on Windows.
 * Usage:
 *   pm2 start ecosystem.config.cjs
 *   pm2 save
 *   Then for start on Windows boot: pm2-windows-startup (see docs/SYSTEM_EXPLAINED.md)
 */
module.exports = {
  apps: [
    {
      name: 'jesproject',
      script: 'run-jesproject.bat',
      interpreter: 'cmd.exe',
      interpreter_args: '/c',
      cwd: __dirname,
      instances: 1,
      autorestart: true,
      watch: false,
      max_restarts: 10,
    },
  ],
};
