Psaní
Servisní zápisník – návod k použití
Základní princip
Servisní zápisník je jednoduchý pracovní systém pro evidenci práce na zakázkách.
Systém je postaven na třech základních prvcích:
zakázka
 └ úkoly
     └ reporty
Každý prvek má jasnou roli:
Zakázka – kontejner práce
Úkol – konkrétní práce, kterou je potřeba provést
Report – záznam o tom, co se při práci stalo
Historie práce se nikdy nepřepisuje, pouze se přidávají nové záznamy.
1. Zakázka
Zakázka je uzavřený kontejner práce.
Obsahuje všechny úkoly a jejich historii.
Vlastnosti zakázky
zakázka může obsahovat libovolný počet úkolů
úkoly lze přidávat kdykoliv během existence zakázky
zakázka uchovává kompletní historii práce
Příklad struktury
Zakázka: Servis čerpadla

 ├ Úkol: Demontáž čerpadla
 │   └ reporty
 │
 ├ Úkol: Kontrola potrubí
 │   └ reporty
 │
 └ Úkol: Montáž nového čerpadla
     └ reporty
2. Úkol
Úkol je konkrétní práce, kterou je potřeba provést.
Pravidla
úkol patří vždy pouze jedné zakázce
úkol nelze sdílet mezi zakázkami
úkol může mít více reportů
úkol uzavírá mistr
Doporučení
Úkol by měl být:
jasný
konkrétní
splnitelný ideálně během jednoho dne
Pokud je práce příliš velká nebo dlouhodobá, pravděpodobně jde spíše o zakázku než úkol.
Názvy úkolů
Názvy úkolů nemusí být unikátní.
Například:
Úkol: Montáž čerpadla
Úkol: Montáž čerpadla
Každý úkol má vlastní ID, které ho jednoznačně identifikuje.
3. Report
Report je zápis o průběhu práce.
Přidávají ho pracovníci během plnění úkolu.
Vlastnosti reportu
report patří jednomu úkolu
report je časový záznam práce
report může mít délku:
1 znak až 10 000 znaků
Report může obsahovat například:
popis provedené práce
zjištěné problémy
průběh práce
další poznámky
Příklad reportu
Demontáž čerpadla dokončena.
Při kontrole zjištěna koroze na potrubí.
Doporučena výměna části potrubí.
4. Uzavření úkolu
Úkol uzavírá mistr, pokud považuje práci za dokončenou.
Po uzavření úkolu:
nelze přidávat další reporty
úkol je považován za dokončený
5. Chyby a opravy
Pokud se po uzavření úkolu objeví problém, úkol se znovu neotevírá.
Vytvoří se nový úkol.
Příklad
Zakázka: Servis čerpadla

 ├ Úkol: Montáž čerpadla
 │   └ reporty
 │
 └ Úkol: Oprava netěsnosti čerpadla
     └ reporty
Tím zůstává historie práce přehledná.
6. Tok práce
Typický průběh práce:
1 mistr vytvoří zakázku
2 mistr vytvoří úkoly
3 pracovníci plní úkoly
4 pracovníci přidávají reporty
5 mistr úkol uzavře
Během práce může mistr kdykoliv:
přidat další úkol
reagovat na nové situace
7. Filozofie systému
Systém je navržen jako jednoduchý pracovní nástroj.
Základní principy:
jednoduchost
jasná struktura
neměnná historie
minimum složitých pravidel
Každá zakázka je samostatný deník práce, který obsahuje kompletní historii provedených úkonů.
Shrnutí
firma
 └ zakázky
     └ úkoly
         └ reporty
Pravidla:
zakázka = kontejner práce
úkol patří jedné zakázce
report patří jednomu úkolu
historie se nepřepisuje
chyby se řeší novým úkolem
Výsledkem je jednoduchý a přehledný systém pro evidenci práce.