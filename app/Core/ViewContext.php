<?php
declare(strict_types=1);

namespace App\Core;

class ViewContext
{
    public string $title = '';
    public ?array $user = null;
    public ?array $users = null;
    public bool $isLogged = false;

    public string $csrf = '';

    /** menu položky */
    public array $menu = [];

    /** flash / info zprávy */
    public array $messages = [];

    /** validační chyby */
    public array $errors = [];

    /** data formulářů */
    public array $data = [];
    
    public array $old = [];
    
    public ?array $roles = null;
    
    public ?string $selectedRole = null;
    
    
}
