<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Cache;

class UnlockController extends Controller
{
    /**
     * Kuhaa ang system password: env() una, dayon config, dayon basahon ang .env file kung walay nada.
     */
    private function getSystemPassword(): string
    {
        $path = base_path('.env');
        if (is_file($path) && is_readable($path)) {
            $content = @file_get_contents($path);
            if ($content !== false) {
                $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
                $content = str_replace("\x00", '', $content);
                if (substr($content, 0, 2) === "\xFF\xFE" || substr($content, 0, 2) === "\xFE\xFF") {
                    $content = mb_convert_encoding($content, 'UTF-8', 'UTF-16');
                }
                $key = 'SYSTEM_PASSWORD';
                $pos = stripos($content, $key);
                if ($pos !== false) {
                    $start = $pos + strlen($key);
                    $rest = substr($content, $start);
                    $rest = ltrim($rest, " \t=\r\n");
                    $end = strpbrk($rest, "\r\n#");
                    $value = $end !== false ? substr($rest, 0, strlen($rest) - strlen($end)) : $rest;
                    $value = trim($value, " \t\"\'");
                    $value = $this->normalizePassword($value);
                    if ($value !== '') {
                        return $value;
                    }
                }
            }
        }
        $value = Env::get('SYSTEM_PASSWORD') ?? $_ENV['SYSTEM_PASSWORD'] ?? env('SYSTEM_PASSWORD') ?? config('system.password');
        if ($value !== null && trim((string) $value) !== '') {
            return $this->normalizePassword($value);
        }
        $fallback = config('system.password');
        return ($fallback !== null && trim((string) $fallback) !== '') ? $this->normalizePassword((string) $fallback) : '';
    }

    private function normalizePassword(string $value): string
    {
        $value = trim(str_replace(["\r", "\n", "\t"], '', $value));
        $value = preg_replace('/[\x00-\x1F\x7F]/u', '', $value);
        return trim($value);
    }

    /**
     * I-validate ang password ug i-set ang session kung sakto.
     */
    public function unlock(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $systemPassword = $this->getSystemPassword();

        if ($systemPassword === '') {
            return back()->withErrors(['password' => 'System password not set. Add SYSTEM_PASSWORD=yourpassword to the .env file (not .env.example), then run: php artisan config:clear']);
        }

        $inputPassword = $this->normalizePassword((string) $request->password);
        if ($inputPassword !== $systemPassword) {
            return back()->withErrors(['password' => 'Wrong password. Please try again.']);
        }

        $activeSessionId = Cache::get('active_system_session_id');
        $currentSessionId = session()->getId();

        if ($activeSessionId && $activeSessionId !== $currentSessionId && ! $request->has('force')) {
            return back()->with('requires_override_confirmation', true)->withInput();
        }

        Cache::put('active_system_session_id', $currentSessionId, now()->addMinutes(config('session.lifetime', 120)));
        session(['system_unlocked' => true]);

        // So the Records page can play a short "unlock" transition animation.
        // (Home '/' already redirects to Records, so we redirect directly here.)
        return redirect()->route('records.index')->with('just_unlocked', true);
    }

    /**
     * Debug only: show system password length and path tried. Only when APP_DEBUG=true.
     */
    public function debugPasswordLength()
    {
        if (! config('app.debug')) {
            abort(404);
        }
        $password = $this->getSystemPassword();
        $len = strlen($password);
        $path = base_path('.env');
        $info = "System password length: {$len}\n";
        $info .= "base_path(): " . base_path() . "\n";
        $info .= "Env::get('SYSTEM_PASSWORD'): " . (Env::get('SYSTEM_PASSWORD') !== null ? 'set (len=' . strlen(Env::get('SYSTEM_PASSWORD') ?? '') . ')' : 'null') . "\n";
        if (is_file($path)) {
            $content = file_get_contents($path);
            $info .= "File size: " . strlen($content) . "\n";
            $pos = stripos($content, 'SYSTEM_PASSWORD');
            $info .= "SYSTEM_PASSWORD found in file: " . ($pos !== false ? 'yes at offset ' . $pos : 'no') . "\n";
        } else {
            $info .= "File .env not found or not readable\n";
        }
        return response($info, 200, ['Content-Type' => 'text/plain']);
    }

    /**
     * I-clear ang session aron i-lock pag-usab ang system.
     */
    public function lock(Request $request)
    {
        if (Cache::get('active_system_session_id') === session()->getId()) {
            Cache::forget('active_system_session_id');
        }

        $request->session()->forget('system_unlocked');

        return redirect('/unlock');
    }
}
