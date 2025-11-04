# ADR-003: Retry płatności przy odnowieniu subskrypcji
## Kontekst

Płatności nie działają „albo tak, albo nie”. Rzeczywistość jest brudna:

- użytkownik ma chwilowy brak środków,
- karta jest dobra, ale provider się wywalił,
- czasem to error komunikacji,
- innym razem user naprawdę nie chce płacić.

Wniosek: nie można od razu expirować subskrypcji. Trzeba dać jej szansę. Ale skończoną. Nie robimy wiecznego błagania karty o łaskę.

## Decyzja

Wprowadzam skończoną politykę retry:

- natychmiast po failu: **1. próba**
- potem jeszcze **2 retry** w określonych odstępach
- jeśli w trakcie retry użytkownik zapłaci sam, system resetuje licznik
- po wyczerpaniu retry subskrypcja wchodzi w **Grace Period**
- po **Grace Period** subskrypcja wygasa

Retry nie resetuje się automatycznie co pół dnia, nie jest nieskończone.

Wydarzenia:

- ```SubscriptionPaymentFailed```
- ```SubscriptionRetryScheduled```
- ```SubscriptionRetrySuccess```
- ```SubscriptionRetryExhausted -> enter GracePeriod```

Retry policy jest w domenie, a nie w cronie. Cron tylko wywołuje „spróbuj” i domena mówi, co dalej.

## Alternatywy rozważane
**1. Retry w cronie / licznik w DB**

Tani hack. Rozsypie się przy user actions pomiędzy retry, przy race conditions i przy braku transakcyjności.

**2. Retry bez limitu**

Słabe. Błaganie o pieniądze bez końca wygląda jak desperacja i prowadzi do inconsistent state.

**3. Brak retry (fail -> grace natychmiast)**

Może wyglądać „czysto”, ale to jak restauracja, która wyrzuca klienta za pierwszym razem, gdy nie zapłacił napiwku.

**Uzasadnienie**

Retry są:

- deterministyczne,
- policzalne,
- sterowane domeną,
- czytelne do debugowania.

Bez niespodzianek w stylu „a bo cron o 3:00 rano się wywalił i licznik nie poszedł”.

## Konsekwencje

Plusy:
- odporność na chaos realnego świata,
- łatwe testowanie logiki retry,
- zachowanie user intent (cancel > retry).

Minusy:
- potrzeba utrzymywać stan retry w modelu,
- więcej eventów, czyli trzeba pilnować ich celu, żeby nie robić miksu komunikatów jak cebula z czosnkiem w kawie.

## Status

Zatwierdzone. Implementacja w toku.