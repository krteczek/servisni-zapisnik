<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\UserModel;
use App\Core\Url;

class AuthController extends Controller
{
    private const MAX_ATTEMPTS = 5;
    private const BLOCK_TIME = 900; // 15 minut
    
    public function root(): string
    {
        Url::redirect(Auth::check() ? '/dashboard' : '/login');
    }
    
    public function loginForm(): string
    {
        $this->view->csrf   = Csrf::token();
        $this->view->errors = [];
        $this->view->data   = [];
        
        // Aprílový vtip
        if ($this->isAprilFirst()) {
            $this->view->aprilWarning = "Pozor! Dnes je 1. duben!";
        }
        
        return $this->render('auth/login');
    }

    public function login(): string
    {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $this->view->csrf = Csrf::token();
        $this->view->errors = [];
        $this->view->data = ['email' => $email];

        // Validace
        if ($email === '') {
            $this->view->errors['email'][] = 'Email je povinný';
        }
        if ($password === '') {
            $this->view->errors['password'][] = 'Heslo je povinné';
        }
        
        if ($this->view->errors) {
            return $this->render('auth/login');
        }

        // Kontrola blokace
        if ($this->isBlocked()) {
            $this->view->errors['global'][] = $this->getBlockedMessage();
            return $this->render('auth/login');
        }

        $userModel = new UserModel();
        $user = $userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            // Zaznamenáme špatný pokus
            $this->recordFailedAttempt();
            
            // Vtipná hláška
            $this->view->errors['global'][] = $this->getFunnyErrorMessage();
            
            // Zobrazíme info o zbývajících pokusech
            $remaining = $this->getRemainingAttempts();
            if ($remaining > 0) {
                $this->view->errors['global'][] = "Zbývá {$remaining} pokusů.";
            }
            
            // Čekání podle počtu pokusů (anti-brute force)
            $waitTime = (int) min(4, $this->getFailedAttempts() * 0.5);
            
            sleep($waitTime);
            
            return $this->render('auth/login');
        }
        
        // ÚSPĚŠNÉ PŘIHLÁŠENÍ
        $this->resetAttempts(); // Reset počítadla
        
        Auth::login([
            'id'          => (int) $user['id'],
            'email'       => $user['email'],
            'global_role' => $user['global_role'],
            'first_name'  => $user['first_name'] ?? null,
            'last_name'   => $user['last_name'] ?? null,
        ]);
        
        // Aprílové přesměrování (jen 1.4.)
        if ($this->isAprilFirst()) {
            $redirectTo = $this->getAprilRedirect();
            if ($redirectTo) {
                Url::redirect($redirectTo);
            }
        }
        
        // Normální přesměrování
        Url::redirect('/dashboard');
    }

    public function logout(): string
    {
        Auth::logout();
        Url::redirect('/login');
    }
    
    /* =========================
       VTIPNÉ HLÁŠKY
       ========================= */
    
    private function getFunnyErrorMessage(): string
    {
        $messages = [
            'Heslo neplatí. Zkusil jsi napsat správně?',
            'To nebylo ono. Možná máš Caps Lock?',
            'Špatná kombinace! Zkus to znovu.',
            'To heslo jsem už viděl... a nefungovalo.',
            'Chyba! Ale pěkný pokus!',
            'Nepovedlo se. Potřebuješ nápovědu?',
            'Heslo nesouhlasí. Zkoušel jsi "heslo"?',
            'Přístup zamítnut. Zkus se víc soustředit.',
            'To nebylo správné. Třetí pokus se počítá!',
            'Chybný vstup. Zkus to pomalu a s citem.',
            'Nene, to ne. Znovu!',
            'Špatně! Ale neboj, stále se učíš.',
            'Heslo neplatí. Možná potřebuješ kávu?',
            'To se nepovedlo. Zkus to ještě jednou.',
            'Ověření selhalo. Jsi si jistý, že jsi ty?'
        ];
        
        return $messages[array_rand($messages)];
    }
    
    private function getBlockedMessage(): string
    {
        if ($this->isAprilFirst()) {
            $messages = [
                'Příliš mnoho pokusů! Jdi si dát kafe a vrať se za 15 minut.',
                'Stop! To už bylo moc. Pauza na přemýšlení.',
                'Zablokováno! Tohle vypadá jako útok! Nebo jen špatná paměť?'
            ];
        } else {
            $messages = [
                'Příliš mnoho pokusů. Zkuste to znovu za 15 minut.',
                'Účet byl dočasně zablokován.',
                'Příliš mnoho neúspěšných pokusů.'
            ];
        }
        
        return $messages[array_rand($messages)];
    }
    
    /* =========================
       APRÍLOVÉ FUNKCE
       ========================= */
    
    private function isAprilFirst(): bool
    {
        return date('m-d') === '04-01';
    }
    
    private function getAprilRedirect(): ?string
    {
        // 25% šance na aprílové přesměrování
        if (rand(1, 100) > 25) {
            return null;
        }
        
        $redirects = [
            '/users',
            '/teams',
            '/dashboard',
            '/about',
            '/dashboard/april',
        ];
        
        return $redirects[array_rand($redirects)];
    }
    
    /* =========================
       ANTI-BRUTE FORCE
       ========================= */
    
    private function getFailedAttempts(): int
    {
        return \App\Core\Session::get('login_attempts', 0);
    }
    
    private function recordFailedAttempt(): void
    {
        $attempts = $this->getFailedAttempts() + 1;
        \App\Core\Session::set('login_attempts', $attempts);
        
        // Po překročení limitu zablokujeme
        if ($attempts >= self::MAX_ATTEMPTS) {
            \App\Core\Session::set(
                'login_blocked_until', 
                time() + self::BLOCK_TIME
            );
        }
    }
    
    private function resetAttempts(): void
    {
        \App\Core\Session::forget('login_attempts');
        \App\Core\Session::forget('login_blocked_until');
    }
    
    private function isBlocked(): bool
    {
        $blockedUntil = \App\Core\Session::get('login_blocked_until', 0);
        
        // Pokud blokace vypršela, vymažeme ji
        if ($blockedUntil > 0 && $blockedUntil < time()) {
            $this->resetAttempts();
            return false;
        }
        
        return $blockedUntil > time();
    }
    
    private function getRemainingAttempts(): int
    {
        $attempts = $this->getFailedAttempts();
        return max(0, self::MAX_ATTEMPTS - $attempts);
    }
}