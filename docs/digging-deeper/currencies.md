# Currencies

Currencies can be managed via `Backend settings > Mall: General > Currencies`.
They are used for all pricing information in the plugin.

## Format

A custom format for each currency can be defined in the `Format` code editor.

## Rate

Every currency can be given a `Rate` that is relative to the default currency. If a price in a specific currency is 
not defined, it will be automatically calculated by multiplying the price from the default currency by the defined 
rate.  

If the base currency is Euro, a rate of 1.15 could be defined for USD.

## Twig-Filter

You can use the Twig filter ` | money` to format any number in the currently active currency.

::: v-pre
`{{ 2000 | money }}` results in `$ 20.00`.
:::
