/*******************************************************
 * SERVISNÍ ZÁPISNÍK – PROJEKTOVÝ KONTRAKT
 * (věci, které se už NIKDY svévolně nemění)
 *
 * Autor: Petr
 * Filosofie: jednoduchost, čitelnost, žádná magie
 *******************************************************/

/*
1) ARCHITEKTURA
--------------------------------------------------------
- Vlastní mini-framework
- Žádné externí MVC frameworky
- Explicitní tok aplikace (žádné implicitní chování)
- Každý krok je čitelný v kódu
*/

/*
2) ROUTER
--------------------------------------------------------
- Router je klíčový prvek aplikace
- Definuje:
  - controller
  - akci
  - title stránky
  - submenu / kontext
- Router je jediné místo, kde se řeší:
  - navigační struktura
  - page title (který se používá i pro <title> i <h1>)
- Title se negeneruje v šablonách, ale přichází z routeru
*/

/*
3) CONTROLLER
--------------------------------------------------------
- Základní Controller je jednoduchý
- Má ViewContext
- Render:
  - používá output buffering
  - include šablon přímo
- Controller:
  - neřeší HTML strukturu
  - neřeší globální validace
*/

/*
4) VIEW / ŠABLONY
--------------------------------------------------------
- header.php:
  - obsahuje <title>
  - obsahuje <h1>
- Title == H1 (jedna pravda, žádná duplicita)
- Žádná logika v šablonách
- Šablony jsou hloupé (jen zobrazují data)
*/

/*
5) REDIRECT
--------------------------------------------------------
- redirect():
  - NIC nevrací
  - ukončuje script (exit)
- Redirect = konec requestu
- Nikdy se s návratovou hodnotou redirectu nepočítá
*/

/*
6) VALIDACE
--------------------------------------------------------
- Validace emailu a hesla:
  - aktuálně pouze v UserControlleru
  - žádný globální validator
  - žádné helper třídy rozeseté po projektu
- Jednoduché lokální metody:
  - isEmail(string): bool
  - isPasswordValid(string): bool
- Použití:
  if (!$this->isEmail($email)) { ... }
*/

/*
7) UŽIVATELÉ
--------------------------------------------------------
- Email:
  - validace formátu
- Heslo:
  - minimální délka (např. 8 znaků)
- Stejná pravidla pro:
  - vytvoření uživatele
  - editaci uživatele
*/

/*
8) SESSION / AUTH
--------------------------------------------------------
- Auth je jednoduchý
- Session obsahuje:
  - user
  - role
- Žádná magie, žádné automaty
*/

/*
9) FILOSOFIE PROJEKTU
--------------------------------------------------------
- Čitelnost > chytrost
- Explicitní kód > abstrakce
- Raději duplicita než magie
- Když se kód po půl roce čte:
  - je jasné, co se děje
  - není potřeba „vědět jak to funguje“
*/

/*
10) ROZSAH
--------------------------------------------------------
- Projekt je servisní zápisník
- Není to:
  - scanner
  - obecný framework
- Věci z jiných projektů se sem netahají
*/

/*******************************************************
 * Pokud má někdo potřebu tohle porušit:
 * → nejdřív se ptá „PROČ“
 * → a odpověď musí bolet
 *******************************************************/
