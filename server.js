/**
 * Wrapper so PM2 can run the Laravel dev server.
 * Run from project root: pm2 start ecosystem.config.cjs
 */
const { spawn } = require('child_process');
const path = require('path');

const cwd = path.join(__dirname);
const php = process.env.PHP_BINARY || 'php';
const child = spawn(php, ['artisan', 'serve', '--host=0.0.0.0', '--port=8000'], {
  cwd,
  stdio: 'inherit',
  shell: true,
});

child.on('error', (err) => {
  console.error('Failed to start server:', err);
  process.exit(1);
});

child.on('exit', (code) => {
  process.exit(code || 0);
});

process.on('SIGINT', () => child.kill('SIGINT'));
process.on('SIGTERM', () => child.kill('SIGTERM'));
