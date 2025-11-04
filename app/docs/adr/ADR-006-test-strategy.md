# ADR-006: Test strategy (unit domain first, integration second)

## Kontekst
DDD, logika w domenie, framework to tylko rury.

## Problem
Jak testujemy, żeby nie skończyć w bagnie mocków i ślepego testowania Symfony?

## Decyzja
Testy piszemy w kolejności:

1. **Unit tests domeny**\
Encje, wartości, reguły, eventy.\
Zero DB. Zero framework.\
Tu łapiemy logikę świata.

2. **Integration tests**\
Doctrine / repozytoria / kontrolery / web layer.\
Sprawdzamy że kable są podłączone, nie że świat działa.

3. **E2E dopiero jak ma to sens**\
Webhook journey, cancel-flow, retry-cycle.\
Nie pisz E2E na start, to droga do płaczu.

## Konsekwencje
Domena hermetyczna, pewna, czyściutka.\
Infrastructure testowana tam gdzie trzeba.\
Nie patrzymy jak Symfony „się czuje”, tylko jak działa biznes.

## Status
Zatwierdzone.