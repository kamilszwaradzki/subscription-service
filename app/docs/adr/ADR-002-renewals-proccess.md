# ADR-002: Proces odnowień, opóźnionych płatności i utraty subskrypcji
## Kontekst

Subskrypcja to nie tabelka w bazie z datą. To proces w czasie:

- zbliża się odnowienie
- próbujemy obciążyć kartę
- czasem działa
- często nie działa, bo życie to nie demo Stripe’a
- użytkownik może anulować w trakcie
- provider może paść
- trzeba honorować intencję użytkownika

W skrócie: świat jest chaotyczny, a system musi reagować deterministycznie.

## Decyzja

Wprowadzam **stanową logikę subskrypcji i zdarzenia**, zamiast ifów rozlanych po kontrolerach i cronach.

Centralne zasady:

1. Stan subskrypcji jest źródłem prawdy

2. Retry płatności są deterministyczne i skończone

3. Intencja użytkownika jest nadrzędna nad automatami

4. System reaguje eventami (a nie „gdzieś tam w kodzie ktoś to ogarnie”)

Kluczowe eventy domenowe:

- SubscriptionRenewalDue

- SubscriptionPaymentFailed

- SubscriptionEnteredGracePeriod

- SubscriptionExpired

- SubscriptionCanceledByUser

- SubscriptionReactivated

## Alternatywy
**1. „Cron + kilka ifów”**

Brutalnie szybkie. I równie brutalnie później gryzie.
Najpierw uczucie: „tylko dopiszę jeszcze warunek”.
Potem rok później: „kto do cholery wywołuje to po trzecim retrze?”.

**2. Fully async / events everywhere**

Ładnie brzmi, dopóki nie trzeba debugować.
Na tym etapie projektu to overkill. Zaczynam od prostszego publish-subscribe inside code.

**3. Webhooks + external brain (Stripe Billing)**

Nie ten case. Chcę pokazać własny model domenowy, nie wpiąć gotowy billing.

## Uzasadnienie

Ta decyzja gwarantuje:

- przewidywalność procesu
- łatwą inspekcję stanu
- testy domenowe zamiast testów integracyjnych do śmierci
- miejsce na rozszerzenie, nie bagno warunków

Nie udaję, że buduję Netflixa. Ale też nie będę robił systemu, który rozjeżdża się przy drugim edge-case.

## Konsekwencje

Plusy:

- zmiany biznesowe są lokalne (stan, komendy, eventy)
- łatwo debugować „dlaczego użytkownik stracił dostęp”
- łatwo dopisać retry policy

Minusy:

- trzeba pilnować projektu, żeby nie skręcił w spaghetti async
- nie wszystko będzie super proste do wdrożenia juniorowi

Cena za spokój jutro? Warto.

## Status

Zatwierdzone. Implementacja w toku.