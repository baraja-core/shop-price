<?php

declare(strict_types=1);

namespace Baraja\Shop\Price;


use Baraja\Localization\Localization;

final class CurrencyResolver
{
	public function __construct(
		private Localization $localization,
	) {
	}


	public function getCurrency(?string $expected = null, ?string $locale = null): string
	{
		$locale ??= $this->localization->getLocale();

		return $expected
			?? $this->getSessionValue() // resolve by session
			?? PriceRenderer::LOCALE_CURRENCY[$locale] // use default by locale
			?? throw new \InvalidArgumentException('Expected currency does not exist.');
	}


	public function setCurrency(string $currency): void
	{
		$currency = strtoupper($currency);
		if (\in_array($currency, array_values(PriceRenderer::LOCALE_CURRENCY), true) === false) {
			throw new \InvalidArgumentException('Currency "' . $currency . '" is not supported now.');
		}
		$this->setSessionValue($currency);
	}


	private function getSessionKey(): string
	{
		return 'baraja_shop__currency';
	}


	private function getSessionValue(): ?string
	{
		$currency = (string) ($_SESSION[$this->getSessionKey()] ?? '');

		return $currency === '' ? null : $currency;
	}


	private function setSessionValue(?string $value): void
	{
		if ($value === null) {
			unset($_SESSION[$this->getSessionKey()]);
		} else {
			$_SESSION[$this->getSessionKey()] = $value;
		}
	}
}
