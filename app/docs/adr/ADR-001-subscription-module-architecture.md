# ADR-001: Architektura modułu subskrypcji

## Kontekst
Tworzę moduł subskrypcji w Symfony jako przykład podejścia do projektowania usług backendowych.\
Cel: pokazać umiejętność tworzenia modularnego kodu z logiką domenową, testami, migracjami, DI, eventami i walidacją.

System ma umożliwiać:
- tworzenie subskrypcji na poziomie API
- walidację danych wejściowych
- logikę aktywacji subskrypcji
- zapis do bazy
- emitowanie eventu o utworzeniu subskrypcji
- przyszłą obsługę płatności i planów taryfowych

## Decyzja
Projektuje moduł subskrypcji jako izolowaną część aplikacji z własnymi:
- encjami
- DTO / request modelami
- walidacją
- warstwą serwisów domenowych
- eventem `SubscriptionCreated`
- kontrolerem API

Nie pcham logiki do kontrolera. Framework jest adapterem. Logika jest po mojej stronie.

## Opcje rozważane
1. **Monolityczna struktura** (kontroler robi wszystko)
   * plusy: szybciej na start  
   * minusy: brak skalowalności i testowalności
2. **Symfony clean modular**
   * plusy: czystość, testowalność
   * minusy: więcej kodu, ale warto
3. **CQRS/event sourcing**
   * plusy: na papierze wygląda mądrze  
   * minusy: overkill, wyglądałoby jak pozowanie zamiast rozumu

Wybrana opcja: **2**

## Uzasadnienie
- pokazuje świadomość architektury
- łatwe do testowania
- rozwijalne o płatności, retry, webhooks
- realny przykład implementacji domeny

## Konsekwencje
- większy rozruch projektu, ale finalnie lepsza jakość
- łatwe dopisywanie kolejnych case study, bo moduł ma realny kształt

## Status
W trakcie realizacji
