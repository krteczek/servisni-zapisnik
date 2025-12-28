
Servisní zápisník

Interní webová aplikace pro řízení servisních prací, úkolů a pracovních příkazů v provozu (údržba, montáže, servis).

Projekt vznikl postupným laděním reálného pracovního procesu mistra / předáka / montérů – od přijetí práce až po denní výstupy a historii.


---

🎯 Cíl projektu

Evidovat práci, ne lidi buzerovat

Minimalizovat ruční psaní

Umět:

zadat práci

rozdělit ji mezi týmy

evidovat skutečně odpracovaný čas

generovat výstupy (denní zápis, příkazy)

dohledat historii




---

🧠 Základní myšlenka

Mistr:

přijímá práci (email, telefon, osobně)

rozhoduje co hned a co počká

část práce má pracovní příkaz, část ne


Systém:

umí práci zapsat nebo importovat (email → task)

automaticky generuje úkoly z opakovaných činností

počítá hodiny podle reality

na konci dne vytvoří denní servisní zápis



---

👥 Role

Mistr / Předák

vytváří a spravuje úkoly

přiřazuje práci týmům nebo jednotlivcům

kontroluje splnění

generuje:

pracovní příkazy

denní zápis



Montér / Technik

vidí, na čem pracuje

zapisuje odpracované hodiny

označuje úkoly jako splněné



---

🧩 Hlavní entity

Users

jméno, příjmení

číslo zaměstnance

datum narození

datum nástupu / ukončení

login údaje


Teams

název týmu

barva týmu (vizuální rozlišení)


TeamMembers

historie příslušnosti uživatele k týmu

časově platné záznamy (od–do)

umožňuje zpětně zjistit, kde kdo byl


Tasks (Úkoly)

práce, která se má udělat

může existovat bez příkazu

může být později propojena s příkazem

přiřazení týmu / uživatele

stav (nový, rozpracovaný, hotový)


RecurringTasks (Opakované úkoly)

pravidelná činnost (kontroly, revize…)

při splnění podmínek automaticky vytvoří Task

aplikace sama hlídá čas (bez CRONu)


WorkLogs

kdo

na čem

kolik hodin

kdy


WorkOrders (Pracovní příkazy)

evidenční číslo (nepovinné)

propojení na jeden nebo více úkolů

hodiny lze upravit

export do:

emailu

textu

PDF



DailyReports (Denní zápis)

automaticky generovaný

shrnutí:

co se dělalo

kde

kým


tisknutelný

archivovaný



---

⏱️ Evidence času

hodiny zapisuje:

montér

předák


systém počítá součty:

na úkol

na příkaz

na den




---

🔁 Automatizace

opakované úkoly se samy promění v task, když nastane čas

upozornění:

mistr

tým


úkol existuje, dokud není splněn



---

📦 Výstupy

pracovní příkazy (export)

denní servisní zápis

historie prací

přehled odpracovaných hodin



---

🛠️ Technické poznámky

PHP aplikace

čas a datum řešeno aplikačně (DateTime)

bez externího CRONu

SQL databáze

jednoduché, čitelné UI



---

📚 Stav projektu

✔️ Datový model hotový

✔️ Logika navržena

🔜 Implementace backendu

🔜 UI



---

> Servisní zápisník není plánovač snů. Je to záznam reality.