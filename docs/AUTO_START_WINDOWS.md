# Auto-start JES Project when the server PC boots (Windows)

Use one of these methods so the app starts automatically after a restart.

---

## Prerequisites

- **Node.js** and **PHP** installed (and in PATH, or Laravel Herd with PHP).
- You have already run **`pm2 start ecosystem.config.cjs`** and **`pm2 save`** at least once in this project folder.

---

## Option A – PM2 startup (recommended)

This makes PM2 start at boot and then restore your saved app (jesproject).

1. **Install the PM2 Windows startup helper** (once):
   ```bash
   npm install -g pm2-windows-startup
   ```

2. **Install the startup task** (run **Command Prompt or PowerShell as Administrator**):
   ```bash
   pm2-startup install
   ```
   When it asks “*Set PM2 to run at startup? (y/n)*”, type **y** and press Enter.

3. **Ensure your app is saved** (in the project folder `c:\laravel-projects\jesproject`):
   ```bash
   pm2 save
   ```

After a reboot, PM2 will start and run **jesproject** (Laravel on port 8000) automatically.

---

## Option B – Startup folder (no admin rights)

If you prefer not to use `pm2-windows-startup` or don’t have admin rights:

1. **Save your PM2 process list** (in the project folder):
   ```bash
   pm2 save
   ```

2. **Open the Windows Startup folder**  
   Press **Win + R**, type:
   ```text
   shell:startup
   ```
   Press Enter. A folder window will open.

3. **Add the batch file**  
   Copy **`pm2-resurrect-on-boot.bat`** from the project folder  
   `c:\laravel-projects\jesproject\`  
   into this Startup folder (or create a shortcut to it there).

After each **login**, the batch file will run and PM2 will resurrect your saved app.  
*(Note: this runs at **user logon**, not at boot before login. Option A runs at **boot**.)*

---

## Verify

- After rebooting (Option A) or logging in (Option B):
  - Open **http://127.0.0.1:8000** on the server PC.
  - From another device: **http://YOUR_PC_IP:8000** (e.g. `http://192.168.8.102:8000`).
- To check PM2: open a terminal and run `pm2 list` and `pm2 logs jesproject`.

---

## Troubleshooting

| Problem | What to try |
|--------|-------------|
| App doesn’t start after reboot | Run `pm2 save` again in the project folder, then reboot. |
| Port 8000 blocked from other PCs | Run **Allow port 8000** (e.g. `allow-port-8000-firewall.bat` as Administrator) once. |
| PM2 not found in startup | For Option A, run `pm2-startup install` again **as Administrator**. |
