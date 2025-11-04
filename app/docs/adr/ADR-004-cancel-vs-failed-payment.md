# ADR-004: Reakcja na race: user cancel vs payment fail

## Kontekst
Wyścig: user naciska "cancel", system szykuje revoke, w tym samym czasie provider zwraca payment failure z próby odnowienia.

## Problem
Co ma pierwszeństwo: zamiar usera czy systemowa logika renew/failed-payment?

## Decyzja
Zawsze honorujemy user cancel.
Jeśli cancel request pojawił się przed lub równocześnie z payment failure, **ignorujemy failure i zamykamy subskrypcję.**

## Reguła
User intent > automated billing.

## Konsekwencja
Brak ghost-grace-periodów po cancel.
Prostszy stan: canceled beats everything.
Stracimy możliwość „jeszcze spróbować płatności” po cancel? Trudno. User zdecydował.

## Status
Zatwierdzone.