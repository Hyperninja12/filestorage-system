/**
 * Wrapper so PM2 can run the Laravel dev server with no visible window.
 * Finds PHP directly (Herd or PATH), spawns it without shell/cmd.exe.
 * (.cjs so Node treats as CommonJS when package.json has "type": "module")
 */
const { spawn } = require('child_process');
const path = require('path');
const fs = require('fs');

const cwd = path.join(__dirname);

function getPhpPath() {
  if (process.env.PHP_BINARY) return process.env.PHP_BINARY;
  if (process.platform !== 'win32') return 'php';
  const user = process.env.USERPROFILE || '';
  const vers = ['php84', 'php83', 'php82', 'php81', 'php80'];
  for (const ver of vers) {
    const exe = path.join(user, '.config', 'herd', 'bin', ver, 'php.exe');
    if (fs.existsSync(exe)) return exe;
  }
  return 'php';
}

/**
 * Windows: `where php` / Herd often returns php.bat. Node spawn() must not use .bat with shell:false.
 */
function resolvePhpExecutable(raw) {
  if (process.platform !== 'win32' || !raw) return raw;
  const lower = String(raw).toLowerCase();
  if (!lower.endsWith('.bat') && !lower.endsWith('.cmd')) return raw;
  const user = process.env.USERPROFILE || '';
  const vers = ['php84', 'php83', 'php82', 'php81', 'php80'];
  for (const ver of vers) {
    const exe = path.join(user, '.config', 'herd', 'bin', ver, 'php.exe');
    if (fs.existsSync(exe)) return exe;
  }
  return raw;
}

const phpExe = resolvePhpExecutable(getPhpPath());

const logsDir = path.join(cwd, 'storage', 'logs');
try {
  fs.mkdirSync(logsDir, { recursive: true });
} catch (_) {}
const logStream = fs.createWriteStream(path.join(logsDir, 'serve-pm2.log'), { flags: 'a' });
logStream.write(`\n--- ${new Date().toISOString()} ---\n`);
logStream.write(`[jesproject] PHP: ${phpExe}\n`);

console.log(`[jesproject] Spawning: "${phpExe}" artisan serve --host=0.0.0.0 --port=8000`);

const child = spawn(phpExe, ['artisan', 'serve', '--host=0.0.0.0', '--port=8000'], {
  cwd,
  shell: false,
  windowsHide: true,
  stdio: ['ignore', logStream, logStream],
});

child.on('error', (err) => {
  console.error('[jesproject] Failed to start PHP:', err.message);
  process.exit(1);
});

child.on('exit', (code, signal) => {
  console.log('[jesproject] PHP exited', { code, signal });
  process.exit(code || 0);
});

process.on('SIGINT', () => child.kill('SIGINT'));
process.on('SIGTERM', () => child.kill('SIGTERM'));
