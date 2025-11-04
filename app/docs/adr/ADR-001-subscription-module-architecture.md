# ADR-001: Architektura modułu subskrypcji

## Kontekst
Buduję moduł subskrypcji w Symfony. Ma nie być „kolejnym CRUD-em z kontrolera”, tylko przykładem rozsądnej architektury domenowej, którą da się rozwijać bez płaczu.

Subskrypcje będą miały:
- stan i jego tranzycje (pending, active, grace, expired)
- logikę aktywacji i wygasania
- integrację z systemem płatności w kolejnych etapach
- procesy okresowe (odnowienia, retry płatności)
- eventy

Cel: To nie ma tylko działać. Ma być utrzymywalne.
## Decyzja
Wybrałem modularny układ domenowy:
- logika domenowa w serwisach domenowych
- tylko adaptery frameworka na brzegu
- eventy domenowe (np. `SubscriptionCreated`)
- kontroler jest wejściem, nie mózgiem
- testy logiki bez bootowania frameworka

Krótko: **Symfony to narzędzie, nie właściciel architektury.**

## Alternatywy
1. **Logika w kontrolerach**\
   Zrobiłem tak w poprzednich projektach. Przez pierwsze 3 miesiące było szybciej. Po roku zmiana planów taryfowych to była archeologia w HTTP layer i refaktor „na żywca”.

   Szybko tanieje, później drogie jak kredyt we frankach.
2. **CQRS/event sourcing**\
   Fajne do wystąpień na meetupach. Tu byłby to teatr dla samego teatru. Problem nie jest aż tak skomplikowany.

## Uzasadnienie wyboru
- chcę móc dopiąć webhooks Stripe bez rwanej refaktoryzacji
- chcę testować logikę domenową bez odpalania pół Symfony
- zmiany w planach i billing logic mają być lokalne, nie rozlane po kontrolerach
- minimalizuję ryzyko „rosnącego bagna”

Ta architektura nie jest po to, żeby wyglądać mądrze, tylko żeby **nie przeklinać siebie za pół roku.**

## Koszty
- więcej plików na start, wolniejszy onboarding
- muszę konsekwentnie pilnować granic modułu, inaczej to będzie tylko ładny diagram a brud ten sam
- nie każdą rzecz szybciej się tu robi, ale **każdą rzecz łatwiej zmienia**

## Status
W trakcie realizacji
