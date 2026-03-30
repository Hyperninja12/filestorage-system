# Quick start — run the app smoothly

## Easiest (recommended): background + close the window

1. **One-time:** `npm install -g pm2`
2. **WiFi from other devices (one-time):** Right‑click `allow-port-8000-firewall.bat` → **Run as administrator**
3. **Every day:** Double‑click **`start-server.bat`**
4. Open **http://127.0.0.1:8000** (this PC) or **http://YOUR_PC_IP:8000** (phones / other PCs on same WiFi)

The server keeps running after you close the CMD window.  
Stop later: `pm2 stop jesproject` (or `pm2 delete jesproject`).

---

## Simplest (no PM2): one window stays open

Double‑click **`run-simple.bat`**.  
Leave the window open. Close it to stop the server.

---

## Auto-start when you log in to Windows

1. Run **`start-server.bat`** once, then `pm2 save` (the batch already saves).
2. Put **`pm2-resurrect-on-boot.bat`** in your Startup folder: `Win+R` → `shell:startup` → copy the file there.

---

## If the browser does not load

- Run **`check-server.bat`**
- Or: `pm2 logs jesproject` and read **`storage/logs/serve-pm2.log`**
