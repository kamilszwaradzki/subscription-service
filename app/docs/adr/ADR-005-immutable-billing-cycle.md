# ADR-005: Immutable billing cycle (timezone ignored after creation)

## Kontekst
User może zmienić timezone w profilu po rozpoczęciu subskrypcji.

## Problem
Czy billing date/time zmienia się razem z timezone usera?

## Decyzja
Billing cycle anchor jest **immutable**.
Billing timestamp zapisany w UTC przy tworzeniu subskrypcji i nigdy nie ruszany.

## Powód
Spójność > ułuda „lokalności czasu”.
Zmiana timezone nie wpływa na cykl płatności, koniec dyskusji.

## Konsekwencja
User może widzieć „dziwne godziny” po zmianie timezone, ale cykl jest stabilny.
Prościej testować, prościej rozumieć, brak edge-messu.

## Status
Zatwierdzone.