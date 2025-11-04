# Subscription Module Architecture

Moduł subskrypcji do SaaS.\
Cel: najprostszy możliwy system cyklicznych płatności z twardą logiką kontroli dostępu, retry, grace period i wymuszanego wygaszania.

## 1. Zakres

Obsługiwane:
- Utworzenie subskrypcji
- Aktywacja (po płatności)
- Odnowienia
- Grace period
- Retry failed payments
- Anulowanie
- Wygaszanie dostępu

Nieobsługiwane (świadomie):
- Pauza subskrypcji
- Zwroty
- Upgrade/downgrade in-cycle
- Part-month proration

Prosta zasada: prosta logika > pokrycie wszystkich edge case’ów.

## 2. Folder structure
```
src/
  Domain/
    Subscription/
      Subscription.php
      SubscriptionStatus.php
      BillingFailureReason.php
      Event/
        SubscriptionCreated.php
        SubscriptionActivated.php
        SubscriptionPaymentFailed.php
        SubscriptionExpired.php
        SubscriptionCanceled.php
    ValueObject/
      PlanId.php
      SubscriptionId.php
      UserId.php

  Application/
    Subscription/
      Command/
        CreateSubscriptionCommand.php
        ConfirmPaymentCommand.php
        CancelSubscriptionCommand.php
        RetryPaymentCommand.php
        FinalizeGracePeriodCommand.php
        RunRenewalsCommand.php
      Handler/
        CreateSubscriptionHandler.php
        ConfirmPaymentHandler.php
        CancelSubscriptionHandler.php
        RetryPaymentHandler.php
        FinalizeGracePeriodHandler.php
        RunRenewalsHandler.php

  Infrastructure/
    Subscription/
      Repository/
        DoctrineSubscriptionRepository.php
      Payment/
        StubPaymentProvider.php
      Console/
        RunRenewalsConsoleCommand.php
        FinalizeGracePeriodConsoleCommand.php
```

## 3. Model domeny

| Status             | Znaczenie                           |
| ------------------ | ----------------------------------- |
| pending_activation | utworzona, brak pierwszej płatności |
| active             | dostęp aktywny                      |
| grace_period       | retry po błędzie płatności          |
| expired            | wygaszona                           |
| canceled           | anulowana przez usera               |

## Zdarzenia domenowe (fakty)

- SubscriptionCreated
- SubscriptionActivated
- SubscriptionPaymentFailed
- SubscriptionGracePeriodStarted
- SubscriptionExpired
- SubscriptionCanceled

Eventy opisują fakty. Nie są komendami.

## 4. State Machine

| Current state      | Event             | Next         | Action                                     |
| ------------------ | ----------------- | ------------ | ------------------------------------------ |
| pending_activation | payment_confirmed | active       | grant access                               |
| pending_activation | payment_failed    | expired      | revoke access                              |
| active             | user_cancel       | canceled     | revoke access immediately                  |
| active             | payment_failed    | grace_period | notify user + schedule retries             |
| grace_period       | retry_success     | active       | restore access, reset counters             |
| grace_period       | grace_expired     | expired      | revoke access                              |
| active             | renewal_date      | try_charge   | if success -> active, else -> grace_period |

Edge-case rules:

- Jeśli user anulował i w tym momencie padła płatność
Cancel wygrywa. Payment fail ignorowany.

- Zmiana strefy czasowej usera nie zmienia daty billingowej.
Cykl jest immutable po stworzeniu.
## 5. Use cases (API modułu)
| Use case            | Input          | Output                   |
| ------------------- | -------------- | ------------------------ |
| CreateSubscription  | userId, planId | subscriptionId           |
| ConfirmPayment      | subscriptionId | activate                 |
| CancelSubscription  | subscriptionId | cancel + revoke          |
| RetryPayment        | subscriptionId | active lub stay in grace |
| RunRenewals         | cron           | charge or start grace    |
| FinalizeGracePeriod | cron           | expire                   |

## 6. Retry logic

- Próby: max 3

- Interwały:
`1h -> 24h -> 72h`

- Po ostatniej próbie: `grace_period -> expired (po 7 dniach)`

## 7. Dostęp

Nie ma „tymczasowego odcięcia”.\
Jest zero-jedynkowo:
- active = ma dostęp
- wszystko inne = nie ma

**Canceled** = natychmiast revoke.

## 8. Integracja z płatnościami

MVP: stub provider

Webhook flow:

```payment_provider -> webhook -> Command -> Handler -> Domain -> Persist -> Emit event```


Fail provider = traktujemy jak fail płatności.\
System nie wróży z fusów czy to „użytkownik biedny” czy „Stripe padł”.
## 9. Cron boty

W Symfony w ```bin/console```:

- ```subscriptions:run-renewals```

- ```subscriptions:finalize-grace```

Uruchamiane przez scheduler.

## 10. Reguły projektowe

- Brak logiki w kontrolerach.

- Handlery robią orkiestrację, nie decydują o stanie.

- Event handler async nie może zmieniać domeny bez command.

## 11. Testy
- Domain: pure tests (PHPUnit, bez DB)

- Application: use-case tests

- Infrastructure: DB + wiring tests